<?php

// Tipografías del perfil público (design_config.tipografia). La clave vacía ('') deja la letra que trae cada estilo.
// titulo = nombre y letras grandes · texto = el resto · google = familias de Google Fonts que hay que cargar.
// Los id no cambian (los usan los perfiles guardados); solo el nombre visible se puede editar.
return [
    'inter' => ['nombre' => 'Inter', 'desc' => 'Moderna y limpia', 'titulo' => '"Inter", sans-serif', 'texto' => '"Inter", sans-serif',
        'google' => ['Inter:wght@400;500;600;700;800;900']],
    'poppins' => ['nombre' => 'Poppins', 'desc' => 'Amigable y redonda', 'titulo' => '"Poppins", sans-serif', 'texto' => '"Poppins", sans-serif',
        'google' => ['Poppins:wght@400;500;600;700;800']],
    'montserrat' => ['nombre' => 'Montserrat', 'desc' => 'Ejecutiva y firme', 'titulo' => '"Montserrat", sans-serif', 'texto' => '"Montserrat", sans-serif',
        'google' => ['Montserrat:wght@400;500;600;700;800;900']],
    'playfair' => ['nombre' => 'Playfair', 'desc' => 'Elegante con serifa', 'titulo' => '"Playfair Display", Georgia, serif', 'texto' => '"Inter", sans-serif',
        'google' => ['Playfair+Display:wght@600;700;800;900', 'Inter:wght@400;500;600;700;800']],
    'lora' => ['nombre' => 'Lora', 'desc' => 'Editorial y cálida', 'titulo' => '"Lora", Georgia, serif', 'texto' => '"Inter", sans-serif',
        'google' => ['Lora:wght@500;600;700', 'Inter:wght@400;500;600;700;800']],
    'dmserif' => ['nombre' => 'DM Serif', 'desc' => 'Clásica de revista', 'titulo' => '"DM Serif Display", Georgia, serif', 'texto' => '"DM Sans", sans-serif',
        'google' => ['DM+Serif+Display', 'DM+Sans:wght@400;500;600;700;800']],
    'cormorant' => ['nombre' => 'Cormorant', 'desc' => 'Lujo y joyería', 'titulo' => '"Cormorant Garamond", Georgia, serif', 'texto' => '"Montserrat", sans-serif',
        'google' => ['Cormorant+Garamond:wght@600;700', 'Montserrat:wght@400;500;600;700;800']],
    'raleway' => ['nombre' => 'Raleway', 'desc' => 'Fina y sofisticada', 'titulo' => '"Raleway", sans-serif', 'texto' => '"Raleway", sans-serif',
        'google' => ['Raleway:wght@400;500;600;700;800;900']],
    'spacegrotesk' => ['nombre' => 'Space Grotesk', 'desc' => 'Tecnológica', 'titulo' => '"Space Grotesk", sans-serif', 'texto' => '"Space Grotesk", sans-serif',
        'google' => ['Space+Grotesk:wght@400;500;600;700']],
    'outfit' => ['nombre' => 'Outfit', 'desc' => 'Geométrica y actual', 'titulo' => '"Outfit", sans-serif', 'texto' => '"Outfit", sans-serif',
        'google' => ['Outfit:wght@400;500;600;700;800;900']],
    'nunito' => ['nombre' => 'Nunito', 'desc' => 'Suave y cercana', 'titulo' => '"Nunito", sans-serif', 'texto' => '"Nunito", sans-serif',
        'google' => ['Nunito:wght@400;600;700;800;900']],
    'bebas' => ['nombre' => 'Bebas Neue', 'desc' => 'Impacto deportivo', 'titulo' => '"Bebas Neue", Impact, sans-serif', 'texto' => '"Inter", sans-serif',
        'google' => ['Bebas+Neue', 'Inter:wght@400;500;600;700;800']],
];
