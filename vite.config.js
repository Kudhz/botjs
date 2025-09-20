// vite.config.js
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          bootstrap: ['bootstrap']
        }
      }
    },
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: false,
        keep_fnames: true,        // ✅ Keep function names
        keep_classnames: true     // ✅ Keep class names
      },
      mangle: {
        keep_fnames: true,        // ✅ Keep function names
        keep_classnames: true     // ✅ Keep class names
      }
    }
  }
})