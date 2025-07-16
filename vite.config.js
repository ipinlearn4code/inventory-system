import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: (() => {
            try {
                // Attempt to use the specified IP
                return { host: '192.168.2.69' };
            } catch (error) {
                // Fallback to localhost if there's an issue
                console.error('Error setting HMR host, falling back to localhost:', error);
                return { host: 'localhost' };
            }
        })(),
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
