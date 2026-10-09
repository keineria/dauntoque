<?php

// Reglas de negocio de los perfiles, en un solo lugar.
return [
    // Máximo de perfiles por cuenta (plan gratuito)
    'limite' => 2,

    // Nicknames que chocan con rutas del portal
    'reservados' => ['login', 'register', 'dashboard', 'editor', 'activar', 'perfil', 'admin', 'api', 'password', 'configuracion', 'analiticas'],
];
