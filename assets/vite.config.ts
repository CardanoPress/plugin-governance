import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'
import themeplate from 'vite-plugin-themeplate'

// https://vitejs.dev/config/
export default defineConfig({
    base: '/',
    build: {
        target: 'es2020',
        assetsDir: '',
        rollupOptions: {
            input: {
                script: './src/main.ts',
                style: './src/main.css',
            },
            output: {
                globals: {
                    alpinejs: 'Alpine',
                },
            },
            external: ['alpinejs'],
        },
    },
    plugins: [
        tailwindcss(),
        themeplate(),
    ],
})
