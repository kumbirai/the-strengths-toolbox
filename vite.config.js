import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        // Minify CSS
        cssMinify: 'lightningcss',
        
        // Minify JavaScript
        minify: 'terser',
        
        // Terser options for production
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
            format: {
                comments: false, // Remove comments
            },
        },
        
        // Optimize chunk splitting
        rollupOptions: {
            output: {
                // Manual chunk splitting for better caching
                manualChunks: {
                    'vendor': ['alpinejs'], // Separate vendor chunks
                },
                // Optimize chunk file names
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },
        
        // Source maps for production (optional, set to false for smaller files)
        sourcemap: false,
        
        // Chunk size warning limit
        chunkSizeWarningLimit: 1000,
    },
    
    // Optimize dependencies
    optimizeDeps: {
        include: ['alpinejs'],
    },
});
