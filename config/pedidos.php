<?php

// Compras de tarjetas desde el sitio de ventas.
return [
    // Países con compra en línea (por ahora solo Colombia, con Bold). Los demás siguen por WhatsApp.
    'paises_en_linea' => ['CO'],

    // Correo que recibe el aviso de cada pedido pagado (para despachar la tarjeta)
    'aviso_correo' => env('PEDIDOS_CORREO_AVISO'),

    // Avisos «X compró la tarjeta» del sitio de ventas: solo pedidos pagados reales, con nombre e inicial.
    // Se encienden en /admin/pagos (ajuste avisos_compras). Sin compras en estos días, el sitio no muestra nada.
    'avisos' => ['dias' => 15, 'maximo' => 8],

    'departamentos' => [
        'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bogotá D.C.', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá',
        'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila',
        'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda',
        'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada',
    ],
];
