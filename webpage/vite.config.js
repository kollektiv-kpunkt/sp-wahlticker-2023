import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/css/admin.scss',
                'resources/js/admin.js',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            host: "spticker.ddev.site",
            protocol: "wss"
        }
    },
    resolve: {
        alias: {
            "@fonts": "/resources/css/typography/fonts",
        },
    },
    esbuild: {
        drop: ["console", "debugger"]
    }
});
