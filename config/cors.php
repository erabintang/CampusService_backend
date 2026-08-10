<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Mengizinkan frontend Next.js (http://127.0.0.1:3000) memanggil API
    | Laravel lintas origin sambil membawa session cookie (credentials).
    | Same-site tetap aman karena host sama (127.0.0.1), hanya port berbeda.
    |
    */

    'paths' => ['api/*', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://127.0.0.1:3000', 'http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
