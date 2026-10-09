import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // panel.css: panel, editor y perfil público (Tailwind local en lugar del CDN)
            input: ['resources/css/app.css', 'resources/css/panel.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
