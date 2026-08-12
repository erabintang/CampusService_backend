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

    // Origin frontend yang di-deploy (Vercel, tunnel Cloudflare, Railway)
    // otomatis diizinkan — tidak perlu update FRONTEND_URL setiap deploy.
    // CATATAN: pola ini dijalankan via preg_match (fruitcake/php-cors),
    // jadi harus berupa REGEX lengkap dengan delimiter (#), bukan Str::is.
    'allowed_origins_patterns' => [
        '#^https://[a-z0-9-]+\.vercel\.app$#i',
        '#^https://[a-z0-9-]+\.trycloudflare\.com$#i',
        '#^https://[a-z0-9-]+\.railway\.app$#i',
        '#^http://localhost(:\d+)?$#i',
        '#^http://127\.0\.0\.1(:\d+)?$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
