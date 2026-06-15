<?php

use Illuminate\Support\Str;

return [

    // Driver de session par defaut

    'driver' => env('SESSION_DRIVER', 'database'),

    // Duree de vie de la session

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    // Chiffrement de la session

    'encrypt' => env('SESSION_ENCRYPT', false),

    // Emplacement des fichiers de session

    'files' => storage_path('framework/sessions'),

    // Connexion base de donnees pour la session

    'connection' => env('SESSION_CONNECTION'),

    // Table de session en base de donnees

    'table' => env('SESSION_TABLE', 'sessions'),

    // Store de cache pour la session

    'store' => env('SESSION_STORE'),

    // Loterie de nettoyage des sessions

    'lottery' => [2, 100],

    // Nom du cookie de session

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    // Chemin du cookie de session

    'path' => env('SESSION_PATH', '/'),

    // Domaine du cookie de session

    'domain' => env('SESSION_DOMAIN'),

    // Cookies HTTPS uniquement

    'secure' => env('SESSION_SECURE_COOKIE'),

    // Acces HTTP uniquement

    'http_only' => env('SESSION_HTTP_ONLY', true),

    // Politique Same-Site des cookies

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    // Cookies partitionnes

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
