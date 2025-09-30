import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
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
    }
});
