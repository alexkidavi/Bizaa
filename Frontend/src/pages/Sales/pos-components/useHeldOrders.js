/**
 * useHeldOrders
 *
 * Persists held orders in localStorage with a 6-hour TTL.
 * Each order is stored with an `expiresAt` timestamp.
 * On load, expired orders are pruned automatically.
 *
 * Storage key: "bizaa_held_orders"
 * TTL:         6 hours (configurable via TTL_MS)
 */

import { ref } from 'vue'

const STORAGE_KEY = 'bizaa_held_orders'
const TTL_MS      = 6 * 60 * 60 * 1000   // 6 hours in milliseconds

/* ── helpers ──────────────────────────── */

function readFromStorage() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return []
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []

    const now = Date.now()
    // Filter out expired orders on read
    return parsed.filter(o => o.expiresAt && o.expiresAt > now)
  } catch {
    return []
  }
}

function writeToStorage(orders) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(orders))
  } catch {
    // Storage full or unavailable — fail silently
  }
}

function removeFromStorage() {
  try {
    localStorage.removeItem(STORAGE_KEY)
  } catch { /* */ }
}

/* ── composable ───────────────────────── */

export function useHeldOrders() {
  // Initialise from storage, pruning expired orders immediately
  const heldOrders = ref(readFromStorage())

  /**
   * persist — writes current state to localStorage synchronously.
   * Called immediately inside every mutation so a refresh mid-tick
   * never loses data.
   */
  const persist = (orders) => {
    if (orders.length === 0) {
      removeFromStorage()
    } else {
      // Strip Vue internal / non-serializable flags before storing
      const clean = orders.map(o => ({
        ...o,
        items: o.items.map(item => {
          const { _new, _showDiscount, ...rest } = item
          return rest
        }),
      }))
      writeToStorage(clean)
    }
  }

  /**
   * holdOrder
   * Saves the current cart as a held order with a 6-hour expiry.
   *
   * @param {object} orderData - { ref, customer, items, subtotal, total }
   */
  const holdOrder = (orderData) => {
    const now  = Date.now()
    const held = {
      ...orderData,
      heldAt:    now,
      expiresAt: now + TTL_MS,
      time: new Date(now).toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' }),
    }
    const updated = [...heldOrders.value, held]
    heldOrders.value = updated
    persist(updated)          // ← synchronous write, survives immediate refresh
  }

  /**
   * resumeOrder
   * Returns the order at `index` and removes it from the list.
   *
   * @param {number} index
   * @returns {object} the held order
   */
  const resumeOrder = (index) => {
    const order   = heldOrders.value[index]
    const updated = heldOrders.value.filter((_, i) => i !== index)
    heldOrders.value = updated
    persist(updated)
    return order
  }

  /**
   * deleteHeldOrder
   * Permanently removes a held order.
   *
   * @param {number} index
   */
  const deleteHeldOrder = (index) => {
    const updated = heldOrders.value.filter((_, i) => i !== index)
    heldOrders.value = updated
    persist(updated)
  }

  /**
   * pruneExpired
   * Manually removes expired orders. Called automatically on init,
   * but can also be called periodically.
   */
  const pruneExpired = () => {
    const now     = Date.now()
    const updated = heldOrders.value.filter(o => o.expiresAt > now)
    heldOrders.value = updated
    persist(updated)
  }

  /**
   * timeRemaining
   * Returns a human-readable string of how long until an order expires.
   *
   * @param {object} order
   * @returns {string}  e.g. "5h 42m" or "23m"
   */
  const timeRemaining = (order) => {
    const diff = order.expiresAt - Date.now()
    if (diff <= 0) return 'Expired'
    const totalMins = Math.floor(diff / 60000)
    const hours     = Math.floor(totalMins / 60)
    const mins      = totalMins % 60
    if (hours > 0) return `${hours}h ${mins}m`
    return `${mins}m`
  }

  return {
    heldOrders,
    holdOrder,
    resumeOrder,
    deleteHeldOrder,
    pruneExpired,
    timeRemaining,
  }
}
