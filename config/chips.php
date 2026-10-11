<?php

// Registro de chips NFC vendidos: solo los códigos registrados se pueden activar.
return [
    // Apagar solo en una emergencia (CHIPS_VALIDAR=false): cualquier código volvería a activarse
    'validar' => env('CHIPS_VALIDAR', true),

    // Correos con acceso a /admin (separados por coma), por ejemplo: ADMIN_CORREOS=keiner@dauntoque.com
    'admins' => array_values(array_filter(array_map(fn ($c) => strtolower(trim($c)), explode(',', (string) env('ADMIN_CORREOS', ''))))),

    // Largo de los códigos que genera el panel (letras minúsculas y números: imposibles de adivinar)
    'largo_codigo' => 12,
];
