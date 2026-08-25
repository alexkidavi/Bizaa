<template>
  <div class="sa-page">
    <div class="sa-toolbar">
      <div class="sa-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input v-model="search" type="text" placeholder="Search stores…" @input="debouncedFetch" />
      </div>
      <select v-model="statusFilter" @change="fetchPage(1)" class="sa-filter-select">
        <option value="">All stores</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <span class="sa-count">{{ pagination.total ?? 0 }} stores</span>
    </div>

    <div class="sa-card">
      <div v-if="loading" class="sa-loading">Loading…</div>
      <div v-else-if="stores.length === 0" class="sa-empty">No stores found.</div>
      <div v-else class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Store</th>
              <th>Business</th>
              <th>Category</th>
              <th>Listed Products</th>
              <th>Status</th>
              <th>Featured</th>
              <th>Created</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in stores" :key="s.id">
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div class="sa-store-logo" :style="{ background: s.banner_color || '#1e3a5f' }">{{ s.name?.charAt(0) }}</div>
                  <div>
                    <p style="font-weight:600;color:#f1f5f9">{{ s.name }}</p>
                    <p style="font-size:11px;color:#64748b">{{ s.slug }}</p>
                  </div>
                </div>
              </td>
              <td>{{ s.business_name }}</td>
              <td>{{ s.category }}</td>
              <td>{{ s.listed_products }}</td>
              <td>
                <span class="sa-badge" :class="s.is_active ? 'sa-badge--green' : 'sa-badge--red'">
                  {{ s.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <span class="sa-badge" :class="s.is_featured ? 'sa-badge--amber' : 'sa-badge--gray'">
                  {{ s.is_featured ? 'Featured' : 'Normal' }}
                </span>
              </td>
              <td>{{ s.created_at }}</td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="sa-btn sa-btn--sm sa-btn--ghost" @click="toggleFeatured(s)" :title="s.is_featured ? 'Unfeature' : 'Feature'">
                    <svg width="12" height="12" viewBox="0 0 24 24" :fill="s.is_featured ? '#fbbf24' : 'none'" stroke="#fbbf24" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                  </button>
                  <button class="sa-btn sa-btn--sm" :class="s.is_active ? 'sa-btn--danger' : 'sa-btn--success'" @click="toggleActive(s)">
                    {{ s.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

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

const stores       = ref([]);
const pagination   = ref({});
const loading      = ref(true);
const search       = ref("");
const statusFilter = ref("");
let   debounceTimer = null;

const fetchPage = async (page = 1) => {
  loading.value = true;
  try {
    const data = await SuperAdminService.getStores({
      page,
      search: search.value || undefined,
      status: statusFilter.value || undefined,
    });
    stores.value     = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page, total: data.total };
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchPage(1), 350);
};

const toggleFeatured = async (store) => {
  try {
    const r = await SuperAdminService.toggleFeatured(store.id);
    store.is_featured = r.is_featured;
  } catch { alert("Error updating store."); }
};

const toggleActive = async (store) => {
  try {
    const r = await SuperAdminService.toggleActive(store.id);
    store.is_active = r.is_active;
  } catch { alert("Error updating store."); }
};

onMounted(() => fetchPage());
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 20px; }
.sa-toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sa-search {
  display: flex; align-items: center; gap: 8px;
  background: #1e293b; border: 1px solid #334155; border-radius: 8px;
  padding: 8px 12px; flex: 1; max-width: 340px;
}
.sa-search input { background: none; border: none; outline: none; color: #e2e8f0; font-size: 13px; width: 100%; }
.sa-search input::placeholder { color: #475569; }
.sa-filter-select { background: #1e293b; border: 1px solid #334155; border-radius: 8px; color: #e2e8f0; font-size: 13px; padding: 8px 12px; outline: none; cursor: pointer; }
.sa-count { font-size: 13px; color: #64748b; margin-left: auto; }

.sa-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.sa-loading, .sa-empty { padding: 48px; text-align: center; color: #64748b; font-size: 13px; }
.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sa-table th { padding: 12px 16px; font-size: 11px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #334155; text-align: left; }
.sa-table td { padding: 12px 16px; color: #cbd5e1; border-bottom: 1px solid #1e293b; vertical-align: middle; }
.sa-table tr:last-child td { border-bottom: none; }

.sa-store-logo { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 800; flex-shrink: 0; }

.sa-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
.sa-badge--green { background: #14532d; color: #4ade80; }
.sa-badge--red   { background: #450a0a; color: #f87171; }
.sa-badge--amber { background: #451a03; color: #fbbf24; }
.sa-badge--gray  { background: #1e293b; color: #64748b; border: 1px solid #334155; }

.sa-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
.sa-btn--sm      { padding: 4px 10px; font-size: 12px; }
.sa-btn--ghost   { background: transparent; border: 1px solid #334155; color: #94a3b8; }
.sa-btn--ghost:hover { background: #0f172a; color: #e2e8f0; }
.sa-btn--success { background: #14532d; color: #4ade80; border: 1px solid #22c55e; }
.sa-btn--success:hover { background: #22c55e; color: #fff; }
.sa-btn--danger  { background: #450a0a; color: #f87171; border: 1px solid #ef4444; }
.sa-btn--danger:hover  { background: #ef4444; color: #fff; }
.sa-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.sa-pagination { display: flex; align-items: center; gap: 12px; padding: 16px; border-top: 1px solid #334155; font-size: 13px; color: #64748b; }
</style>
