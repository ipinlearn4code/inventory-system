import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: { host: 'localhost' }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/dashboard-theme-improved.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
