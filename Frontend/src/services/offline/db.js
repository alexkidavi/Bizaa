/**
 * IndexedDB layer for the offline POS.
 *
 * One database, four stores:
 *   - kv         snapshot metadata, keyed "snapshot:<branchId>"
 *   - products   catalogue snapshot, one row per product, indexed by branch
 *   - categories category snapshot, indexed by branch
 *   - customers  recent-customers snapshot, indexed by branch
 *   - outbox     sales rung up offline, awaiting sync, keyed by client_sale_id
 *
 * Everything here is best-effort: a private-mode browser, denied storage, or
 * a wiped database must degrade to "online only", never throw into the POS.
 */

import { openDB } from 'idb'

const DB_NAME = 'bizaa-pos'
const DB_VERSION = 1

let dbPromise = null

function db() {
  if (dbPromise) return dbPromise

  dbPromise = openDB(DB_NAME, DB_VERSION, {
    upgrade(database) {
      database.createObjectStore('kv', { keyPath: 'key' })

      const products = database.createObjectStore('products', { keyPath: 'uid' })
      products.createIndex('by-branch', 'branch_id')

      const categories = database.createObjectStore('categories', { keyPath: 'uid' })
      categories.createIndex('by-branch', 'branch_id')

      const customers = database.createObjectStore('customers', { keyPath: 'uid' })
      customers.createIndex('by-branch', 'branch_id')

      database.createObjectStore('outbox', { keyPath: 'client_sale_id' })
    },
  }).catch((err) => {
    console.warn('[offline-db] open failed — offline POS disabled:', err)
    return null
  })

  return dbPromise
}

/** Ask the browser to keep our data around (best effort, resolves either way). */
export async function requestPersistence() {
  try {
    if (navigator.storage?.persist && !(await navigator.storage.persisted())) {
      await navigator.storage.persist()
    }
  } catch {
    /* not supported — fine */
  }
}

/* ── snapshot metadata ─────────────────────────────────────────── */

export async function getSnapshotMeta(branchId) {
  const d = await db()
  if (!d) return null
  return (await d.get('kv', `snapshot:${branchId}`))?.value ?? null
}

async function setSnapshotMeta(branchId, value) {
  const d = await db()
  if (!d) return
  await d.put('kv', { key: `snapshot:${branchId}`, value })
}

/** Branch switcher list — business-wide, cached so an offline reload keeps it. */
export async function writeBranches(branches) {
  const d = await db()
  if (!d || !Array.isArray(branches)) return
  await d.put('kv', { key: 'pos:branches', value: branches })
}

export async function readBranches() {
  const d = await db()
  if (!d) return []
  return (await d.get('kv', 'pos:branches'))?.value ?? []
}

/* ── writing a fresh snapshot ──────────────────────────────────── */

/**
 * Replace the cached snapshot for one branch with a fresh bootstrap payload.
 * Wipes that branch's rows first so deleted products/customers don't linger.
 */
export async function writeSnapshot(branchId, payload) {
  const d = await db()
  if (!d) return false

  const bid = Number(branchId)

  const wipe = async (storeName) => {
    const tx = d.transaction(storeName, 'readwrite')
    let cursor = await tx.store.index('by-branch').openCursor(IDBKeyRange.only(bid))
    while (cursor) {
      await cursor.delete()
      cursor = await cursor.continue()
    }
    await tx.done
  }

  await Promise.all([wipe('products'), wipe('categories'), wipe('customers')])

  const putAll = async (storeName, rows, keyField = 'id') => {
    const tx = d.transaction(storeName, 'readwrite')
    for (const row of rows ?? []) {
      await tx.store.put({ ...row, branch_id: bid, uid: `${bid}:${row[keyField]}` })
    }
    await tx.done
  }

  await Promise.all([
    putAll('products', payload.products),
    putAll('categories', payload.categories),
    putAll('customers', payload.customers),
  ])

  await writeBranches(payload.branches)

  await setSnapshotMeta(bid, {
    syncedAt: payload.synced_at ?? new Date().toISOString(),
    business: payload.business ?? null,
    branch: payload.branch ?? null,
    taxRate: payload.tax_rate ?? 16,
    loyalty: payload.loyalty ?? null,
    productsTruncated: !!payload.products_truncated,
    counts: {
      products: payload.products?.length ?? 0,
      categories: payload.categories?.length ?? 0,
      customers: payload.customers?.length ?? 0,
    },
  })

  return true
}

/* ── reads (offline catalogue) ─────────────────────────────────── */

export async function readCategories(branchId) {
  const d = await db()
  if (!d) return []
  const rows = await d.getAllFromIndex('categories', 'by-branch', Number(branchId))
  return rows.map(({ uid, branch_id, ...rest }) => rest)
}

export async function readAllProducts(branchId) {
  const d = await db()
  if (!d) return []
  const rows = await d.getAllFromIndex('products', 'by-branch', Number(branchId))
  return rows.map(({ uid, branch_id, ...rest }) => rest)
}

export async function readCustomers(branchId) {
  const d = await db()
  if (!d) return []
  const rows = await d.getAllFromIndex('customers', 'by-branch', Number(branchId))
  return rows.map(({ uid, branch_id, ...rest }) => rest)
}

/**
 * Apply the same delta the POS applies optimistically after an offline sale,
 * so the on-screen stock keeps shrinking across several offline sales.
 * `items` is [{ product_id (encrypted), quantity }].
 */
export async function decrementLocalStock(branchId, items) {
  const d = await db()
  if (!d) return
  const bid = Number(branchId)
  const tx = d.transaction('products', 'readwrite')
  for (const item of items) {
    const uid = await findProductUid(tx.store, bid, item.product_id)
    if (!uid) continue
    const row = await tx.store.get(uid)
    if (!row) continue
    row.current_stock = Number(row.current_stock ?? 0) - Number(item.quantity ?? 0)
    await tx.store.put(row)
  }
  await tx.done
}

async function findProductUid(store, branchId, encryptedId) {
  // Products are keyed by `${branch}:${rawId}` but the cart carries the
  // encrypted id, so match on the stored `id` field instead.
  let cursor = await store.index('by-branch').openCursor(IDBKeyRange.only(branchId))
  while (cursor) {
    if (cursor.value.id === encryptedId) return cursor.value.uid
    cursor = await cursor.continue()
  }
  return null
}

/* ── outbox (offline sales awaiting sync) ──────────────────────── */

export async function outboxAdd(sale) {
  const d = await db()
  if (!d) throw new Error('offline storage unavailable')
  await d.put('outbox', sale)
}

export async function outboxAll() {
  const d = await db()
  if (!d) return []
  return d.getAll('outbox')
}

export async function outboxRemove(clientSaleId) {
  const d = await db()
  if (!d) return
  await d.delete('outbox', clientSaleId)
}

export async function outboxUpdate(clientSaleId, patch) {
  const d = await db()
  if (!d) return
  const row = await d.get('outbox', clientSaleId)
  if (row) await d.put('outbox', { ...row, ...patch })
}

export async function outboxCount() {
  const d = await db()
  if (!d) return 0
  return d.count('outbox')
}
