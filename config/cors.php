<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Origin frontend yang diizinkan memanggil API Laravel ini sambil membawa
    | session cookie (credentials).
    |
    | - Lokal (default): http://127.0.0.1:3000 dan http://localhost:3000.
    | - Produksi: set FRONTEND_URL di .env, dipisah koma, misalnya
    |   FRONTEND_URL=https://campus-service.vercel.app,https://staging.vercel.app
    |
    */

    'paths' => ['api/*', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FRONTEND_URL', 'http://127.0.0.1:3000,http://localhost:3000'))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
