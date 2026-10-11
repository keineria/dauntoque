<?php

// App adicional "Agenda de citas": reglas y precios en un solo lugar.
return [
    // El primer año va gratis con la tarjeta; después se renueva al año (moneda de la tabla markets)
    'dias_gratis' => 365,
    'precio_renovacion' => ['CO' => 99000, 'US' => 39],

    // Zona horaria por país al activar la agenda; en EE. UU. el dueño elige la suya en Ajustes
    'zona_por_pais' => ['CO' => 'America/Bogota', 'US' => 'America/New_York'],
    'zonas' => [
        'America/Bogota' => 'Colombia (Bogotá)',
        'America/New_York' => 'EE. UU. Este (Nueva York, Miami)',
        'America/Chicago' => 'EE. UU. Centro (Chicago, Houston)',
        'America/Denver' => 'EE. UU. Montaña (Denver)',
        'America/Phoenix' => 'EE. UU. Arizona (Phoenix)',
        'America/Los_Angeles' => 'EE. UU. Pacífico (Los Ángeles)',
        'America/Puerto_Rico' => 'Puerto Rico',
    ],

    // Opciones de los ajustes (minutos u horas)
    'intervalos' => [15, 20, 30, 45, 60],

    // Minutos que dura el código de "Mis citas" y cuánto queda abierta la sesión después de usarlo
    'codigo_minutos' => 10,
    'sesion_minutos' => 120,

    // Mensaje de WhatsApp por defecto; {nombre} {servicio} {fecha} {hora} {enlace} se reemplazan
    'mensaje_whatsapp' => 'Hola {nombre}, te recuerdo tu cita de {servicio} el {fecha} a las {hora}. Si necesitas moverla o cancelarla: {enlace}',
];
