<?php

return [

    // Nom de l'application

    'name' => env('APP_NAME', 'Laravel'),

    // Environnement de l'application

    'env' => env('APP_ENV', 'production'),

    // Mode debug

    'debug' => (bool) env('APP_DEBUG', false),

    // URL de l'application

    'url' => env('APP_URL', 'http://localhost'),

    // Fuseau horaire

    'timezone' => 'UTC',

    // Configuration des langues

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'fr_FR'),

    // Cle de chiffrement

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    // Mode maintenance

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
