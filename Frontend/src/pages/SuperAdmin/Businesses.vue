<template>
  <div class="sa-page">
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
              <th>Industry</th>
              <th>Users</th>
              <th>Subscription</th>
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
              <td>{{ b.industry ?? '—' }}</td>
              <td>{{ b.user_count }}</td>
              <td>
                <span v-if="b.subscription" class="sa-badge"
                  :class="b.subscription.days_left <= 5 ? 'sa-badge--amber' : 'sa-badge--green'">
                  {{ b.subscription.status }} · {{ b.subscription.days_left }}d left
                </span>
                <span v-else class="sa-badge sa-badge--red">No subscription</span>
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

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchPage(1), 350);
};

onMounted(() => fetchPage());
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 20px; }
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
</style>
