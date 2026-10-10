<?php

// Servicio anual de la tarjeta NFC: empieza el día que el cliente activa su tarjeta (no el día de la compra).
return [
    // Días de servicio que da la compra y cada renovación
    'dias' => 365,

    // Desde cuántos días antes del vencimiento se muestra el aviso "Renueva pronto"
    'aviso_dias' => 30,

    // Precio de la renovación anual por país (moneda de la tabla markets)
    'precio_renovacion' => ['CO' => 120000, 'US' => 55],

    // Días que el perfil sigue visible después de vencer
    'gracia_dias' => 15,
];
