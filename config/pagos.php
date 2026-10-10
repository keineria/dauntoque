<?php

/*
 * Medios de pago que un perfil puede mostrar, por país.
 * Los países (moneda, idioma, WhatsApp de ventas) viven en la tabla markets;
 * aquí solo se dice qué medios pide La Forja en cada uno y cómo se pinta cada botón.
 *
 * accion: 'copiar' abre una ventana con el dato para copiarlo; 'enlace' abre la app con "enlace" + valor.
 */
return [
    'por_pais' => [
        'CO' => ['nequi', 'llave_breb'],
        'US' => ['zelle', 'cashapp', 'venmo'],
    ],

    'pais_defecto' => 'CO',

    'medios' => [
        'nequi' => [
            'nombre' => 'Nequi', 'icono' => 'N', 'fondo' => '#da0081', 'texto' => '#ffffff', 'acento' => '#da0081',
            'accion' => 'copiar', 'placeholder' => 'Celular de 10 dígitos', 'inputmode' => 'numeric',
            'ayuda' => ['es' => 'Abre Nequi y envía el pago a este número:', 'en' => 'Open Nequi and send the payment to this number:'],
        ],
        'llave_breb' => [
            'nombre' => 'Llave Bre-B', 'icono' => 'B', 'fondo' => '#0f4c81', 'texto' => '#ffffff', 'acento' => '#2f86d6',
            'accion' => 'copiar', 'placeholder' => 'Celular, correo, cédula o @llave', 'inputmode' => 'text',
            'ayuda' => ['es' => 'Abre tu banco, elige Bre-B y usa esta llave:', 'en' => 'Open your bank app, choose Bre-B and use this key:'],
        ],
        'zelle' => [
            'nombre' => 'Zelle', 'icono' => 'Z', 'fondo' => 'linear-gradient(#8621a1,#741b8c)', 'texto' => '#ffffff', 'acento' => '#8b3fe0',
            'accion' => 'copiar', 'placeholder' => 'Correo o teléfono', 'inputmode' => 'email',
            'ayuda' => ['es' => 'Abre tu banco y envía el pago a:', 'en' => 'Open your bank app and send the payment to:'],
        ],
        'cashapp' => [
            'nombre' => 'Cash App', 'icono' => '$', 'fondo' => '#00d632', 'texto' => '#06210f', 'acento' => '#00b82b',
            'accion' => 'enlace', 'enlace' => 'https://cash.app/$', 'quitar' => '$', 'placeholder' => '$Cashtag', 'inputmode' => 'text',
        ],
        'venmo' => [
            'nombre' => 'Venmo', 'icono' => 'V', 'fondo' => '#008cff', 'texto' => '#ffffff', 'acento' => '#008cff',
            'accion' => 'enlace', 'enlace' => 'https://venmo.com/u/', 'quitar' => '@', 'placeholder' => '@usuario', 'inputmode' => 'text',
        ],
    ],
];
