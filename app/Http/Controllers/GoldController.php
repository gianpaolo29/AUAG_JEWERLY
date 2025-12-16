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
    public function index(Request $request, \App\Services\Gold\ExchangeRateService $fx)
    {
        $days = (int) $request->query('days', 14);
        $days = in_array($days, [7, 14, 30], true) ? $days : 14;

        $fxInfo = $fx->usdToPhp();
        $usdToPhp = (float) $fxInfo['rate'];
        $fxDate = (string) $fxInfo['date'];

        // ✅ provide $history
        $history = \App\Models\GoldPrice::query()
            ->orderBy('date', 'desc')
            ->limit(180)
            ->get(['date', 'price_usd'])
            ->reverse()
            ->values();

        $latest = \App\Models\GoldPrice::query()->orderByDesc('date')->first();

        $prev = $latest
            ? \App\Models\GoldPrice::query()->where('date', '<', $latest->date)->orderByDesc('date')->first()
            : null;

        $latestPhpPerGram = $latest ? (($latest->price_usd * $usdToPhp) / 31.1034768) : null;
        $prevPhpPerGram   = $prev   ? (($prev->price_usd   * $usdToPhp) / 31.1034768) : null;

        $change = ($latestPhpPerGram !== null && $prevPhpPerGram !== null)
            ? ($latestPhpPerGram - $prevPhpPerGram)
            : null;

        $changePct = ($latestPhpPerGram !== null && $prevPhpPerGram !== null && $prevPhpPerGram > 0)
            ? ($change / $prevPhpPerGram) * 100
            : null;

        $lastRun = \App\Models\GoldModelRun::query()->latest('trained_at')->first();

        // ✅ provide $forecast
        $forecast = collect();
        if ($latest && $lastRun) {
            $forecast = \App\Models\GoldForecast::query()
                ->where('as_of_date', $latest->date)
                ->where('gold_model_run_id', $lastRun->id)
                ->orderBy('target_date')
                ->limit($days)
                ->get(['target_date', 'predicted_usd', 'lower_usd', 'upper_usd']);
        }

        // ✅ points for chart (PHP/gram)
        $historyPhpPoints = $history->map(fn($r) => [
            'x' => $r->date->toDateString(),
            'y' => ((float)$r->price_usd * $usdToPhp) / 31.1034768,
        ])->values();

        $forecastPhpPoints = $forecast->map(fn($r) => [
            'x' => \Carbon\Carbon::parse($r->target_date)->toDateString(),
            'y' => ((float)$r->predicted_usd * $usdToPhp) / 31.1034768,
        ])->values();

        return view('admin.Forecast.Index', compact(
            'days',
            'history',            // ✅ now exists in Blade
            'forecast',           // ✅ now exists in Blade
            'latest',
            'lastRun',
            'historyPhpPoints',
            'forecastPhpPoints',
            'latestPhpPerGram',
            'change',
            'changePct',
            'usdToPhp',
            'fxDate',
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
                ->route('admin.forecast', ['days' => $days])
                ->with('status', "Forecast complete: {$count} future points generated.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
