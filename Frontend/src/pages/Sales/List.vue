<template>
  <div class="sales-root">
    <!-- ── Page Header ── -->
    <div class="page-header">
      <div class="header-left">
        <div class="page-icon">
          <svg
            class="w-5 h-5 text-white"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
        </div>
        <div>
          <h1 class="page-title">Sales</h1>
          <p class="page-sub">{{ totalSales }} transactions</p>
        </div>
      </div>
      <div class="header-right">
        <router-link to="/sales/pos" class="btn-primary">
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          New Sale
        </router-link>
      </div>
    </div>

    <!-- ── Stats Row ── -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-label">Today's Sales</div>
        <div class="stat-value">KES {{ stats.today.toLocaleString() }}</div>
        <div class="stat-sub">{{ stats.todayCount }} transactions</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">This Week</div>
        <div class="stat-value">KES {{ stats.week.toLocaleString() }}</div>
        <div class="stat-sub">{{ stats.weekCount }} transactions</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">This Month</div>
        <div class="stat-value">KES {{ stats.month.toLocaleString() }}</div>
        <div class="stat-sub">{{ stats.monthCount }} transactions</div>
      </div>
      <div class="stat-card accent">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value text-red-500">
          KES {{ stats.outstanding.toLocaleString() }}
        </div>
        <div class="stat-sub">{{ stats.outstandingCount }} unpaid</div>
      </div>
    </div>

    <!-- ── Filters ── -->
    <div class="filters-bar">
      <!-- Search -->
      <div class="search-wrap">
        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
        <input
          v-model="filters.search"
          @input="onFilterChange"
          type="text"
          placeholder="Search ref, customer, amount…"
          class="search-input"
        />
        <button
          v-if="filters.search"
          @click="
            filters.search = '';
            onFilterChange();
          "
          class="search-clear"
        >
          ×
        </button>
      </div>

      <!-- Date range -->
      <div class="filter-group">
        <input
          v-model="filters.dateFrom"
          @change="onFilterChange"
          type="date"
          class="filter-input"
          title="From date"
        />
        <span class="text-slate-400 text-xs">to</span>
        <input
          v-model="filters.dateTo"
          @change="onFilterChange"
          type="date"
          class="filter-input"
          title="To date"
        />
      </div>

      <!-- Payment method -->
      <select
        v-model="filters.paymentMethod"
        @change="onFilterChange"
        class="filter-select"
      >
        <option value="">All Methods</option>
        <option value="cash">Cash</option>
        <option value="mpesa">M-Pesa</option>
        <option value="card">Card</option>
        <option value="credit">Credit</option>
        <option value="split">Split</option>
      </select>

      <!-- Status -->
      <select v-model="filters.status" @change="onFilterChange" class="filter-select">
        <option value="">All Status</option>
        <option value="paid">Paid</option>
        <option value="partial">Partial</option>
        <option value="unpaid">Unpaid</option>
        <option value="refunded">Refunded</option>
      </select>

      <!-- Clear filters -->
      <button v-if="hasActiveFilters" @click="clearFilters" class="clear-filters-btn">
        <svg
          class="w-3.5 h-3.5"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Clear
      </button>
    </div>

    <!-- ── Table ── -->
    <div class="table-card">
      <!-- Loading -->
      <div v-if="loading" class="table-loading">
        <div v-for="n in 8" :key="n" class="skeleton-row">
          <div class="skeleton w-24"></div>
          <div class="skeleton w-32"></div>
          <div class="skeleton w-20"></div>
          <div class="skeleton w-20"></div>
          <div class="skeleton w-16"></div>
          <div class="skeleton w-16"></div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="sales.length === 0" class="table-empty">
        <svg
          class="w-12 h-12 opacity-20 mb-3"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <p class="font-bold text-slate-400">No sales found</p>
        <p class="text-sm text-slate-300 mt-1">Try adjusting your filters</p>
      </div>

      <!-- Table -->
      <table v-else class="sales-table">
        <thead>
          <tr>
            <th
              @click="sortBy('reference')"
              class="th sortable"
              :class="{ sorted: sort.field === 'reference' }"
            >
              Reference
              <svg
                class="sort-icon"
                :class="{ desc: sort.dir === 'desc' && sort.field === 'reference' }"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
              </svg>
            </th>
            <th
              @click="sortBy('created_at')"
              class="th sortable"
              :class="{ sorted: sort.field === 'created_at' }"
            >
              Date
              <svg
                class="sort-icon"
                :class="{ desc: sort.dir === 'desc' && sort.field === 'created_at' }"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
              </svg>
            </th>
            <th class="th">Customer</th>
            <th class="th">Branch</th>
            <th class="th">Items</th>
            <th
              @click="sortBy('total')"
              class="th sortable text-right"
              :class="{ sorted: sort.field === 'total' }"
            >
              Total
              <svg
                class="sort-icon"
                :class="{ desc: sort.dir === 'desc' && sort.field === 'total' }"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
              </svg>
            </th>
            <th class="th">Method</th>
            <th class="th">Status</th>
            <th class="th"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="sale in sales"
            :key="sale.sale_id"
            class="tr"
            @click="goToSale(sale.sale_id)"
          >
            <td class="td">
              <span class="ref-text">{{ sale.reference }}</span>
            </td>
            <td class="td">
              <div class="date-cell">
                <span class="date-main">{{ formatDate(sale.created_at) }}</span>
                <span class="date-time">{{ formatTime(sale.created_at) }}</span>
              </div>
            </td>
            <td class="td">
              <div class="customer-cell">
                <div class="customer-avatar">
                  {{ (sale.customer?.name || "W").charAt(0).toUpperCase() }}
                </div>
                <span class="customer-name">{{ sale.customer?.name || "Walk-in" }}</span>
              </div>
            </td>
            <td class="td">
              <div class="branch-cell">
                <span class="branch-name">{{ sale.branch?.name || "Walk-in" }}</span>
              </div>
            </td>
            <td class="td text-center">
              <span class="items-badge">{{
                sale.items_count ?? sale.items?.length ?? "—"
              }}</span>
            </td>
            <td class="td text-right">
              <div class="amount-cell">
                <span class="amount-total"
                  >KES {{ Number(sale.total).toLocaleString() }}</span
                >
                <span v-if="sale.balance > 0" class="amount-balance"
                  >-{{ Number(sale.balance).toLocaleString() }} due</span
                >
              </div>
            </td>
            <td class="td">
              <span class="method-pill" :class="sale.payment_method">
                {{ methodLabel(sale.payment_method) }}
              </span>
            </td>
            <td class="td">
              <span class="status-pill" :class="sale.status">
                {{ sale.status }}
              </span>
            </td>
            <td class="td" @click.stop>
              <div class="row-actions">
                <button @click="goToSale(sale.sale_id)" class="row-btn" title="View">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                </button>
                <button @click="printSale(sale)" class="row-btn" title="Print">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="!loading && totalPages > 1" class="pagination">
        <button
          :disabled="page === 1"
          @click="goToPage(1)"
          class="page-btn"
          title="First"
        >
          «
        </button>
        <button :disabled="page === 1" @click="goToPage(page - 1)" class="page-btn">
          ‹
        </button>

        <button
          v-for="p in pageNumbers"
          :key="p"
          @click="typeof p === 'number' && goToPage(p)"
          class="page-btn"
          :class="{ active: p === page, ellipsis: p === '…' }"
          :disabled="p === '…'"
        >
          {{ p }}
        </button>

        <button
          :disabled="page >= totalPages"
          @click="goToPage(page + 1)"
          class="page-btn"
        >
          ›
        </button>
        <button
          :disabled="page >= totalPages"
          @click="goToPage(totalPages)"
          class="page-btn"
          title="Last"
        >
          »
        </button>

        <span class="page-info"
          >{{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, totalSales) }} of
          {{ totalSales }}</span
        >
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import SalesService from "@/services/SaleService";

const router = useRouter();

/* ── State ──────────────────────────── */
const sales = ref([]);
const loading = ref(false);
const totalSales = ref(0);
const page = ref(1);
const perPage = 10;

const stats = ref({
  today: 0,
  todayCount: 0,
  week: 0,
  weekCount: 0,
  month: 0,
  monthCount: 0,
  outstanding: 0,
  outstandingCount: 0,
});

const filters = ref({
  search: "",
  dateFrom: "",
  dateTo: "",
  paymentMethod: "",
  status: "",
});

const sort = ref({ field: "created_at", dir: "desc" });

let filterTimer = null;

/* ── Computed ────────────────────────── */
const totalPages = computed(() => Math.max(1, Math.ceil(totalSales.value / perPage)));

const hasActiveFilters = computed(
  () =>
    filters.value.search ||
    filters.value.dateFrom ||
    filters.value.dateTo ||
    filters.value.paymentMethod ||
    filters.value.status
);

const pageNumbers = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const cur = page.value;
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (cur > 3) pages.push("…");
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++)
      pages.push(i);
    if (cur < total - 2) pages.push("…");
    pages.push(total);
  }
  return pages;
});

/* ── Fetch ───────────────────────────── */
const fetchSales = async () => {
  loading.value = true;
  try {
    const params = {
      page: page.value,
      per_page: perPage,
      sort_by: sort.value.field,
      sort_dir: sort.value.dir,
      ...(filters.value.search && { search: filters.value.search }),
      ...(filters.value.dateFrom && { date_from: filters.value.dateFrom }),
      ...(filters.value.dateTo && { date_to: filters.value.dateTo }),
      ...(filters.value.paymentMethod && { payment_method: filters.value.paymentMethod }),
      ...(filters.value.status && { status: filters.value.status }),
    };

    const res = await SalesService.getAll(params);

    const payload = res.data?.original ?? res.data;

    sales.value = payload?.data ?? [];
    totalSales.value = payload?.total ?? 0;

    if (payload?.stats) {
      stats.value = payload.stats;
    }
  } catch (e) {
    console.error(e);
    sales.value = [];
  } finally {
    loading.value = false;
  }
};

const onFilterChange = () => {
  clearTimeout(filterTimer);
  page.value = 1;
  filterTimer = setTimeout(fetchSales, 350);
};

const clearFilters = () => {
  filters.value = { search: "", dateFrom: "", dateTo: "", paymentMethod: "", status: "" };
  page.value = 1;
  fetchSales();
};

const sortBy = (field) => {
  if (sort.value.field === field) {
    sort.value.dir = sort.value.dir === "asc" ? "desc" : "asc";
  } else {
    sort.value.field = field;
    sort.value.dir = "desc";
  }
  page.value = 1;
  fetchSales();
};

const goToPage = (p) => {
  page.value = p;
  fetchSales();
};

/* ── Navigation ──────────────────────── */
const goToSale = (sale_id) => router.push(`/sales/${sale_id}`);
const printSale = (sale) => router.push(`/sales/${sale.sale_id}?print=1`);

/* ── Helpers ─────────────────────────── */
const formatDate = (dt) =>
  dt
    ? new Date(dt).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : "—";
const formatTime = (dt) =>
  dt
    ? new Date(dt).toLocaleTimeString("en-KE", { hour: "2-digit", minute: "2-digit" })
    : "";

const methodLabel = (m) =>
  ({ cash: "Cash", mpesa: "M-Pesa", card: "Card", credit: "Credit", split: "Split" }[m] ??
  m ??
  "—");

onMounted(fetchSales);
</script>

<style scoped>
.sales-root {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 24px;
  min-height: 100vh;
  background: #f0f6fb;
  font-family: "Segoe UI", system-ui, sans-serif;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.page-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #229de7, #1682c4);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.page-title {
  font-size: 22px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.1;
}
.page-sub {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
  margin-top: 1px;
}
.header-right {
  display: flex;
  gap: 10px;
}
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: 10px;
  background: linear-gradient(135deg, #229de7, #1682c4);
  color: white;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.15s;
  box-shadow: 0 4px 12px rgba(34, 157, 231, 0.3);
}
.btn-primary:hover {
  background: linear-gradient(135deg, #1682c4, #0f6fab);
  transform: translateY(-1px);
}

/* Stats */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}
.stat-card {
  background: white;
  border-radius: 14px;
  padding: 16px 18px;
  border: 1.5px solid #e8f0f8;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}
.stat-label {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}
.stat-value {
  font-size: 20px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.1;
}
.stat-sub {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
}

/* Filters */
.filters-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  background: white;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1.5px solid #e8f0f8;
}
.search-wrap {
  position: relative;
  flex: 1;
  min-width: 200px;
}
.search-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #94a3b8;
  pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 8px 32px 8px 34px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 9px;
  font-size: 13px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
}
.search-input:focus {
  border-color: #229de7;
  background: white;
}
.search-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
.search-clear:hover {
  color: #475569;
}
.filter-group {
  display: flex;
  align-items: center;
  gap: 6px;
}
.filter-input {
  padding: 8px 10px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 9px;
  font-size: 12px;
  color: #1e293b;
  outline: none;
  cursor: pointer;
}
.filter-input:focus {
  border-color: #229de7;
}
.filter-select {
  padding: 8px 10px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 9px;
  font-size: 12px;
  color: #1e293b;
  outline: none;
  cursor: pointer;
}
.filter-select:focus {
  border-color: #229de7;
}
.clear-filters-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 7px 12px;
  border-radius: 8px;
  background: #fef2f2;
  border: 1.5px solid #fecaca;
  font-size: 12px;
  font-weight: 700;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.15s;
}
.clear-filters-btn:hover {
  background: #ef4444;
  color: white;
}

/* Table card */
.table-card {
  background: white;
  border-radius: 16px;
  border: 1.5px solid #e8f0f8;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

/* Loading skeletons */
.table-loading {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.skeleton-row {
  display: flex;
  gap: 16px;
  padding: 12px 16px;
  align-items: center;
}
.skeleton {
  height: 14px;
  border-radius: 7px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Empty */
.table-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #94a3b8;
}

/* Table */
.sales-table {
  width: 100%;
  border-collapse: collapse;
}
.th {
  padding: 11px 16px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #fafcff;
  border-bottom: 1.5px solid #eef5fb;
  white-space: nowrap;
}
.th.sortable {
  cursor: pointer;
  user-select: none;
}
.th.sortable:hover {
  color: #229de7;
}
.th.sorted {
  color: #229de7;
}
.sort-icon {
  display: inline-block;
  width: 12px;
  height: 12px;
  margin-left: 4px;
  vertical-align: middle;
  transition: transform 0.15s;
}
.sort-icon.desc {
  transform: rotate(180deg);
}

.tr {
  cursor: pointer;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.1s;
}
.tr:last-child {
  border-bottom: none;
}
.tr:hover {
  background: #f8fbff;
}
.td {
  padding: 12px 16px;
  font-size: 13px;
  color: #334155;
  vertical-align: middle;
}

.ref-text {
  font-family: monospace;
  font-weight: 700;
  color: #229de7;
  font-size: 12px;
}

.date-cell {
  display: flex;
  flex-direction: column;
}
.date-main {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
}
.date-time {
  font-size: 10px;
  color: #94a3b8;
}

.customer-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}
.customer-avatar {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  color: #229de7;
  font-weight: 900;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.customer-name {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.items-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  padding: 0 6px;
  background: #f1f5f9;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
}

.amount-cell {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.amount-total {
  font-size: 13px;
  font-weight: 800;
  color: #0f172a;
}
.amount-balance {
  font-size: 10px;
  font-weight: 700;
  color: #ef4444;
}

/* Method pills */
.method-pill {
  display: inline-flex;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
  background: #f1f5f9;
  color: #64748b;
}
.method-pill.cash {
  background: #ecfdf5;
  color: #059669;
}
.method-pill.mpesa {
  background: #dcfce7;
  color: #16a34a;
}
.method-pill.card {
  background: #eff6ff;
  color: #2563eb;
}
.method-pill.credit {
  background: #fef9ec;
  color: #d97706;
}
.method-pill.split {
  background: #f5f3ff;
  color: #7c3aed;
}

/* Status pills */
.status-pill {
  display: inline-flex;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-pill.paid {
  background: #ecfdf5;
  color: #059669;
}
.status-pill.partial {
  background: #fef9ec;
  color: #d97706;
}
.status-pill.unpaid {
  background: #fef2f2;
  color: #dc2626;
}
.status-pill.refunded {
  background: #f5f3ff;
  color: #7c3aed;
}

/* Row actions */
.row-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}
.row-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.row-btn:hover {
  background: #229de7;
  border-color: #229de7;
  color: white;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 14px 16px;
  border-top: 1px solid #eef5fb;
  flex-wrap: wrap;
}
.page-btn {
  min-width: 32px;
  height: 32px;
  border-radius: 8px;
  padding: 0 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.page-btn:hover:not(:disabled):not(.ellipsis) {
  background: #eaf5fd;
  border-color: #b3defa;
  color: #229de7;
}
.page-btn.active {
  background: #229de7;
  border-color: #229de7;
  color: white;
}
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.page-btn.ellipsis {
  background: none;
  border-color: transparent;
  cursor: default;
}
.page-info {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  margin-left: 8px;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 768px) {
  .sales-root {
    padding: 16px;
    gap: 14px;
  }
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  .stat-value {
    font-size: 16px;
  }
  .filters-bar {
    gap: 8px;
  }
  .filter-group {
    flex-wrap: wrap;
  }
  .sales-table {
    display: block;
    overflow-x: auto;
  }
  /* Hide less important columns on mobile */
  .sales-table th:nth-child(4),
  .sales-table td:nth-child(4),
  .sales-table th:nth-child(7),
  .sales-table td:nth-child(7) {
    display: none;
  }
}
@media (max-width: 480px) {
  .stats-row {
    grid-template-columns: 1fr 1fr;
  }
  .sales-table th:nth-child(3),
  .sales-table td:nth-child(3) {
    display: none;
  }
}
</style>
