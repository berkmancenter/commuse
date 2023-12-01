import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'url'
import tryImportLocalComponents from './plugins/tryImportLocalComponents'

export default defineConfig({
  plugins: [vue(), tryImportLocalComponents()],
  server: {
    host: '0.0.0.0',
    port: 5175,
  },
  resolve: {
    alias: [
      { find: '@', replacement: fileURLToPath(new URL('./src', import.meta.url)) },
    ],
  },
  build: {
    assetsDir: 'front_assets',
  },
})
