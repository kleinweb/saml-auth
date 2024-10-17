// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

import laravel from 'laravel-vite-plugin'
import {defineConfig} from 'vite'
import manifestSRI from 'vite-plugin-manifest-sri'

const port = 5173
const origin = `${process.env.DDEV_PRIMARY_URL}:${port}`

export default defineConfig({
  base: './',

  build: {
    assetsDir: '',
    sourcemap: true,
    manifest: 'manifest.json',
    outDir: 'resources/dist',
  },

  plugins: [
    manifestSRI(),
    laravel({
      input: [
        'resources/css/kleinweb-auth-login.css',
        'resources/js/kleinweb-auth-login.ts',
      ],
      refresh: true,
    }),
  ],

  // DDEV compatibility
  // https://vitejs.dev/config/server-options.html
  server: {
    host: '0.0.0.0',
    port,
    strictPort: true,
    origin,
  },
})
