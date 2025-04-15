<?php

/*
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // 'allowed_origins' => ['http://localhost:3000', 'https://front-badge-chi.vercel.app'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Autorise les cookies
];*/
/*
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'https://apirem.r4client.fr', 
        'https://front-badge.vercel.app', 
        'https://app.rem-distribution.com', 
        'https://preprod.rem-distribution.com', 
        'https://front-badge-chi.vercel.app',
        'https://preprodfront-badge.vercel.app'], 
        // // Ajoutez vos domaines frontend
    'allowed_headers' => ['*'],
    'allowed_credentials' => true, // Important pour les cookies cross-origin
    'exposedHeaders' => ['Authorization'], // Vous pouvez ajouter des en-têtes exposés si nécessaire
    'max_age' => 0,
    'supports_credentials' => true, // Assurez-vous que c'est true
];*/
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'https://apirem.r4client.fr', 
        'https://front-badge.vercel.app', 
        'https://app.rem-distribution.com', 
        'https://preprod.rem-distribution.com', 
        'https://front-badge-chi.vercel.app',
        'https://preprodfront-badge.vercel.app',
        'https://app.aeropaperasse.fr'
    ], 
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];
/*
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    //'allowed_origins' => ['http://localhost:3000','https://front-badge-nruyy0qpf-dupins-projects.vercel.app'], // URL de votre frontend React
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];*/