<?php

namespace App\Services\Gold;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    /**
     * Returns ['rate' => float, 'date' => 'YYYY-MM-DD'] for USD->PHP.
     */
    public function usdToPhp(): array
    {
        return Cache::remember('fx.usd_php.latest', now()->addHours(6), function () {
            $res = Http::retry(3, 500)->get('https://api.frankfurter.dev/v1/latest', [
                'base' => 'USD',
                'symbols' => 'PHP',
            ]);

            $res->throw();

            $rate = data_get($res->json(), 'rates.PHP');
            $date = data_get($res->json(), 'date');

            if (! $rate || ! $date) {
                throw new \RuntimeException('Failed to fetch USD->PHP rate from Frankfurter.');
            }

            return ['rate' => (float) $rate, 'date' => (string) $date];
        });
    }
}
