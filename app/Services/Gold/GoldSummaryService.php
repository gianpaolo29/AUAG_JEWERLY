<?php

namespace App\Services\Gold;

use App\Models\GoldForecast;
use App\Models\GoldModelRun;
use App\Models\GoldPrice;
use Carbon\Carbon;

class GoldSummaryService
{
    private const TROY_OZ_TO_GRAMS = 31.1034768;

    public function get(int $historyLimit = 60): array
    {
        $latest = GoldPrice::query()->orderByDesc('date')->first();

        // History base (always return something predictable)
        $historyRows = GoldPrice::query()
            ->orderBy('date', 'desc')
            ->limit($historyLimit)
            ->get(['date', 'price_usd'])
            ->reverse()
            ->values();

        if (! $latest) {
            return [
                'current_php_per_gram' => null,
                'current_date' => null,
                'next_php_per_gram' => null,
                'next_date' => null,
                'history_points' => [],
                'note' => 'No gold data yet. Please sync first in /gold.',
            ];
        }

        $fx = app(ExchangeRateService::class)->usdToPhp();
        $usdToPhp = (float) $fx['rate'];

        // Convert history to PHP/gram points
        $historyPoints = $historyRows->map(fn($r) => [
            'x' => Carbon::parse($r->date)->toDateString(),
            'y' => (((float)$r->price_usd * $usdToPhp) / self::TROY_OZ_TO_GRAMS),
        ])->values()->all();

        $currentDate = Carbon::parse($latest->date)->toDateString();
        $currentPhpPerGram = (((float)$latest->price_usd * $usdToPhp) / self::TROY_OZ_TO_GRAMS);

        $run = GoldModelRun::query()->latest('trained_at')->first();
        if (! $run) {
            return [
                'current_php_per_gram' => $currentPhpPerGram,
                'current_date' => $currentDate,
                'next_php_per_gram' => null,
                'next_date' => null,
                'history_points' => $historyPoints,
                'note' => 'No trained model yet. Train/forecast in /gold.',
            ];
        }

        // Next forecast date = latest data date + 1 day
        $nextDate = Carbon::parse($currentDate)->addDay()->toDateString();

        $forecast = GoldForecast::query()
            ->where('as_of_date', $currentDate)
            ->where('gold_model_run_id', $run->id)
            ->where('target_date', $nextDate)
            ->first();

        if (! $forecast) {
            return [
                'current_php_per_gram' => $currentPhpPerGram,
                'current_date' => $currentDate,
                'next_php_per_gram' => null,
                'next_date' => $nextDate,
                'history_points' => $historyPoints,
                'note' => 'No forecast yet. Generate forecast in /gold.',
            ];
        }

        $nextPhpPerGram = (((float)$forecast->predicted_usd * $usdToPhp) / self::TROY_OZ_TO_GRAMS);

        return [
            'current_php_per_gram' => $currentPhpPerGram,
            'current_date' => $currentDate,
            'next_php_per_gram' => $nextPhpPerGram,
            'next_date' => $nextDate,
            'history_points' => $historyPoints,
            'note' => null,
        ];
    }
}
