import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // The dev server is a different origin (127.0.0.1:5173) than the tenant domains
    // (*.aeropaperasse.test), so its assets are loaded cross-origin. Allow CORS so the
    // Vite/HMR modules and CSS aren't blocked by the browser when running `npm run dev`.
    server: {
        host: '127.0.0.1',
        cors: {
            origin: /https?:\/\/([a-z0-9-]+\.)?aeropaperasse\.test(:\d+)?$/,
        },
    },
});
