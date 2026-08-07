<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Public JSON API is open to any origin for native mobile clients.
    | Flutter mobile apps do not rely on browser CORS; this mainly helps
    | Flutter web / local browser debugging against the same endpoints.
    |
    | When you introduce cookie-based Sanctum SPA auth, set
    | supports_credentials=true and replace allowed_origins=['*'] with
    | an explicit list (credentials + wildcard are incompatible).
    |
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
