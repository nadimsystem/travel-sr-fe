import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  base: '/review/', // Ensure assets are loaded correctly from subdirectory
  server: {
    proxy: {
      '/api_review.php': 'http://localhost/travel-sr-fe/review/api_review.php'
    }
  }
})
