<?php

return [
    'provider' => env('GOLD_PROVIDER', 'metals_dev'),
    'daily_start' => env('GOLD_DAILY_START'),
    'model_path' => env('GOLD_MODEL_PATH', storage_path('app/gold/gold-model.rbx')),


    'metals_dev' => [
        'base_url' => 'https://api.metals.dev/v1',
        'api_key'  => env('METALS_DEV_API_KEY'),
    ],
];
