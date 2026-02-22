import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { viteStaticCopy } from 'vite-plugin-static-copy'

import { VitePWA } from 'vite-plugin-pwa'

// https://vite.dev/config/
export default defineConfig({
  base: '/ops/',
  plugins: [
    vue(),
    viteStaticCopy({
      targets: [
        { src: 'api.php', dest: '.' },
        { src: 'db_config.php', dest: '.' },
        { src: 'api_modules', dest: '.' },
        { src: '.htaccess', dest: '.' }
        // { src: '../image', dest: '.' } // Optional: config image if shared
      ]
    }),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'logo.png', 'logo.webp'],
      manifest: {
        name: 'Sutan Raya Ops',
        short_name: 'Sutan Raya',
        description: 'Sutan Raya Operations Dashboard',
        theme_color: '#ffffff',
        icons: [
          {
            src: 'logo.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'logo.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  server: {
    proxy: {
      '/ops/api.php': {
        target: 'http://localhost/travel-sr-fe/ops/api.php',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/ops/, '')
      },
      '/ops/buktibayar': {
        target: 'http://localhost/travel-sr-fe/ops/buktibayar',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/ops/, '')
      }
    }
  }
})
