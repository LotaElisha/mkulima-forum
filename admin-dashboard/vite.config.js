import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  base: '/admin/',
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: './src/test/setup.js'
  },
  server: {
    port: 3020,
    host: '0.0.0.0',
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  },
  build: {
    // Filament serves /admin. A build must not land in public/admin or nginx
    // will serve this bundle instead of the panel.
    outDir: 'dist',
    emptyOutDir: true,
    assetsDir: 'assets'
  }
})
