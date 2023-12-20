import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
          external: ['laravel-vite-plugin','vite'], // 将 "laravel-vite-plugin" 列为外部依赖项
        },
      },
});
