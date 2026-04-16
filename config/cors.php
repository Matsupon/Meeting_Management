<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    | This is the standard Fruitcake/Laravel CORS config. The 'allowed_origins'
    | key MUST be an array. Our custom HandleCors middleware reads from
    | 'app_cors.allowed_origins' (a separate key) to avoid conflicting with this.
    |
    | See: https://github.com/fruitcake/php-cors
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
