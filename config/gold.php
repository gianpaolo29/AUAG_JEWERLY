<?php

return [
    // FreeGoldAPI endpoint (no key)
    'api_url' => env('GOLD_API_URL', 'https://freegoldapi.com/data/latest.json'),

    // IMPORTANT:
    // FreeGoldAPI contains annual + monthly + daily segments.
    // To keep forecasting consistent for a daily model, we will train only on recent daily data.
    // FreeGoldAPI says daily data is available for 2025-present. :contentReference[oaicite:4]{index=4}
    'daily_start' => env('GOLD_DAILY_START', '2025-01-01'),

    // Where to store the trained model file
    'model_path' => storage_path('app/models/gold_forecast.rbx'),


    // How many past days to use as input features
    'lookback' => (int) env('GOLD_LOOKBACK', 60),
];
