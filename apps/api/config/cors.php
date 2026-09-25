<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',',
        env('CORS_ALLOWED_ORIGINS', env('APP_ENV') === 'local'
            ? 'http://localhost:3000,http://127.0.0.1:3000' : '')
    )))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Content-Type', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
