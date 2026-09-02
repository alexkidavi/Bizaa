<template>
  <div class="offline-bar" :class="{ 'is-offline': !store.isOnline }">
    <!-- connection + snapshot state -->
    <div class="ob-group">
      <span class="ob-dot" :class="store.isOnline ? 'ok' : 'warn'" />
      <span class="ob-label">{{ store.isOnline ? 'Online' : 'Offline' }}</span>

      <span class="ob-sep">·</span>

      <template v-if="store.hasSnapshot">
        <span class="ob-muted">
          Offline data {{ store.isSnapshotStale ? 'is stale' : 'ready' }} ·
          synced {{ syncedAgo }}
        </span>
        <span v-if="store.snapshotMeta?.productsTruncated" class="ob-warn-text">
          (catalogue truncated)
        </span>
      </template>
      <span v-else class="ob-warn-text">
        Not synced yet — connect once to enable offline selling
      </span>
    </div>

    <!-- actions -->
    <div class="ob-group ob-actions">
      <span v-if="store.draining" class="ob-muted">Syncing sales…</span>

      <span v-else-if="store.outboxCount > 0" class="ob-pill amber">
        {{ store.outboxCount }} sale{{ store.outboxCount === 1 ? '' : 's' }} waiting
      </span>

      <button
        v-if="store.failedCount > 0"
        class="ob-btn danger"
        @click="store.retryFailed()"
      >
        {{ store.failedCount }} failed — Retry
      </button>

      <button
        v-if="store.isOnline"
        class="ob-btn"
        :disabled="store.refreshing"
        @click="store.refreshSnapshot()"
      >
        {{ store.refreshing ? 'Syncing…' : 'Sync now' }}
      </button>

      <button v-if="canInstall" class="ob-btn primary" @click="promptInstall">
        Install POS App
      </button>
      <span v-else-if="iosHint" class="ob-muted ob-ios">
        Install: Share → Add to Home Screen
      </span>
      <span v-else-if="menuHint" class="ob-muted ob-ios">
        Install: browser menu (⋮) → Install / Apps
      </span>

      <button v-if="needsRefresh" class="ob-btn primary" @click="applyUpdate">
        New version — Reload
      </button>
    </div>

    <p v-if="store.lastSyncError" class="ob-error">{{ store.lastSyncError }}</p>
  </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import { usePosOffline } from '@/stores/posOffline'
import { usePosInstall } from '@/composables/usePosInstall'
import { needsRefresh, applyUpdate } from '@/pwa.js'

const store = usePosOffline()
const { canInstall, iosHint, menuHint, promptInstall } = usePosInstall()

const now = ref(Date.now())
let tick = null
onMounted(() => {
  tick = setInterval(() => (now.value = Date.now()), 30_000)
})
onBeforeUnmount(() => clearInterval(tick))

const syncedAgo = computed(() => {
  if (!store.snapshotMeta?.syncedAt) return ''
  const diff = now.value - new Date(store.snapshotMeta.syncedAt).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'just now'
  if (mins < 60) return `${mins}m ago`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h ago`
  return `${Math.floor(hrs / 24)}d ago`
})
</script>

<style scoped>
.offline-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 5px 12px;
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
  font-size: 11px;
  color: #475569;
  flex-shrink: 0;
}
.offline-bar.is-offline {
  background: #fffbeb;
  border-bottom-color: #fde68a;
}
.ob-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.ob-actions {
  justify-content: flex-end;
}
.ob-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.ob-dot.ok {
  background: #22c55e;
}
.ob-dot.warn {
  background: #f59e0b;
}
.ob-label {
  font-weight: 700;
}
.ob-sep {
  color: #cbd5e1;
}
.ob-muted {
  color: #94a3b8;
}
.ob-warn-text {
  color: #b45309;
  font-weight: 600;
}
.ob-pill {
  padding: 2px 8px;
  border-radius: 999px;
  font-weight: 700;
}
.ob-pill.amber {
  background: #fef3c7;
  color: #92400e;
}
.ob-btn {
  padding: 3px 10px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: #fff;
  font-weight: 700;
  font-size: 11px;
  color: #334155;
  cursor: pointer;
}
.ob-btn:hover {
  background: #f8fafc;
}
.ob-btn:disabled {
  opacity: 0.6;
  cursor: default;
}
.ob-btn.primary {
  background: #229de7;
  border-color: #229de7;
  color: #fff;
}
.ob-btn.danger {
  background: #fef2f2;
  border-color: #fecaca;
  color: #b91c1c;
}
.ob-error {
  flex-basis: 100%;
  color: #b91c1c;
  font-weight: 600;
}
@media (max-width: 640px) {
  .ob-ios {
    display: none;
  }
}
</style>
