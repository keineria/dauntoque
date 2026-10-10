<?php

// Reglas de negocio de los perfiles, en un solo lugar.
return [
    // Máximo de perfiles por cuenta (plan gratuito)
    'limite' => 2,

    // Nicknames que chocan con rutas o archivos del portal (nuevo = sitio de ventas de prueba, ver sitio:generar)
    'reservados' => [
        'login', 'register', 'logout', 'dashboard', 'editor', 'activar', 'perfil', 'admin', 'api', 'password',
        'forgot-password', 'reset-password', 'verify-email', 'confirm-password', 'email', 'up',
        'configuracion', 'analiticas', 'nuevo', 'build', 'uploads', 'storage', 'renovar', 'webhooks', 'comprar', 'pedido',
        'robots.txt', 'sitemap.xml', 'favicon.ico', 'index.php',
    ],

    // Tipos de perfil que elige el editor (paso 2)
    'tipos' => ['corporativo', 'empresa', 'hobbies'],
];
