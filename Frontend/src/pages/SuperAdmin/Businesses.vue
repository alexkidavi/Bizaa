<template>
  <div class="sa-page">
    <!-- Summary cards -->
    <div class="sa-stats-grid">
      <div class="sa-stat-card">
        <span class="sa-stat-label">Total Businesses</span>
        <span class="sa-stat-value">{{ statsLoading ? "…" : stats.total_businesses }}</span>
      </div>
      <div class="sa-stat-card sa-stat-card--green">
        <span class="sa-stat-label">Active Subscriptions</span>
        <span class="sa-stat-value">{{ statsLoading ? "…" : stats.active_subscriptions }}</span>
      </div>
      <div class="sa-stat-card sa-stat-card--red">
        <span class="sa-stat-label">Expired Subscriptions</span>
        <span class="sa-stat-value">{{ statsLoading ? "…" : stats.expired_subscriptions }}</span>
      </div>
      <div class="sa-stat-card sa-stat-card--blue">
        <span class="sa-stat-label">Products Uploaded</span>
        <span class="sa-stat-value">{{ statsLoading ? "…" : stats.products_uploaded }}</span>
      </div>
      <div class="sa-stat-card sa-stat-card--amber">
        <span class="sa-stat-label">Sales Made</span>
        <span class="sa-stat-value">{{ statsLoading ? "…" : stats.sales_made }}</span>
      </div>
    </div>

    <div class="sa-toolbar">
      <div class="sa-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input v-model="search" type="text" placeholder="Search businesses…" @input="debouncedFetch" />
      </div>
      <span class="sa-count">{{ pagination.total ?? 0 }} businesses</span>
    </div>

    <div class="sa-card">
      <div v-if="loading" class="sa-loading">Loading…</div>
      <div v-else-if="businesses.length === 0" class="sa-empty">No businesses found.</div>
      <div v-else class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Business</th>
              <th>Subscription</th>
              <th>Setup &amp; activity progress</th>
              <th>Joined</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in businesses" :key="b.id">
              <td>
                <div>
                  <p style="font-weight:600;color:#f1f5f9">{{ b.name }}</p>
                  <p style="font-size:11px;color:#64748b">{{ b.email }}</p>
                </div>
              </td>
              <td>
                <span v-if="b.subscription" class="sa-badge"
                  :class="b.subscription.days_left <= 5 ? 'sa-badge--amber' : 'sa-badge--green'">
                  {{ b.subscription.status }} · {{ b.subscription.days_left }}d left
                </span>
                <span v-else class="sa-badge sa-badge--red">No subscription</span>
              </td>
              <td>
                <div class="sa-progress-row">
                  <span class="sa-progress-pill" :class="b.units_count > 0 ? 'is-done' : 'is-pending'" title="Units">
                    Units {{ b.units_count > 0 ? b.units_count : "Pending" }}
                  </span>
                  <span class="sa-progress-pill" :class="b.categories_count > 0 ? 'is-done' : 'is-pending'" title="Categories">
                    Categories {{ b.categories_count > 0 ? b.categories_count : "Pending" }}
                  </span>
                  <span class="sa-progress-pill" :class="b.products_count > 0 ? 'is-done' : 'is-pending'" title="Products">
                    Products {{ b.products_count > 0 ? b.products_count : "Pending" }}
                  </span>
                  <span class="sa-progress-pill" :class="b.sales_count > 0 ? 'is-done' : 'is-pending'" title="Sales">
                    Sales {{ b.sales_count > 0 ? b.sales_count : "None yet" }}
                  </span>
                </div>
              </td>
              <td>{{ b.created_at }}</td>
              <td>
                <button class="sa-btn sa-btn--sm sa-btn--ghost" @click="$router.push(`/superadmin/businesses/${b.id}`)">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="sa-pagination">
        <button :disabled="pagination.current_page <= 1" class="sa-btn sa-btn--sm sa-btn--ghost" @click="fetchPage(pagination.current_page - 1)">← Prev</button>
        <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button :disabled="pagination.current_page >= pagination.last_page" class="sa-btn sa-btn--sm sa-btn--ghost" @click="fetchPage(pagination.current_page + 1)">Next →</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SuperAdminService from "@/services/SuperAdminService";

const businesses = ref([]);
const pagination = ref({});
const loading    = ref(true);
const search     = ref("");
let   debounceTimer = null;

const stats = ref({
  total_businesses: 0,
  active_subscriptions: 0,
  expired_subscriptions: 0,
  products_uploaded: 0,
  sales_made: 0,
});
const statsLoading = ref(true);

const fetchPage = async (page = 1) => {
  loading.value = true;
  try {
    const data = await SuperAdminService.getBusinesses({ page, search: search.value || undefined });
    businesses.value = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page, total: data.total };
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  statsLoading.value = true;
  try {
    stats.value = await SuperAdminService.getBusinessStats();
  } finally {
    statsLoading.value = false;
  }
};

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchPage(1), 350);
};

onMounted(() => {
  fetchPage();
  fetchStats();
});
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 20px; }

.sa-stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; }
.sa-stat-card {
  background: #1e293b; border: 1px solid #334155; border-radius: 12px;
  padding: 16px 18px; display: flex; flex-direction: column; gap: 6px;
  border-left: 3px solid #475569;
}
.sa-stat-card--green  { border-left-color: #22c55e; }
.sa-stat-card--red    { border-left-color: #ef4444; }
.sa-stat-card--blue   { border-left-color: #60a5fa; }
.sa-stat-card--amber  { border-left-color: #fbbf24; }
.sa-stat-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.sa-stat-value { font-size: 26px; color: #f1f5f9; font-weight: 800; }

.sa-toolbar { display: flex; align-items: center; gap: 12px; }
.sa-search {
  display: flex; align-items: center; gap: 8px;
  background: #1e293b; border: 1px solid #334155; border-radius: 8px;
  padding: 8px 12px; flex: 1; max-width: 360px;
}
.sa-search input { background: none; border: none; outline: none; color: #e2e8f0; font-size: 13px; width: 100%; }
.sa-search input::placeholder { color: #475569; }
.sa-count { font-size: 13px; color: #64748b; margin-left: auto; }

.sa-card {
  background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden;
}
.sa-loading, .sa-empty { padding: 48px; text-align: center; color: #64748b; font-size: 13px; }
.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sa-table th { padding: 12px 16px; font-size: 11px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #334155; text-align: left; }
.sa-table td { padding: 12px 16px; color: #cbd5e1; border-bottom: 1px solid #1e293b; }
.sa-table tr:last-child td { border-bottom: none; }
.sa-table tbody tr:hover td { background: #0f172a; }

.sa-progress-row { display: flex; flex-wrap: wrap; gap: 6px; max-width: 320px; }
.sa-progress-pill {
  display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 100px;
  font-size: 11px; font-weight: 600; white-space: nowrap;
}
.sa-progress-pill.is-done    { background: #14532d; color: #4ade80; }
.sa-progress-pill.is-pending { background: #1e293b; color: #64748b; border: 1px solid #334155; }

.sa-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.sa-badge--green  { background: #14532d; color: #4ade80; }
.sa-badge--amber  { background: #451a03; color: #fbbf24; }
.sa-badge--red    { background: #450a0a; color: #f87171; }
.sa-badge--blue   { background: #1e3a5f; color: #60a5fa; }

.sa-btn { padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
.sa-btn--sm { padding: 4px 10px; font-size: 12px; }
.sa-btn--ghost { background: transparent; border: 1px solid #334155; color: #94a3b8; }
.sa-btn--ghost:hover { background: #0f172a; color: #e2e8f0; border-color: #60a5fa; }
.sa-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.sa-pagination { display: flex; align-items: center; gap: 12px; padding: 16px; border-top: 1px solid #334155; font-size: 13px; color: #64748b; }

@media (max-width: 1100px) {
  .sa-stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 640px) {
  .sa-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
