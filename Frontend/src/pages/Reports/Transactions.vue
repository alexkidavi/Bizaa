<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Transactions
        </h1>
        <p class="text-slate-500 mt-1 text-sm">Sale and purchase payment history.</p>
      </div>
    </div>

    <!-- TAB NAV -->
    <div
      class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm mb-6 overflow-hidden"
    >
      <div
        class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
      ></div>
      <div class="flex px-4 pt-3 gap-1">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="switchTab(tab.key)"
          class="flex items-center gap-2 px-5 py-2.5 text-xs font-bold uppercase tracking-widest whitespace-nowrap border-b-2 transition-all"
          :class="
            activeTab === tab.key
              ? 'border-[#229DE7] text-[#229DE7]'
              : 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-200'
          "
        >
          <span class="w-2 h-2 rounded-full" :class="tab.dot"></span>
          {{ tab.label }}
          <span
            v-if="hasData"
            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
            :class="
              activeTab === tab.key
                ? 'bg-[#EAF5FD] text-[#229DE7]'
                : 'bg-slate-100 text-slate-400'
            "
          >
            {{
              tab.key === "sales"
                ? saleData.summary.total_count
                : purchaseData.summary.total_count
            }}
          </span>
        </button>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5 mb-5">
      <div class="flex flex-wrap items-end gap-4">
        <!-- Period presets -->
        <div>
          <label class="field-label">Period</label>
          <div class="flex gap-2 mt-1 flex-wrap">
            <button
              v-for="p in presets"
              :key="p.value"
              @click="filters.preset = p.value"
              class="px-3.5 py-2 rounded-xl text-xs font-bold border transition"
              :class="
                filters.preset === p.value
                  ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm'
                  : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'
              "
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- Custom range -->
        <transition name="slide">
          <div v-if="filters.preset === 'custom'" class="flex items-end gap-3">
            <div>
              <label class="field-label">From</label>
              <input v-model="filters.from" type="date" class="field-input w-40" />
            </div>
            <div>
              <label class="field-label">To</label>
              <input v-model="filters.to" type="date" class="field-input w-40" />
            </div>
          </div>
        </transition>

        <!-- Payment method filter -->
        <div>
          <label class="field-label">Method</label>
          <select v-model="filters.method" class="field-input w-36">
            <option value="">All</option>
            <option v-for="m in paymentMethods" :key="m" :value="m" class="capitalize">
              {{ m }}
            </option>
          </select>
        </div>

        <!-- Search -->
        <div>
          <label class="field-label">Search</label>
          <div class="relative">
            <svg
              class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>

            <input
              v-model="filters.search"
              type="text"
              placeholder="Invoice, name, ref..."
              class="field-input pl-10 w-52"
            />
          </div>
        </div>

        <button @click="loadAll" :disabled="loading" class="run-btn ml-auto">
          <svg
            v-if="loading"
            class="animate-spin h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          {{ loading ? "Loading..." : "Load Transactions" }}
        </button>
      </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div v-if="loading" class="space-y-6">
      <!-- Summary Skeletons -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="n in 4"
          :key="n"
          class="h-24 bg-white border border-[#D9EBF7] animate-pulse rounded-2xl"
        ></div>
      </div>
      <!-- Method Skeletons -->
      <div class="h-32 bg-white border border-[#D9EBF7] animate-pulse rounded-2xl"></div>
      <!-- Table Skeletons -->
      <div class="report-table-wrap border-none shadow-none">
        <div class="space-y-2">
          <div
            v-for="n in 10"
            :key="n"
            class="h-12 bg-white border border-[#D9EBF7] animate-pulse rounded-xl"
          ></div>
        </div>
      </div>
    </div>

    <template v-else-if="hasData">
      <!-- SUMMARY CARDS -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="stat-card">
          <p class="stat-label">Sale Payments</p>
          <p class="stat-value text-emerald-600">
            {{ fmt(saleData.summary.total_amount) }}
          </p>
          <p class="text-[10.5px] text-slate-400 mt-1">
            {{ saleData.summary.total_count }} transaction(s)
          </p>
        </div>
        <div class="stat-card">
          <p class="stat-label">Purchase Payments</p>
          <p class="stat-value text-red-500">
            {{ fmt(purchaseData.summary.total_amount) }}
          </p>
          <p class="text-[10.5px] text-slate-400 mt-1">
            {{ purchaseData.summary.total_count }} transaction(s)
          </p>
        </div>
        <div class="stat-card">
          <p class="stat-label">Net Flow</p>
          <p
            class="stat-value"
            :class="netFlow >= 0 ? 'text-emerald-600' : 'text-red-500'"
          >
            {{ fmt(netFlow) }}
          </p>
          <p class="text-[10.5px] text-slate-400 mt-1">Sales − Purchases</p>
        </div>
        <div class="stat-card">
          <p class="stat-label">Total Transactions</p>
          <p class="stat-value text-slate-800">
            {{ saleData.summary.total_count + purchaseData.summary.total_count }}
          </p>
        </div>
      </div>

      <!-- METHOD BREAKDOWN -->
      <div
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden mb-5"
      >
        <div
          class="px-5 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            {{ activeTab === "sales" ? "Sale Payments" : "Purchase Payments" }} by Method
          </h3>
          <span
            class="text-[10px] font-bold px-2.5 py-1 rounded-full border"
            :class="
              activeTab === 'sales'
                ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                : 'bg-red-50 text-red-500 border-red-200'
            "
          >
            {{ activeTab === "sales" ? "MONEY IN" : "MONEY OUT" }}
          </span>
        </div>
        <div class="p-5 flex flex-wrap gap-4">
          <div
            v-for="m in activeMethodBreakdown"
            :key="m.payment_method"
            class="flex items-center gap-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-4 py-3 min-w-[180px]"
          >
            <span
              class="w-3 h-3 rounded-full flex-shrink-0"
              :class="methodDot(m.payment_method)"
            ></span>
            <div>
              <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400">
                {{ m.payment_method }}
              </p>
              <p class="text-base font-extrabold text-slate-800">{{ fmt(m.total) }}</p>
              <p class="text-[10px] text-slate-400">{{ m.count }} payment(s)</p>
            </div>
          </div>
          <p v-if="!activeMethodBreakdown?.length" class="text-sm text-slate-400 py-2">
            No payment data.
          </p>
        </div>
      </div>

      <!-- SALE PAYMENTS TABLE -->
      <div v-if="activeTab === 'sales'" class="report-table-wrap">
        <div class="report-table-header">
          <h3 class="report-table-title">Sale Payments</h3>
          <div class="relative ml-auto">
            <svg
              class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
            <input
              v-model="tableSearch"
              type="text"
              placeholder="Filter..."
              class="pl-9 pr-3 py-2 text-xs border border-[#D9EBF7] rounded-xl bg-[#F5FAFF] outline-none focus:border-[#229DE7] w-44"
            />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="report-table">
            <thead>
              <tr class="report-thead-tr">
                <th class="report-th">#</th>
                <th class="report-th">Date</th>
                <th class="report-th">Invoice</th>
                <th class="report-th">Customer</th>
                <th class="report-th">Method</th>
                <th class="report-th">Reference</th>
                <th class="report-th">Note</th>
                <th class="report-th">Received By</th>
                <th class="report-th text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="(row, i) in paginatedSale"
                :key="row.id"
                class="group hover:bg-[#F5FAFF] transition-colors"
              >
                <td class="report-td text-slate-400 font-bold text-xs">
                  {{ salePg.from + i }}
                </td>
                <td class="report-td text-xs whitespace-nowrap text-slate-500">
                  {{ formatDate(row.created_at) }}
                </td>
                <td class="report-td">
                  <span class="font-mono text-xs font-bold text-[#229DE7]">{{
                    row.invoice_number || "—"
                  }}</span>
                </td>
                <td class="report-td">
                  <p class="font-semibold text-sm text-slate-800 leading-tight">
                    {{ row.party_name }}
                  </p>
                  <p v-if="row.party_phone" class="text-[10.5px] text-slate-400">
                    {{ row.party_phone }}
                  </p>
                </td>
                <td class="report-td">
                  <span class="method-badge" :class="methodBadge(row.payment_method)">
                    <span
                      class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                      :class="methodDot(row.payment_method)"
                    ></span>
                    {{ row.payment_method }}
                  </span>
                </td>
                <td class="report-td text-xs font-mono text-slate-500">
                  {{ row.reference_no || "—" }}
                </td>
                <td
                  class="report-td text-xs text-slate-500 max-w-[160px] truncate"
                  :title="row.notes"
                >
                  {{ row.notes || "—" }}
                </td>
                <td class="report-td text-xs text-slate-500">
                  {{ row.received_by || "—" }}
                </td>
                <td class="report-td text-right">
                  <span class="font-extrabold text-emerald-600">{{
                    fmt(row.amount)
                  }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationBar
          v-if="filteredSale.length"
          :current="salePg.current"
          :total-pages="salePg.totalPages"
          :from="salePg.from"
          :to="salePg.to"
          :total="filteredSale.length"
          @prev="salePg.current--"
          @next="salePg.current++"
        />
      </div>

      <!-- PURCHASE PAYMENTS TABLE -->
      <div v-if="activeTab === 'purchases'" class="report-table-wrap">
        <div class="report-table-header">
          <h3 class="report-table-title">Purchase Payments</h3>
          <div class="relative ml-auto">
            <svg
              class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
            <input
              v-model="tableSearch"
              type="text"
              placeholder="Filter..."
              class="pl-9 pr-3 py-2 text-xs border border-[#D9EBF7] rounded-xl bg-[#F5FAFF] outline-none focus:border-[#229DE7] w-44"
            />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="report-table">
            <thead>
              <tr class="report-thead-tr">
                <th class="report-th">#</th>
                <th class="report-th">Date</th>
                <th class="report-th">Purchase Ref</th>
                <th class="report-th">Supplier</th>
                <th class="report-th">Method</th>
                <th class="report-th">Pay Reference</th>
                <th class="report-th">Notes</th>
                <th class="report-th">Paid By</th>
                <th class="report-th text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="(row, i) in paginatedPurchase"
                :key="row.id"
                class="group hover:bg-[#F5FAFF] transition-colors"
              >
                <td class="report-td text-slate-400 font-bold text-xs">
                  {{ purchasePg.from + i }}
                </td>
                <td class="report-td text-xs whitespace-nowrap text-slate-500">
                  {{ formatDate(row.created_at) }}
                </td>
                <td class="report-td">
                  <span class="font-mono text-xs font-bold text-red-400">{{
                    row.purchase_ref || "—"
                  }}</span>
                </td>
                <td class="report-td">
                  <p class="font-semibold text-sm text-slate-800 leading-tight">
                    {{ row.party_name }}
                  </p>
                  <p v-if="row.party_phone" class="text-[10.5px] text-slate-400">
                    {{ row.party_phone }}
                  </p>
                </td>
                <td class="report-td">
                  <span class="method-badge" :class="methodBadge(row.payment_method)">
                    <span
                      class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                      :class="methodDot(row.payment_method)"
                    ></span>
                    {{ row.payment_method }}
                  </span>
                </td>
                <td class="report-td text-xs font-mono text-slate-500">
                  {{ row.reference_no || "—" }}
                </td>
                <td
                  class="report-td text-xs text-slate-500 max-w-[160px] truncate"
                  :title="row.notes"
                >
                  {{ row.notes || "—" }}
                </td>
                <td class="report-td text-xs text-slate-500">{{ row.paid_by || "—" }}</td>
                <td class="report-td text-right">
                  <span class="font-extrabold text-red-500">{{ fmt(row.amount) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationBar
          v-if="filteredPurchase.length"
          :current="purchasePg.current"
          :total-pages="purchasePg.totalPages"
          :from="purchasePg.from"
          :to="purchasePg.to"
          :total="filteredPurchase.length"
          @prev="purchasePg.current--"
          @next="purchasePg.current++"
        />
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="py-24 text-center">
      <div
        class="w-20 h-20 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-5"
      >
        <svg
          class="w-10 h-10 text-[#229DE7]"
          fill="none"
          stroke="currentColor"
          stroke-width="1.6"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
          />
        </svg>
      </div>
      <h3 class="font-bold text-base text-slate-900 mb-1">No transactions found</h3>
      <p class="text-slate-400 text-sm">
        Select a period above and click Load Transactions.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, defineComponent, h, watch } from "vue";
import Swal from "sweetalert2";
import ReportsService from "@/services/ReportService";

/* PAGINATION BAR COMPONENT */
const PaginationBar = defineComponent({
  props: ["current", "totalPages", "from", "to", "total"],
  emits: ["prev", "next"],
  setup(props, { emit }) {
    return () =>
      h(
        "div",
        {
          class:
            "px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3",
        },
        [
          h(
            "p",
            { class: "text-sm text-slate-500 font-semibold" },
            `Showing ${props.from}–${props.to} of ${props.total}`
          ),
          h("div", { class: "flex items-center gap-2" }, [
            h(
              "button",
              {
                disabled: props.current === 1,
                onClick: () => emit("prev"),
                class:
                  "px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm",
              },
              "← Previous"
            ),
            h(
              "div",
              {
                class:
                  "flex items-center px-4 py-2 bg-[#229DE7] rounded-xl text-sm font-bold text-white shadow-md shadow-[#229DE7]/30",
              },
              `${props.current} / ${props.totalPages}`
            ),
            h(
              "button",
              {
                disabled: props.current >= props.totalPages,
                onClick: () => emit("next"),
                class:
                  "px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm",
              },
              "Next →"
            ),
          ]),
        ]
      );
  },
});

const PAGE_SIZE = 15;
const paymentMethods = ["cash", "mpesa", "card", "bank", "cheque", "other"];
const presets = [
  { label: "Today", value: "today" },
  { label: "This Week", value: "this_week" },
  { label: "This Month", value: "this_month" },
  { label: "Custom", value: "custom" },
];
const tabs = [
  { key: "sales", label: "Sale Payments", dot: "bg-emerald-500" },
  { key: "purchases", label: "Purchase Payments", dot: "bg-red-500" },
];

const loading = ref(false);
const activeTab = ref("sales");
const tableSearch = ref("");
const today = new Date().toISOString().split("T")[0];

const filters = reactive({
  preset: "today",
  from: today,
  to: today,
  method: "",
  search: "",
});
const saleData = reactive({
  summary: { total_count: 0, total_amount: 0, by_method: [] },
  payments: [],
});
const purchaseData = reactive({
  summary: { total_count: 0, total_amount: 0, by_method: [] },
  payments: [],
});

const salePg = reactive({ current: 1, totalPages: 1, from: 1, to: 0 });
const purchasePg = reactive({ current: 1, totalPages: 1, from: 1, to: 0 });

const fmt = (v) =>
  new Intl.NumberFormat("en-KE", { style: "currency", currency: "KES" }).format(v ?? 0);
const formatDate = (v) =>
  v
    ? new Date(v).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : "—";
const methodDot = (m) =>
  ({
    cash: "bg-emerald-500",
    mpesa: "bg-[#229DE7]",
    card: "bg-violet-500",
    bank: "bg-amber-500",
    cheque: "bg-slate-400",
  }[(m || "").toLowerCase()] ?? "bg-slate-300");
const methodBadge = (m) =>
  ({
    cash: "bg-emerald-50 text-emerald-700 border-emerald-200",
    mpesa: "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]",
    card: "bg-violet-50 text-violet-700 border-violet-200",
    bank: "bg-amber-50 text-amber-700 border-amber-200",
    cheque: "bg-slate-100 text-slate-600 border-slate-200",
  }[(m || "").toLowerCase()] ?? "bg-slate-100 text-slate-500 border-slate-200");

const rangeParams = () =>
  filters.preset === "custom"
    ? { preset: "custom", from: filters.from, to: filters.to }
    : { preset: filters.preset };
const switchTab = (key) => {
  activeTab.value = key;
  tableSearch.value = "";
};

const hasData = computed(
  () => saleData.payments.length > 0 || purchaseData.payments.length > 0
);
const netFlow = computed(
  () => saleData.summary.total_amount - purchaseData.summary.total_amount
);
const activeMethodBreakdown = computed(() =>
  activeTab.value === "sales"
    ? saleData.summary.by_method
    : purchaseData.summary.by_method
);

const filteredSale = computed(() => {
  const q = tableSearch.value.toLowerCase();
  return q
    ? saleData.payments.filter(
        (r) =>
          (r.invoice_number || "").toLowerCase().includes(q) ||
          (r.party_name || "").toLowerCase().includes(q)
      )
    : saleData.payments;
});

const paginatedSale = computed(() => {
  const start = (salePg.current - 1) * PAGE_SIZE;
  salePg.totalPages = Math.max(1, Math.ceil(filteredSale.value.length / PAGE_SIZE));
  salePg.from = filteredSale.value.length ? start + 1 : 0;
  salePg.to = Math.min(start + PAGE_SIZE, filteredSale.value.length);
  return filteredSale.value.slice(start, start + PAGE_SIZE);
});

const filteredPurchase = computed(() => {
  const q = tableSearch.value.toLowerCase();
  return q
    ? purchaseData.payments.filter(
        (r) =>
          (r.purchase_ref || "").toLowerCase().includes(q) ||
          (r.party_name || "").toLowerCase().includes(q)
      )
    : purchaseData.payments;
});

const paginatedPurchase = computed(() => {
  const start = (purchasePg.current - 1) * PAGE_SIZE;
  purchasePg.totalPages = Math.max(
    1,
    Math.ceil(filteredPurchase.value.length / PAGE_SIZE)
  );
  purchasePg.from = filteredPurchase.value.length ? start + 1 : 0;
  purchasePg.to = Math.min(start + PAGE_SIZE, filteredPurchase.value.length);
  return filteredPurchase.value.slice(start, start + PAGE_SIZE);
});

watch(tableSearch, () => {
  salePg.current = 1;
  purchasePg.current = 1;
});

const loadAll = async () => {
  loading.value = true;
  const params = {
    ...rangeParams(),
    ...(filters.method && { method: filters.method }),
    ...(filters.search && { search: filters.search }),
  };
  try {
    const [sRes, pRes] = await Promise.all([
      ReportsService.salePayments(params),
      ReportsService.purchasePayments(params),
    ]);
    const sd = sRes?.data?.data ?? sRes?.data;
    const pd = pRes?.data?.data ?? pRes?.data;
    saleData.payments = sd?.payments ?? [];
    saleData.summary = sd?.summary ?? { total_count: 0, total_amount: 0, by_method: [] };
    purchaseData.payments = pd?.payments ?? [];
    purchaseData.summary = pd?.summary ?? {
      total_count: 0,
      total_amount: 0,
      by_method: [],
    };
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err?.response?.data?.message || "Something went wrong.",
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Keeping only necessary transitions */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.field-input {
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  outline: none;
}
.field-input:focus {
  border-color: #229de7;
  background: #fff;
}

.run-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: #229de7;
  color: white;
  font-size: 13px;
  font-weight: 700;
  border-radius: 12px;
  cursor: pointer;
}
.run-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.stat-card {
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  padding: 16px;
}
.stat-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}
.stat-value {
  font-size: 20px;
  font-weight: 800;
}

.report-table-wrap {
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  overflow: hidden;
}
.report-table-header {
  padding: 16px 20px;
  border-bottom: 1px solid #eef5fb;
  display: flex;
  align-items: center;
}
.report-table-title {
  font-size: 12px;
  font-weight: 800;
  color: #1682c4;
  text-transform: uppercase;
}
.report-table {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
.report-thead-tr {
  background: #eaf5fd;
  border-bottom: 1px solid #b3defa;
}
.report-th {
  padding: 12px 20px;
  font-size: 10.5px;
  font-weight: 700;
  color: #1682c4;
  text-transform: uppercase;
}
.report-td {
  padding: 13px 20px;
  font-size: 13px;
  color: #334155;
}

.method-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
  border: 1px solid;
  text-transform: capitalize;
}
</style>
