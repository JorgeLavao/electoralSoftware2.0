import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: process.env.VITE_DEV_SERVER_HOST || '0.0.0.0',
        port: Number(process.env.VITE_DEV_SERVER_PORT || 5173),
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_CLIENT_HOST || 'localhost',
            port: Number(process.env.VITE_HMR_CLIENT_PORT || 5173),
            protocol: 'ws',
        },
        watch: {
        usePolling: process.env.CHOKIDAR_USEPOLLING === 'true',
        },
        cors: true,
    }
});
