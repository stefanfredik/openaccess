import { readdirSync, statSync } from 'fs'
import { join, relative, dirname } from 'path'
import { fileURLToPath } from 'url'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    outDir: '../../public/build-company',
    emptyOutDir: true,
    manifest: true,
  },
  plugins: [
    laravel({
      publicDirectory: '../../public',
      buildDirectory: 'build-company',
      input: [__dirname + '/resources/assets/sass/app.scss', __dirname + '/resources/assets/js/app.js'],
      refresh: true,
    }),
  ],
})
