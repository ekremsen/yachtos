<?php

return [
    // This API accepts bearer tokens only, never a web session as a fallback.
    'stateful' => [],
    'guard' => [],
    'expiration' => max(1, (int) env('SANCTUM_EXPIRATION_MINUTES', 1440)),
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];
