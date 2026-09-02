/**
 * Network calls for the offline POS.
 *
 * Deliberately uses its OWN axios instance, not the app-wide one: the global
 * instance redirects to /login on any 401 (main.js interceptor), which would
 * destroy the cashier's session mid-shift if a token happened to expire while
 * the outbox drainer was running. Here a 401 is just a failed drain we retry
 * after re-auth.
 */

import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { API_BASE_URL } from '@/config'

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
})

client.interceptors.request.use((config) => {
  const token = useAuthStore().token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

/** Lightweight reachability + auth probe. Resolves true/false, never throws. */
export async function pingServer() {
  try {
    await client.get('/pos/ping', { timeout: 6000 })
    return true
  } catch {
    return false
  }
}

/** Pull the offline snapshot for a branch. Throws on any failure. */
export async function fetchBootstrap(branchId) {
  const { data } = await client.get('/pos/offline-bootstrap', {
    params: { branch_id: branchId },
  })
  return data
}

/**
 * Push one queued sale. The backend is idempotent on `client_sale_id`, so a
 * retried sale returns the original row instead of duplicating.
 *
 * @returns {{ ok: true, sale: object } | { ok: false, retryable: boolean, status?: number, message: string }}
 */
export async function pushSale(payload) {
  try {
    const { data } = await client.post('/sales/pos-sale', payload)
    return { ok: true, sale: data?.data ?? data ?? {} }
  } catch (err) {
    const status = err.response?.status

    // No response at all → still offline / server unreachable → retry later.
    if (!err.response) {
      return { ok: false, retryable: true, message: 'network unreachable' }
    }

    // 401/403 (auth) and 5xx (server) are worth retrying once the situation
    // clears. 409/422 and other 4xx are permanent for this payload — the sale
    // needs a human to look at it, so park it as "failed" rather than loop.
    const retryable = status === 401 || status === 403 || status >= 500

    return {
      ok: false,
      retryable,
      status,
      message: err.response?.data?.message || `request failed (${status})`,
    }
  }
}
