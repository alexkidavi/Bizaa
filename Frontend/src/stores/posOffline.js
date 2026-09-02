/**
 * Offline-POS coordinator.
 *
 * Owns: connectivity state, the cached snapshot's freshness, and the outbox
 * of sales rung up while disconnected. POS.vue reads catalogue data through
 * here when offline and enqueues sales here at checkout.
 *
 * Scope is the POS only — nothing else in the app touches this store.
 */

import { defineStore } from 'pinia'
import * as offlineDb from '@/services/offline/db'
import { fetchBootstrap, pushSale, pingServer } from '@/services/offline/PosSyncService'

const RECOVERY_INTERVAL_MS = 20 * 1000 // how often to probe while offline / with a backlog

const STALE_AFTER_MS = 12 * 60 * 60 * 1000 // 12h → show "snapshot getting old"

export const usePosOffline = defineStore('posOffline', {
  state: () => ({
    online: navigator.onLine,
    activeBranchId: null,

    snapshotMeta: null, // { syncedAt, business, taxRate, loyalty, counts, productsTruncated }
    refreshing: false,

    outboxCount: 0,
    failedCount: 0,
    draining: false,
    lastSyncError: null,

    _wired: false,
    _recoveryTimer: null,
  }),

  getters: {
    isOnline: (s) => s.online,
    hasSnapshot: (s) => !!s.snapshotMeta,
    snapshotAgeMs: (s) =>
      s.snapshotMeta ? Date.now() - new Date(s.snapshotMeta.syncedAt).getTime() : Infinity,
    isSnapshotStale() {
      return this.snapshotAgeMs > STALE_AFTER_MS
    },
    // Payment methods that make sense with no connection. Anything needing a
    // live server call (M-Pesa STK, loyalty points redemption, split) is out.
    offlinePaymentMethods: () => ['cash', 'credit'],
  },

  actions: {
    async init(branchId) {
      this.activeBranchId = Number(branchId)
      await offlineDb.requestPersistence()
      await this.loadMeta()
      await this.refreshOutboxCounters()

      if (!this._wired) {
        window.addEventListener('online', this._onOnline)
        window.addEventListener('offline', this._onOffline)
        this._wired = true
      }

      if (!this._recoveryTimer) {
        this._recoveryTimer = setInterval(() => this._recoveryTick(), RECOVERY_INTERVAL_MS)
      }

      // Opportunistic: freshen the snapshot and flush the outbox on entry.
      if (this.online) {
        this.refreshSnapshot().catch(() => {})
        this.drain().catch(() => {})
      }
    },

    dispose() {
      if (this._recoveryTimer) {
        clearInterval(this._recoveryTimer)
        this._recoveryTimer = null
      }
      if (!this._wired) return
      window.removeEventListener('online', this._onOnline)
      window.removeEventListener('offline', this._onOffline)
      this._wired = false
    },

    /**
     * Runs on a timer. `navigator` online/offline events are unreliable (they
     * fire for link state, not real reachability), so while we believe we're
     * offline — or we're online but still carrying a backlog — actively probe
     * the server and recover / flush on our own.
     */
    async _recoveryTick() {
      if (this.online && this.outboxCount === 0 && this.failedCount === 0) return
      const reachable = await pingServer()
      if (reachable) {
        this.online = true
        await this.drain()
      } else {
        this.online = false
      }
    },

    _onOnline() {
      this.online = true
      this.refreshSnapshot().catch(() => {})
      this.drain().catch(() => {})
    },

    _onOffline() {
      this.online = false
    },

    /** Called by POS data calls: a network failure is the real offline signal. */
    reportNetworkFailure() {
      this.online = false
    },

    /** Called after any successful POS network call. */
    reportNetworkSuccess() {
      if (!this.online) {
        this.online = true
        this.drain().catch(() => {})
      }
    },

    async setBranch(branchId) {
      this.activeBranchId = Number(branchId)
      await this.loadMeta()
      if (this.online) this.refreshSnapshot().catch(() => {})
    },

    async loadMeta() {
      this.snapshotMeta = await offlineDb.getSnapshotMeta(this.activeBranchId)
    },

    async refreshOutboxCounters() {
      const all = await offlineDb.outboxAll()
      this.outboxCount = all.filter((s) => s._status !== 'failed').length
      this.failedCount = all.filter((s) => s._status === 'failed').length
    },

    /**
     * Pull a fresh snapshot for the active branch (or a given one) and cache
     * it. Returns true on success. Safe to call speculatively — failures are
     * swallowed into `lastSyncError` and leave the previous snapshot intact.
     */
    async refreshSnapshot(branchId = null) {
      const bid = Number(branchId ?? this.activeBranchId)
      if (!bid || this.refreshing) return false

      this.refreshing = true
      try {
        const payload = await fetchBootstrap(bid)
        await offlineDb.writeSnapshot(bid, payload)
        if (bid === this.activeBranchId) await this.loadMeta()
        this.reportNetworkSuccess()
        this.lastSyncError = null
        return true
      } catch (err) {
        if (!err.response) this.reportNetworkFailure()
        this.lastSyncError = err.response?.data?.message || 'Could not refresh offline data'
        return false
      } finally {
        this.refreshing = false
      }
    },

    /* ── catalogue reads (offline path) ────────────────────────── */

    async getCategories() {
      return offlineDb.readCategories(this.activeBranchId)
    },

    async getBranches() {
      return offlineDb.readBranches()
    },

    /**
     * Mirror of the server's paginated product search, run against the cached
     * snapshot. Same return shape POS.vue expects from ProductService.getAll.
     */
    async getProducts({ search = '', categoryId = null, page = 1, perPage = 20 } = {}) {
      let rows = await offlineDb.readAllProducts(this.activeBranchId)

      const q = search.trim().toLowerCase()
      if (q) {
        rows = rows.filter(
          (p) =>
            p.name?.toLowerCase().includes(q) ||
            p.product_code?.toLowerCase().includes(q) ||
            p.barcode?.toLowerCase().includes(q),
        )
      }
      if (categoryId !== null && categoryId !== undefined) {
        rows = rows.filter((p) => String(p.category_id) === String(categoryId))
      }

      rows.sort((a, b) => (a.name || '').localeCompare(b.name || ''))

      const total = rows.length
      const start = (page - 1) * perPage
      return { data: rows.slice(start, start + perPage), total }
    },

    async searchCustomers(query = '') {
      const all = await offlineDb.readCustomers(this.activeBranchId)
      const q = query.trim().toLowerCase()
      if (!q) return all.slice(0, 25)
      return all
        .filter((c) => c.name?.toLowerCase().includes(q) || (c.phone || '').includes(q))
        .slice(0, 25)
    },

    /* ── outbox (write path) ───────────────────────────────────── */

    /**
     * Queue a completed offline sale and apply its stock delta to the local
     * snapshot so the screen stays believable across several offline sales.
     */
    async enqueueSale(payload) {
      const record = {
        ...payload,
        _status: 'queued',
        _queuedAt: new Date().toISOString(),
        _attempts: 0,
        _lastError: null,
      }
      await offlineDb.outboxAdd(record)
      await offlineDb.decrementLocalStock(this.activeBranchId, payload.items || [])
      await this.loadMeta()
      await this.refreshOutboxCounters()
    },

    /**
     * Push every queued sale to the server, in order. Idempotent on the
     * backend, so a partial run that gets re-invoked is safe. Stops early on
     * the first network failure (still offline) and keeps the rest queued.
     */
    async drain() {
      if (this.draining || !this.online) return
      this.draining = true
      this.lastSyncError = null

      try {
        const queue = (await offlineDb.outboxAll())
          .filter((s) => s._status !== 'failed')
          .sort((a, b) => (a._queuedAt || '').localeCompare(b._queuedAt || ''))

        for (const sale of queue) {
          const { _status, _queuedAt, _attempts, _lastError, ...payload } = sale
          const res = await pushSale(payload)

          if (res.ok) {
            await offlineDb.outboxRemove(sale.client_sale_id)
            continue
          }

          if (!res.retryable) {
            await offlineDb.outboxUpdate(sale.client_sale_id, {
              _status: 'failed',
              _lastError: res.message,
              _attempts: (_attempts || 0) + 1,
            })
            this.lastSyncError = `A sale could not be synced: ${res.message}`
            continue
          }

          // Retryable failure — bump attempt count, leave it queued, stop the
          // run (likely still offline or auth expired).
          await offlineDb.outboxUpdate(sale.client_sale_id, {
            _attempts: (_attempts || 0) + 1,
            _lastError: res.message,
          })
          if (res.message === 'network unreachable') this.reportNetworkFailure()
          this.lastSyncError = res.message
          break
        }
      } finally {
        await this.refreshOutboxCounters()
        this.draining = false
      }
    },

    /** Retry sales previously parked as failed (manual "try again" button). */
    async retryFailed() {
      const all = await offlineDb.outboxAll()
      for (const s of all.filter((x) => x._status === 'failed')) {
        await offlineDb.outboxUpdate(s.client_sale_id, { _status: 'queued', _lastError: null })
      }
      await this.refreshOutboxCounters()
      await this.drain()
    },
  },
})
