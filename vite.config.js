import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Fonts are downloaded at build time and served from our own server.
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600, 700],
                    optimizedFallbacks: false,
                    preload: [{ weight: 400 }],
                }),
                bunny('Bricolage Grotesque', {
                    weights: [600, 700, 800],
                    optimizedFallbacks: false,
                    preload: [{ weight: 800 }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
