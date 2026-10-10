<?php

// Reglas de negocio de los perfiles, en un solo lugar.
return [
    // Máximo de perfiles por cuenta (plan gratuito)
    'limite' => 2,

    // Nicknames que chocan con rutas del portal (nuevo = sitio de ventas de prueba, ver sitio:generar)
    'reservados' => ['login', 'register', 'dashboard', 'editor', 'activar', 'perfil', 'admin', 'api', 'password', 'configuracion', 'analiticas', 'nuevo', 'build', 'uploads', 'storage'],
];
