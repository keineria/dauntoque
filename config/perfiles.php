<?php

// Reglas de negocio de los perfiles, en un solo lugar.
return [
    // Dirección pública de los perfiles: la que abre la tarjeta y la que se comparte (dauntoque.com/keiner).
    // En dauntoque.com, el .htaccess que crea sitio:generar manda al portal toda ruta que no sea del sitio de ventas.
    'url_publica' => rtrim(env('PERFIL_URL_PUBLICA', env('APP_URL', 'http://localhost')), '/'),

    // Máximo de perfiles por cuenta (plan gratuito)
    'limite' => 2,

    // Nicknames que chocan con rutas o archivos del portal (nuevo = sitio de ventas de prueba, ver sitio:generar)
    'reservados' => [
        'login', 'register', 'logout', 'dashboard', 'editor', 'activar', 'perfil', 'admin', 'api', 'password',
        'forgot-password', 'reset-password', 'verify-email', 'confirm-password', 'email', 'up',
        'configuracion', 'analiticas', 'nuevo', 'build', 'uploads', 'storage', 'renovar', 'webhooks', 'comprar', 'pedido',
        'robots.txt', 'sitemap.xml', 'favicon.ico', 'index.php',
        // Carpetas y archivos del sitio de ventas en dauntoque.com (ver sitio:generar)
        'es-co', 'es-us', 'en-us', 'media', 'sitio.css', 'sitio.js', 'index.html', '.htaccess', '.well-known',
    ],

    // Tipos de perfil que elige el editor (paso 2)
    'tipos' => ['corporativo', 'empresa', 'hobbies'],
];
