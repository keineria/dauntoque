<?php

/*
 * Apariencia del panel (Mi cuenta → Apariencia): paletas de color y modo claro/oscuro.
 * Cada paleta trae sus colores en claro y en oscuro; partials/panel-estilos los convierte en variables CSS
 * y la pantalla de Mi cuenta pinta con ellos las miniaturas para elegir.
 *
 * fondo: fondo de la página · tarjeta: cajas y menús · superficie: zonas suaves · borde · texto · suave: texto secundario
 * tinta: botón principal y selección · acento: segundo color (degradados) · sobre: texto encima de la tinta · enlace
 */
return [
    'paleta_defecto' => 'dauntoque',
    'modo_defecto' => 'oscuro',
    'modos' => ['auto' => 'Automático', 'claro' => 'Claro', 'oscuro' => 'Oscuro'],

    'paletas' => [
        // La de siempre: oscura con brillos azul y violeta
        'dauntoque' => [
            'nombre' => 'DaUnToque', 'descripcion' => 'Azul y violeta',
            'claro' => ['fondo' => '#f3f5fb', 'tarjeta' => '#ffffff', 'superficie' => '#eef1f8', 'borde' => 'rgba(15,23,42,.09)', 'texto' => '#0f172a', 'suave' => '#5b6476', 'tinta' => '#2563eb', 'acento' => '#7c3aed', 'sobre' => '#ffffff', 'enlace' => '#2563eb'],
            'oscuro' => ['fondo' => '#050507', 'tarjeta' => '#121218', 'superficie' => 'rgba(255,255,255,.04)', 'borde' => 'rgba(255,255,255,.07)', 'texto' => '#f5f5f7', 'suave' => '#9ca3af', 'tinta' => '#3b82f6', 'acento' => '#8b5cf6', 'sobre' => '#ffffff', 'enlace' => '#60a5fa'],
        ],
        'grafito' => [
            'nombre' => 'Grafito', 'descripcion' => 'Gris neutro',
            'claro' => ['fondo' => '#f4f4f5', 'tarjeta' => '#ffffff', 'superficie' => '#efeff1', 'borde' => 'rgba(0,0,0,.08)', 'texto' => '#18181b', 'suave' => '#6b6b72', 'tinta' => '#1f2023', 'acento' => '#71717a', 'sobre' => '#ffffff', 'enlace' => '#1f2023'],
            'oscuro' => ['fondo' => '#111113', 'tarjeta' => '#1a1a1d', 'superficie' => '#222226', 'borde' => 'rgba(255,255,255,.08)', 'texto' => '#f4f4f5', 'suave' => '#a1a1aa', 'tinta' => '#e4e4e7', 'acento' => '#a1a1aa', 'sobre' => '#111113', 'enlace' => '#e4e4e7'],
        ],
        'papel' => [
            'nombre' => 'Papel', 'descripcion' => 'Crema cálido',
            'claro' => ['fondo' => '#f6f1ea', 'tarjeta' => '#fffdfa', 'superficie' => '#f2ebe2', 'borde' => 'rgba(60,40,20,.10)', 'texto' => '#241c16', 'suave' => '#7a6c60', 'tinta' => '#2b221d', 'acento' => '#c9875c', 'sobre' => '#ffffff', 'enlace' => '#8a4f2a'],
            'oscuro' => ['fondo' => '#171411', 'tarjeta' => '#211d19', 'superficie' => '#2a2520', 'borde' => 'rgba(255,240,220,.09)', 'texto' => '#f4ece3', 'suave' => '#b3a596', 'tinta' => '#f1e4d4', 'acento' => '#e0a77d', 'sobre' => '#1d1813', 'enlace' => '#f0c9a6'],
        ],
        'salvia' => [
            'nombre' => 'Salvia', 'descripcion' => 'Verde gris',
            'claro' => ['fondo' => '#eef1ec', 'tarjeta' => '#fcfdfb', 'superficie' => '#e9ede6', 'borde' => 'rgba(30,50,30,.10)', 'texto' => '#1c241e', 'suave' => '#66705f', 'tinta' => '#233026', 'acento' => '#6f8f6c', 'sobre' => '#ffffff', 'enlace' => '#3e6b45'],
            'oscuro' => ['fondo' => '#111512', 'tarjeta' => '#1a201b', 'superficie' => '#222a23', 'borde' => 'rgba(220,255,220,.08)', 'texto' => '#eaf0e8', 'suave' => '#9fab9b', 'tinta' => '#d8e6d3', 'acento' => '#8fb98a', 'sobre' => '#121812', 'enlace' => '#b5d6ae'],
        ],
        'marea' => [
            'nombre' => 'Marea', 'descripcion' => 'Azul de agua',
            'claro' => ['fondo' => '#eaf0f7', 'tarjeta' => '#fdfeff', 'superficie' => '#e5ecf5', 'borde' => 'rgba(20,40,70,.10)', 'texto' => '#13202f', 'suave' => '#5c6b7d', 'tinta' => '#142233', 'acento' => '#3f7bb8', 'sobre' => '#ffffff', 'enlace' => '#2a64a3'],
            'oscuro' => ['fondo' => '#0d1420', 'tarjeta' => '#15202f', 'superficie' => '#1c2a3c', 'borde' => 'rgba(200,225,255,.09)', 'texto' => '#e8f0fa', 'suave' => '#9aabbf', 'tinta' => '#cfe1f6', 'acento' => '#6aa5e0', 'sobre' => '#0d1420', 'enlace' => '#93c2f0'],
        ],
        'ciruela' => [
            'nombre' => 'Ciruela', 'descripcion' => 'Malva suave',
            'claro' => ['fondo' => '#f4edf1', 'tarjeta' => '#fffcfd', 'superficie' => '#efe6eb', 'borde' => 'rgba(60,20,50,.10)', 'texto' => '#251a22', 'suave' => '#75636e', 'tinta' => '#2a1e27', 'acento' => '#a35b86', 'sobre' => '#ffffff', 'enlace' => '#8d3f6e'],
            'oscuro' => ['fondo' => '#161015', 'tarjeta' => '#21181f', 'superficie' => '#2b2028', 'borde' => 'rgba(255,220,240,.09)', 'texto' => '#f6ecf2', 'suave' => '#b39fac', 'tinta' => '#f0d6e6', 'acento' => '#d18bb5', 'sobre' => '#1a1218', 'enlace' => '#e7b3d2'],
        ],
        'arcilla' => [
            'nombre' => 'Arcilla', 'descripcion' => 'Terracota tostada',
            'claro' => ['fondo' => '#f5ebe2', 'tarjeta' => '#fffcf8', 'superficie' => '#f1e5da', 'borde' => 'rgba(80,40,20,.11)', 'texto' => '#2a1d15', 'suave' => '#7e6a5b', 'tinta' => '#2c1f17', 'acento' => '#c0663e', 'sobre' => '#ffffff', 'enlace' => '#a54f2b'],
            'oscuro' => ['fondo' => '#17110d', 'tarjeta' => '#221913', 'superficie' => '#2d2119', 'borde' => 'rgba(255,225,200,.09)', 'texto' => '#f7ebe1', 'suave' => '#b8a291', 'tinta' => '#f3dccb', 'acento' => '#e08a5f', 'sobre' => '#1a120d', 'enlace' => '#f0b393'],
        ],
        'glicina' => [
            'nombre' => 'Glicina', 'descripcion' => 'Violeta azulado',
            'claro' => ['fondo' => '#eeeef8', 'tarjeta' => '#fdfdff', 'superficie' => '#e8e8f5', 'borde' => 'rgba(30,30,80,.10)', 'texto' => '#1c1c2e', 'suave' => '#66667f', 'tinta' => '#1e1e30', 'acento' => '#6d5fd6', 'sobre' => '#ffffff', 'enlace' => '#5146c4'],
            'oscuro' => ['fondo' => '#111120', 'tarjeta' => '#1a1a2d', 'superficie' => '#22223a', 'borde' => 'rgba(220,220,255,.09)', 'texto' => '#ececfa', 'suave' => '#a3a3c2', 'tinta' => '#dcdcfa', 'acento' => '#9c90f0', 'sobre' => '#12121f', 'enlace' => '#bdb4ff'],
        ],
    ],
];
