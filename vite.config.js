import { defineConfig } from 'vite';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
            ],
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
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['resources/js/test/setup.js'],
        include: ['resources/js/**/*.spec.js', 'resources/js/**/*.test.js'],
        coverage: {
            provider: process.env.VITEST_COVERAGE_PROVIDER || 'v8',
            reporter: ['text', 'text-summary', 'html', 'lcov'],
            reportsDirectory: 'coverage/vue',
            clean: true,
            include: ['resources/js/**/*.vue', 'resources/js/**/*.js'],
            exclude: [
                'node_modules/',
                '**/*.spec.js',
                '**/*.test.js',
                'resources/js/test/**',
                'resources/js/app.js',
                'resources/js/bootstrap.js',
            ],
        },
    },
});
