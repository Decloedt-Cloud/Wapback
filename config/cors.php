<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173', // Vite
        'http://localhost:3000', // React par défaut
        'http://localhost:8000', // API locale
        'http://preprod.hellowap.com', // Ton environnement distant
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Obligatoire pour Sanctum
];
