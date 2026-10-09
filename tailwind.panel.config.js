/**
 * Tailwind del panel, el editor y el perfil público.
 * Misma base que el CDN que se usaba antes (tema por defecto, sin el plugin de formularios de Breeze),
 * para que el aspecto no cambie al compilarlo en local.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './resources/views/layouts/app.blade.php',
        './resources/views/dashboard/**/*.blade.php',
        './resources/views/partials/**/*.blade.php',
        './resources/views/components/mini-perfil.blade.php',
        './resources/views/editor.blade.php',
        './resources/views/public/**/*.blade.php',
    ],
    // Formas de botón que llegan desde la base de datos (design_config.forma_botones)
    safelist: ['rounded-none', 'rounded-md', 'rounded-xl', 'rounded-2xl', 'rounded-full', '-skew-x-12'],
    theme: { extend: {} },
    plugins: [],
};
