
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
    VitePWA({
      // We call registerSW() ourselves (src/pwa.js) so we control when the
      // "new version" prompt shows.
      registerType: 'prompt',
      injectRegister: false,

      includeAssets: ['favicon-32.png', 'favicon-192.png', 'apple-touch-icon.png'],

      manifest: {
        name: 'Bizaa POS',
        short_name: 'Bizaa POS',
        description:
          'Point of sale for Bizaa — sell, take payment and print receipts, online or offline.',
        // The installed app opens straight on the till, not the dashboard.
        start_url: '/sales/pos?source=pwa',
        scope: '/',
        display: 'standalone',
        orientation: 'any',
        background_color: '#ffffff',
        theme_color: '#229de7',
        icons: [
          { src: '/favicon-192.png', sizes: '192x192', type: 'image/png' },
          { src: '/favicon-512.png', sizes: '512x512', type: 'image/png' },
          {
            src: '/favicon-512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'maskable',
          },
        ],
      },

      workbox: {
        globPatterns: ['**/*.{js,css,html,woff2}'],
        maximumFileSizeToCacheInBytes: 4 * 1024 * 1024,

        // Offline navigations fall back to the cached SPA shell — but ONLY
        // for the POS route. Every other route still needs the network; we
        // don't claim the rest of the app works offline.
        navigateFallback: '/index.html',
        navigateFallbackAllowlist: [/^\/sales\/pos/],
        navigateFallbackDenylist: [/^\/api/, /^\/superadmin/],

        runtimeCaching: [
          {
            // Google Fonts stylesheet + font files (index.html loads Inter
            // and Material Icons from here) so the POS renders correctly
            // with no connection.
            urlPattern: ({ url }) =>
              url.origin === 'https://fonts.googleapis.com' ||
              url.origin === 'https://fonts.gstatic.com',
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts',
              expiration: { maxEntries: 30, maxAgeSeconds: 60 * 60 * 24 * 365 },
              cacheableResponse: { statuses: [0, 200] },
            },
          },
        ],
      },

      devOptions: {
        // Keep the SW out of `npm run dev` — it caches aggressively and
        // gets in the way of HMR. Test the PWA against `npm run build` +
        // `npm run preview`.
        enabled: false,
      },
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '@pages': path.resolve(__dirname, './src/pages'),
    },
  },
})
