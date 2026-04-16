<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application-level CORS Allowed Origins (for custom HandleCors middleware)
    |--------------------------------------------------------------------------
    | In production, set CORS_ALLOWED_ORIGINS in .env to a comma-separated
    | list of allowed origins (e.g. "https://example.com,https://www.example.com").
    | Leave unset to fall back to APP_URL. In non-production, '*' is used.
    |
    */
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', env('APP_URL', 'http://localhost')),
];
