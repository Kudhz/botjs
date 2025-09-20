import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    // Optimize build output
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true, // Remove console.log in production
        drop_debugger: true,
      },
    },
    // Split chunks for better caching
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          bootstrap: ['bootstrap', '@popperjs/core'],
        },
      },
    },
    // Chunk size warnings
    chunkSizeWarningLimit: 600,
  },
  // Optimization during development
  optimizeDeps: {
    include: ['react', 'react-dom', 'bootstrap'],
  },
})
