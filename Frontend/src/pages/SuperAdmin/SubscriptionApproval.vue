<template>
  <div class="sa-page">
    <div class="sa-toolbar">
      <div class="sa-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.35-4.35" />
        </svg>
        <input v-model="search" type="text" placeholder="Search business…" @input="debouncedFetch" />
      </div>
      <select v-model="statusFilter" @change="fetchPage(1)" class="sa-filter-select">
        <option value="">All statuses</option>
        <option value="trial">Trial</option>
        <option value="active">Active</option>
        <option value="expired">Expired</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <span class="sa-count">{{ pagination.total ?? 0 }} records</span>
    </div>

    <div class="sa-card">
      <div v-if="loading" class="sa-loading">Loading…</div>
      <div v-else-if="subs.length === 0" class="sa-empty">No subscriptions found.</div>
      <div v-else class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th>Business</th>
              <th>Plan</th>
              <th>Status</th>
              <th>Start</th>
              <th>End</th>
              <!-- <th>Days Left</th> -->
              <th>Amount</th>
              <th>Method</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in subs" :key="s.id">
              <td>
                <span class="sa-link" @click="$router.push(`/superadmin/businesses/${s.business_id}`)">{{
                  s.business_name }}</span>
              </td>
              <td>{{ s.plan }}</td>
              <td><span class="sa-badge" :class="statusClass(s.status)">{{ s.status }}</span></td>
              <td>{{ s.starts_at }}</td>
              <td>{{ s.ends_at }}</td>
              <!-- <td>
                <span v-if="s.status === 'active' || s.status === 'trial'"
                  :class="s.days_left <= 5 ? 'sa-text--danger' : ''">
                  {{ s.days_left }}d
                </span>
                <span v-else>—</span>
              </td> -->
              <td>KES {{ Number(s.amount_paid).toLocaleString() }}</td>
              <td>{{ s.payment_method ?? '—' }}</td>
              <td>
                <div style="display:flex;gap:6px">
                  <button v-if="!['approved', 'rejected'].includes(s.status)" class="sa-btn sa-btn--sm sa-btn--ghost"
                    style="color: green; border: 1px solid green" @click="approve(s.id)">
                    Approve
                  </button>

                  <button v-if="!['approved', 'rejected'].includes(s.status)" class="sa-btn sa-btn--sm sa-btn--danger"
                    @click="reject(s.id)">
                    Reject
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination.last_page > 1" class="sa-pagination">
        <button :disabled="pagination.current_page <= 1" class="sa-btn sa-btn--sm sa-btn--ghost"
          @click="fetchPage(pagination.current_page - 1)">← Prev</button>
        <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button :disabled="pagination.current_page >= pagination.last_page" class="sa-btn sa-btn--sm sa-btn--ghost"
          @click="fetchPage(pagination.current_page + 1)">Next →</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SuperAdminService from "@/services/SuperAdminService";

const subs = ref([]);
const pagination = ref({});
const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
let debounceTimer = null;

const statusClass = (s) => ({
  active: "sa-badge--green",
  trial: "sa-badge--blue",
  expired: "sa-badge--red",
  cancelled: "sa-badge--red",
}[s] ?? "sa-badge--amber");

const fetchPage = async (page = 1) => {
  loading.value = true;
  try {
    const data = await SuperAdminService.getSubscriptionsApprovals({
      page,
      search: search.value || undefined,
      status: statusFilter.value || undefined,
    });
    subs.value = data.data;
    pagination.value = { current_page: data.current_page, last_page: data.last_page, total: data.total };
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchPage(1), 350);
};

const reject = async (id) => {
  if (!confirm("Reject This Renewal?")) return;
  try { await SuperAdminService.renewSubscription(id, "rejected"); fetchPage(); }
  catch (e) { alert(e.response?.data?.message ?? "Error."); }
};

const approve = async (id) => {
  if (!confirm("Approve This Subscription?")) return;
  try { await SuperAdminService.renewSubscription(id, "approved"); fetchPage(); }
  catch (e) { alert(e.response?.data?.message ?? "Error."); }
};

onMounted(() => fetchPage());
</script>

<style scoped>
.sa-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.sa-toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.sa-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 8px 12px;
  flex: 1;
  max-width: 340px;
}

.sa-search input {
  background: none;
  border: none;
  outline: none;
  color: #e2e8f0;
  font-size: 13px;
  width: 100%;
}

.sa-search input::placeholder {
  color: #475569;
}

.sa-filter-select {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #e2e8f0;
  font-size: 13px;
  padding: 8px 12px;
  outline: none;
  cursor: pointer;
}

.sa-count {
  font-size: 13px;
  color: #64748b;
  margin-left: auto;
}

.sa-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  overflow: hidden;
}

.sa-loading,
.sa-empty {
  padding: 48px;
  text-align: center;
  color: #64748b;
  font-size: 13px;
}

.sa-table-wrap {
  overflow-x: auto;
}

.sa-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.sa-table th {
  padding: 12px 16px;
  font-size: 11px;
  color: #475569;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-bottom: 1px solid #334155;
  text-align: left;
}

.sa-table td {
  padding: 12px 16px;
  color: #cbd5e1;
  border-bottom: 1px solid #1e293b;
}

.sa-table tr:last-child td {
  border-bottom: none;
}

.sa-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
}

.sa-badge--green {
  background: #14532d;
  color: #4ade80;
}

.sa-badge--blue {
  background: #1e3a5f;
  color: #60a5fa;
}

.sa-badge--amber {
  background: #451a03;
  color: #fbbf24;
}

.sa-badge--red {
  background: #450a0a;
  color: #f87171;
}

.sa-text--danger {
  color: #f87171;
  font-weight: 600;
}

.sa-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 7px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.sa-btn--sm {
  padding: 4px 10px;
  font-size: 12px;
}

.sa-btn--ghost {
  background: transparent;
  border: 1px solid #334155;
  color: #94a3b8;
}

.sa-btn--ghost:hover {
  background: #0f172a;
  color: #e2e8f0;
}

.sa-btn--danger {
  background: #450a0a;
  color: #f87171;
  border: 1px solid #ef4444;
}

.sa-btn--danger:hover {
  background: #ef4444;
  color: #fff;
}

.sa-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.sa-pagination {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border-top: 1px solid #334155;
  font-size: 13px;
  color: #64748b;
}

.sa-link {
  color: #60a5fa;
  cursor: pointer;
}

.sa-link:hover {
  text-decoration: underline;
}
</style>
