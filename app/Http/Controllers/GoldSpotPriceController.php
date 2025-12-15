<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GoldSpotPriceController extends Controller
{
    public function __invoke()
    {
        $apiKey   = config('services.goldapi.key', env('GOLDAPI_KEY'));
        $usdToPhp = (float) config('services.goldapi.usd_to_php', env('USD_TO_PHP_RATE', 58));
        $endpoint = 'https://www.goldapi.io/api/XAU/USD';

        if (!$apiKey) {
            return response()->json([
                'error'   => 'missing_api_key',
                'message' => 'GOLDAPI_KEY is not configured.',
            ], 500);
        }

        try {
            // Cache for 1 day – only 1 external call per day
            $payload = Cache::remember('gold_spot_price_daily', now()->addDay(), function () use ($apiKey, $usdToPhp, $endpoint) {
                $response = Http::withHeaders([
                    'x-access-token' => $apiKey,
                    'Content-Type'   => 'application/json',
                ])->timeout(10)->get($endpoint);

                if ($response->failed()) {
                    throw new \RuntimeException(
                        'GoldAPI request failed: '.$response->status().' '.$response->body()
                    );
                }

                $data     = $response->json();
                $usdPerOz = $data['price'] ?? null;

                if ($usdPerOz === null) {
                    throw new \RuntimeException('GoldAPI response missing "price" field.');
                }

                $phpPerOz = $usdPerOz * $usdToPhp;

                return [
                    'metal'            => $data['metal'] ?? 'XAU',
                    'source_currency'  => $data['currency'] ?? 'USD',
                    'usd_per_ounce'    => round($usdPerOz, 2),
                    'php_per_ounce'    => round($phpPerOz, 2),
                    'usd_to_php_rate'  => $usdToPhp,
                    'timestamp'        => $data['timestamp'] ?? null,
                    'prev_close_price' => $data['prev_close_price'] ?? null,
                    'open_price'       => $data['open_price'] ?? null,
                    // extra field for your UI if you want it
                    'updated_at'       => now()->toDateTimeString(),
                ];
            });

            return response()->json($payload);

        } catch (\Throwable $e) {
            \Log::error('Gold spot price error', ['error' => $e->getMessage()]);

            return response()->json([
                'error'   => 'exception',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
