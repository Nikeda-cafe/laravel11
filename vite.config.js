import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const devServerHost = process.env.VITE_DEV_SERVER_HOST ?? 'localhost';
const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT ?? 5173);

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: devServerPort,
        hmr: {
            host: devServerHost,
            port: devServerPort,
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
