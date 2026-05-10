<?php

return [
    'prediction_iterations' => (int) env('LEAGUE_PREDICTION_ITERATIONS', 10000),

    'prediction_cache' => [
        'enabled' => (bool) env('LEAGUE_PREDICTION_CACHE_ENABLED', true),
        'ttl_seconds' => (int) env('LEAGUE_PREDICTION_CACHE_TTL', 600),
        'prefix' => env('LEAGUE_PREDICTION_CACHE_PREFIX', 'league:predictions'),
    ],
];
