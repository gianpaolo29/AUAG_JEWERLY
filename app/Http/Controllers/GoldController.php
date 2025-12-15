<?php

namespace App\Http\Controllers;

use App\Models\GoldForecast;
use App\Models\GoldModelRun;
use App\Models\GoldPrice;
use App\Services\Gold\GoldDataSyncService;
use App\Services\Gold\GoldForecaster;
use App\Services\Gold\GoldModelTrainer;
use Illuminate\Http\Request;

class GoldController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->query('days', 14);
        $days = in_array($days, [7, 14, 30], true) ? $days : 14;

        // last 180 records for chart
        $history = GoldPrice::query()
            ->orderBy('date', 'desc')
            ->limit(180)
            ->get(['date', 'price_usd'])
            ->reverse()
            ->values();

        // latest real price
        $latest = GoldPrice::query()->orderByDesc('date')->first();

        // previous day price (for change)
        $prev = $latest
            ? GoldPrice::query()
                ->where('date', '<', $latest->date)
                ->orderByDesc('date')
                ->first()
            : null;

        $change = ($latest && $prev) ? ((float) $latest->price_usd - (float) $prev->price_usd) : null;

        $changePct = ($latest && $prev && (float) $prev->price_usd > 0)
            ? ($change / (float) $prev->price_usd) * 100
            : null;

        // latest training run
        $lastRun = GoldModelRun::query()->latest('trained_at')->first();

        // load forecast for latest date + latest run (if exists)
        $forecast = collect();
        if ($latest && $lastRun) {
            $forecast = GoldForecast::query()
                ->where('as_of_date', $latest->date)
                ->where('gold_model_run_id', $lastRun->id)
                ->orderBy('target_date')
                ->limit($days)
                ->get(['target_date', 'predicted_usd', 'lower_usd', 'upper_usd']);
        }

        return view('gold.dashboard', compact(
            'history',
            'forecast',
            'latest',
            'prev',
            'change',
            'changePct',
            'lastRun',
            'days'
        ));
    }

    public function sync(GoldDataSyncService $sync)
    {
        try {
            $count = $sync->sync();
            return back()->with('status', "Sync complete: {$count} rows upserted.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function train(GoldModelTrainer $trainer)
    {
        try {
            $run = $trainer->train();
            return back()->with('status', "Training complete: model run #{$run->id} saved.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forecast(Request $request, GoldForecaster $forecaster)
    {
        try {
            $days = (int) $request->input('days', 14);
            $days = in_array($days, [7, 14, 30], true) ? $days : 14;

            $count = $forecaster->forecast($days);

            return redirect()
                ->route('gold.dashboard', ['days' => $days])
                ->with('status', "Forecast complete: {$count} future points generated.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
