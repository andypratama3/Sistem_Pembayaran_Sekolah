import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/app.jsx',
            ],
            refresh: true,
        }),

        react(),
    ],

    build: {
        commonjsOptions: {
            include: [/konva/, /node_modules/],
        },
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-konva': ['konva'],
                    'vendor-react': ['react', 'react-dom'],
                },
            },
        },
    },
});
