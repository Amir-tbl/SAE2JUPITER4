<?php

return [

    // Authentification par defaut

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    // Configuration CAS INSA
    'cas' => [
    'debug'  => env('INSA_CAS_DEBUG', false),
    'server' => [
        'hostname' => env('INSA_CAS_HOSTNAME', ''),
        'port'     => (int) env('INSA_CAS_PORT', 443),
        'uri'      => env('INSA_CAS_URI', ''),
    ],
    ],

    // Guards d'authentification

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    // Fournisseurs d'utilisateurs

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    // Reinitialisation des mots de passe

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    // Delai d'expiration de confirmation mot de passe

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
