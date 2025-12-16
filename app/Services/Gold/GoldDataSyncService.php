<?php

namespace App\Services\Gold;

use App\Models\GoldPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GoldDataSyncService
{
    /**
     * Backward compatible (if other code calls sync(180)).
     */
    public function sync(int $days = 180): int
    {
        return $this->syncDays($days);
    }

    /**
     * Sync last N days (daily history) + also upsert latest live row.
     */
    public function syncDays(int $days = 180): int
    {
        $end = now('UTC')->toDateString();
        $start = now('UTC')->subDays(max(1, $days) - 1)->toDateString();

        return $this->syncRange($start, $end);
    }

    /**
     * Sync inclusive date range for TIMESERIES (daily history).
     * Metals.dev timeseries max is 30 days, so we chunk.
     * After timeseries, we also upsert /latest to ensure "today/latest" exists.
     */
    public function syncRange(string $from, string $to): int
    {
        $baseUrl = rtrim((string) config('services.metals_dev.base_url', ''), '/');
        $apiKey  = (string) config('services.metals_dev.key', '');

        if ($baseUrl === '' || ! str_starts_with($baseUrl, 'http')) {
            throw new \RuntimeException('Invalid METALS_DEV_BASE_URL. Check .env + config/services.php');
        }
        if ($apiKey === '') {
            throw new \RuntimeException('Missing METALS_DEV_API_KEY. Check .env + config/services.php');
        }

        $start = Carbon::parse($from)->startOfDay();
        $end   = Carbon::parse($to)->startOfDay();

        if ($start->gt($end)) {
            throw new \RuntimeException('Invalid range: --from is after --to.');
        }

        $total = 0;

        // ---- A) DAILY HISTORY via /timeseries (chunked) ----
        while ($start->lte($end)) {
            // inclusive 30-day window = start + 29 days
            $chunkEnd = $start->copy()->addDays(29);
            if ($chunkEnd->gt($end)) $chunkEnd = $end->copy();

            $total += $this->syncTimeseriesChunk($baseUrl, $apiKey, $start, $chunkEnd);

            $start = $chunkEnd->copy()->addDay();
        }

        // ---- B) LATEST LIVE row via /latest (ensures today/latest date exists) ----
        $total += $this->syncLatest($baseUrl, $apiKey);

        return $total;
    }

    private function syncTimeseriesChunk(string $baseUrl, string $apiKey, Carbon $start, Carbon $end): int
    {
        $resp = Http::retry(3, 500)
            ->baseUrl($baseUrl)
            ->get('/timeseries', [
                'api_key'    => $apiKey,
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
            ]);

        $resp->throw();
        $json = $resp->json();

        if (($json['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Metals.dev timeseries error: ' . json_encode($json));
        }

        $rates = $json['rates'] ?? [];
        if (! is_array($rates)) return 0;

        $count = 0;

        foreach ($rates as $date => $payload) {
            // Typical structure: rates[date].metals.gold
            $goldUsdToz = data_get($payload, 'metals.gold');

            if (! is_numeric($goldUsdToz)) continue;

            GoldPrice::updateOrCreate(
                ['date' => $date],
                [
                    'price_usd' => (float) $goldUsdToz,
                    'source'    => 'metals.dev.timeseries',
                ]
            );

            $count++;
        }

        return $count;
    }

    private function syncLatest(string $baseUrl, string $apiKey): int
    {
        $resp = Http::retry(3, 500)
            ->baseUrl($baseUrl)
            ->get('/latest', [
                'api_key'   => $apiKey,
                'currency'  => 'USD',
                'unit'      => 'toz',
            ]);

        $resp->throw();
        $json = $resp->json();

        if (($json['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Metals.dev latest error: ' . json_encode($json));
        }

        $goldUsdToz = data_get($json, 'metals.gold');
        $ts         = data_get($json, 'timestamps.metal') ?? data_get($json, 'timestamp');

        if (! is_numeric($goldUsdToz)) return 0;

        // If timestamp missing, still store as today's UTC date
        $date = $ts ? Carbon::parse($ts)->toDateString() : now('UTC')->toDateString();

        GoldPrice::updateOrCreate(
            ['date' => $date],
            [
                'price_usd' => (float) $goldUsdToz,
                'source'    => 'metals.dev.latest',
            ]
        );

        return 1;
    }
}
