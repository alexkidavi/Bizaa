/**
 * Resolves where the backend lives.
 *
 * `VITE_APP_URL` is optional. When it's empty (the recommended production
 * setup, where the SPA and the API are served from the same domain), every
 * URL below is **relative** — the app talks to whatever origin served it, so
 * it can't be pointed at a wrong/unreachable host by a stale build.
 *
 * Set `VITE_APP_URL` only when the API is on a *different* origin than the
 * SPA (e.g. `http://127.0.0.1:8000` in local dev, or a separate `api.`
 * subdomain in production).
 */

const raw = (import.meta.env.VITE_APP_URL ?? '').trim().replace(/\/+$/, '')

/** Backend origin, or '' for same-origin. e.g. '' or 'https://api.example.com' */
export const APP_ORIGIN = raw

/** Base for API calls. '' + '/api' → '/api' (relative). */
export const API_BASE_URL = `${raw}/api`

/** Base for backend-served assets (uploaded logos, receipts under /storage). */
export const ASSET_BASE_URL = raw
