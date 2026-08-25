<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK BUTTON + HEADER -->
    <div class="mb-6">
      <button
        @click="$router.back()"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#229DE7] hover:text-[#1682C4] mb-5 transition group"
      >
        <svg
          class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Suppliers
      </button>

      <!-- Profile Hero Card -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
        ></div>
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
          <!-- Avatar -->
          <div
            class="w-16 h-16 rounded-2xl bg-[#EAF5FD] border-2 border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xl flex-shrink-0 shadow-inner"
          >
            {{ initials }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-3">
              <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                {{ customer?.name || "—" }}
              </h1>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                :class="statusClass(customer?.status)"
              >
                {{ customer?.status ? capitalize(customer.status) : "—" }}
              </span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-1.5">
              <span class="text-sm text-slate-500 flex items-center gap-1.5">
                <svg
                  class="w-3.5 h-3.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
                {{ customer?.email || "—" }}
              </span>
              <span class="text-sm text-slate-500 flex items-center gap-1.5">
                <svg
                  class="w-3.5 h-3.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
                {{ customer?.phone || "—" }}
              </span>
            </div>
            <!-- Branches -->
            <div class="flex flex-wrap gap-1.5 mt-2.5">
              <span
                v-for="b in customer?.branches"
                :key="b.id"
                class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10.5px] font-bold"
              >
                {{ b.name }}
              </span>
            </div>
          </div>

          <!-- Summary Stats -->
          <div class="flex gap-4 flex-shrink-0">
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-xl font-extrabold text-[#229DE7]">{{ sales.length }}</div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-0.5"
              >
                Sales
              </div>
            </div>
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-xl font-extrabold text-emerald-600">
                {{ formatCurrency(totalPaid) }}
              </div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-0.5"
              >
                Paid
              </div>
            </div>
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-xl font-extrabold text-red-500">
                {{ formatCurrency(totalBalance) }}
              </div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-0.5"
              >
                Balance
              </div>
            </div>
          </div>
        </div>

        <!-- Notes strip -->
        <div
          v-if="customer?.notes"
          class="px-6 py-3 bg-amber-50 border-t border-amber-100 flex items-start gap-2"
        >
          <svg
            class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
            />
          </svg>
          <p class="text-xs text-amber-700 font-semibold">{{ customer.notes }}</p>
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div
      class="flex gap-1 mb-5 bg-white border border-[#D9EBF7] rounded-xl p-1 w-fit shadow-sm"
    >
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-5 py-2 rounded-lg text-sm font-bold transition-all"
        :class="
          activeTab === tab.key
            ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
            : 'text-slate-500 hover:text-[#229DE7] hover:bg-[#EAF5FD]'
        "
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- ══════════ SALES TAB ══════════ -->
    <div v-if="activeTab === 'sales'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Sales History
          </h2>
          <span class="text-xs font-bold text-slate-400"
            >{{ sales.length }} record{{ sales.length !== 1 ? "s" : "" }}</span
          >
        </div>

        <!-- Skeleton -->
        <div v-if="salesLoading" class="p-6 space-y-3">
          <div
            v-for="n in 4"
            :key="n"
            class="h-12 bg-slate-50 animate-pulse rounded-xl"
          ></div>
        </div>

        <!-- Empty -->
        <div v-else-if="sales.length === 0" class="py-16 text-center">
          <div
            class="w-14 h-14 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-3"
          >
            <svg
              class="w-7 h-7 text-[#229DE7]"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
          <p class="font-bold text-slate-700">No sales yet</p>
          <p class="text-xs text-slate-400 mt-1">This customer has no recorded sales.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr
                class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
              >
                <th class="px-5 py-3.5 w-10">#</th>
                <th class="px-5 py-3.5">Sale Ref</th>
                <th class="px-5 py-3.5">Date</th>
                <th class="px-5 py-3.5">Items</th>
                <th class="px-5 py-3.5">Total</th>
                <th class="px-5 py-3.5">Paid</th>
                <th class="px-5 py-3.5">Balance</th>
                <th class="px-5 py-3.5">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="(sale, i) in sales"
                :key="sale.id"
                class="group hover:bg-[#F5FAFF] transition-colors"
              >
                <td class="px-5 py-3.5 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                <td class="px-5 py-3.5">
                  <span class="font-bold text-sm text-slate-800">{{
                    sale.reference || `#${sale.id}`
                  }}</span>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500 font-semibold">
                  {{ formatDate(sale.created_at || sale.date) }}
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-600 font-semibold">
                  {{ sale.items_count ?? sale.items?.length ?? "—" }}
                </td>
                <td class="px-5 py-3.5 text-sm font-bold text-slate-800">
                  {{ formatCurrency(sale.total_amount ?? sale.total) }}
                </td>
                <td class="px-5 py-3.5 text-sm font-bold text-emerald-600">
                  {{ formatCurrency(sale.paid_amount ?? sale.paid) }}
                </td>
                <td
                  class="px-5 py-3.5 text-sm font-bold"
                  :class="
                    (sale.balance ?? sale.total_amount - sale.paid_amount) > 0
                      ? 'text-red-500'
                      : 'text-slate-400'
                  "
                >
                  {{
                    formatCurrency(
                      sale.balance ?? (sale.total_amount ?? 0) - (sale.paid_amount ?? 0)
                    )
                  }}
                </td>
                <td class="px-5 py-3.5">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border"
                    :class="saleStatusClass(sale.payment_status ?? sale.status)"
                  >
                    {{ capitalize(sale.payment_status ?? sale.status ?? "—") }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sales footer totals -->
        <div
          v-if="sales.length > 0"
          class="px-6 py-4 border-t border-[#EEF5FB] flex flex-wrap gap-6 justify-end"
        >
          <div class="text-sm">
            <span class="text-slate-500 font-semibold">Total Sales: </span>
            <span class="font-extrabold text-slate-800">{{
              formatCurrency(totalSales)
            }}</span>
          </div>
          <div class="text-sm">
            <span class="text-slate-500 font-semibold">Total Paid: </span>
            <span class="font-extrabold text-emerald-600">{{
              formatCurrency(totalPaid)
            }}</span>
          </div>
          <div class="text-sm">
            <span class="text-slate-500 font-semibold">Outstanding: </span>
            <span class="font-extrabold text-red-500">{{
              formatCurrency(totalBalance)
            }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ PAYMENTS TAB ══════════ -->
    <div v-if="activeTab === 'payments'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Payment History
          </h2>
          <span class="text-xs font-bold text-slate-400"
            >{{ payments.length }} record{{ payments.length !== 1 ? "s" : "" }}</span
          >
        </div>

        <!-- Skeleton -->
        <div v-if="paymentsLoading" class="p-6 space-y-3">
          <div
            v-for="n in 4"
            :key="n"
            class="h-12 bg-slate-50 animate-pulse rounded-xl"
          ></div>
        </div>

        <!-- Empty -->
        <div v-else-if="payments.length === 0" class="py-16 text-center">
          <div
            class="w-14 h-14 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-3"
          >
            <svg
              class="w-7 h-7 text-[#229DE7]"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
              />
            </svg>
          </div>
          <p class="font-bold text-slate-700">No payments yet</p>
          <p class="text-xs text-slate-400 mt-1">
            This customer has no recorded payments.
          </p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr
                class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
              >
                <th class="px-5 py-3.5 w-10">#</th>
                <th class="px-5 py-3.5">Reference</th>
                <th class="px-5 py-3.5">Sale Ref</th>
                <th class="px-5 py-3.5">Date</th>
                <th class="px-5 py-3.5">Method</th>
                <th class="px-5 py-3.5">Amount</th>
                <th class="px-5 py-3.5">Note</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="(payment, i) in payments"
                :key="payment.id"
                class="group hover:bg-[#F5FAFF] transition-colors"
              >
                <td class="px-5 py-3.5 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                <td class="px-5 py-3.5">
                  <span class="font-bold text-sm text-slate-800">{{
                    payment.reference || `#${payment.id}`
                  }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <span class="text-xs font-semibold text-[#1682C4]">{{
                    payment.sale_reference || payment.sale?.reference || "—"
                  }}</span>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500 font-semibold">
                  {{ formatDate(payment.created_at || payment.date) }}
                </td>
                <td class="px-5 py-3.5">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                  >
                    {{ capitalize(payment.payment_method ?? payment.method ?? "Cash") }}
                  </span>
                </td>
                <td class="px-5 py-3.5 text-sm font-extrabold text-emerald-600">
                  {{ formatCurrency(payment.amount) }}
                </td>
                <td
                  class="px-5 py-3.5 text-xs text-slate-400 italic max-w-[140px] truncate"
                >
                  {{ payment.note || payment.notes || "—" }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Payments footer -->
        <div
          v-if="payments.length > 0"
          class="px-6 py-4 border-t border-[#EEF5FB] flex justify-end"
        >
          <div class="text-sm">
            <span class="text-slate-500 font-semibold">Total Received: </span>
            <span class="font-extrabold text-emerald-600">{{
              formatCurrency(totalPaid)
            }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════ DETAILS TAB ══════════ -->
    <div v-if="activeTab === 'details'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
        ></div>
        <div class="px-6 py-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Customer Details
          </h2>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div class="detail-block">
            <span class="detail-label">Full Name</span>
            <span class="detail-value">{{ customer?.name || "—" }}</span>
          </div>
          <div class="detail-block">
            <span class="detail-label">Email Address</span>
            <span class="detail-value">{{ customer?.email || "—" }}</span>
          </div>
          <div class="detail-block">
            <span class="detail-label">Phone Number</span>
            <span class="detail-value">{{ customer?.phone || "—" }}</span>
          </div>
          <div class="detail-block">
            <span class="detail-label">Status</span>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border w-fit mt-1"
              :class="statusClass(customer?.status)"
            >
              {{ customer?.status ? capitalize(customer.status) : "—" }}
            </span>
          </div>
          <div class="detail-block sm:col-span-2">
            <span class="detail-label">Assigned Branches</span>
            <div class="flex flex-wrap gap-2 mt-1.5">
              <span
                v-for="b in customer?.branches"
                :key="b.id"
                class="px-3 py-1 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-xs font-bold"
              >
                {{ b.name }}
              </span>
              <span v-if="!customer?.branches?.length" class="text-sm text-slate-400"
                >—</span
              >
            </div>
          </div>
          <div class="detail-block sm:col-span-2">
            <span class="detail-label">Notes</span>
            <span class="detail-value">{{
              customer?.notes || "No notes provided."
            }}</span>
          </div>
          <div class="detail-block">
            <span class="detail-label">Created</span>
            <span class="detail-value">{{ formatDate(customer?.created_at) }}</span>
          </div>
          <div class="detail-block">
            <span class="detail-label">Last Updated</span>
            <span class="detail-value">{{ formatDate(customer?.updated_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import CustomerService from "@/services/CustomerService";

/* ─── ROUTE ───────────────────────────── */
const route = useRoute();
const customerId = route.params.id;

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const salesLoading = ref(false);
const paymentsLoading = ref(false);

const customer = ref(null);
const sales = ref([]);
const payments = ref([]);

const activeTab = ref("sales");
const tabs = [
  { key: "sales", label: "Sales" },
  { key: "payments", label: "Payments" },
  { key: "details", label: "Details" },
];

/* ─── COMPUTED ────────────────────────── */
const initials = computed(() => {
  if (!customer.value?.name) return "?";
  return customer.value.name
    .split(" ")
    .slice(0, 2)
    .map((w) => w[0])
    .join("")
    .toUpperCase();
});

const totalSales = computed(() =>
  sales.value.reduce((sum, s) => sum + Number(s.total_amount ?? s.total ?? 0), 0)
);
const totalPaid = computed(() =>
  payments.value.reduce((sum, p) => sum + Number(p.amount ?? 0), 0)
);
const totalBalance = computed(() => totalSales.value - totalPaid.value);

/* ─── HELPERS ─────────────────────────── */
const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : "—");

const formatDate = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatCurrency = (val) => {
  const num = Number(val ?? 0);
  return new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(num);
};

const statusClass = (status) => {
  const map = {
    active: "bg-emerald-50 text-emerald-700 border-emerald-200",
    inactive: "bg-slate-100 text-slate-500 border-slate-200",
    suspended: "bg-red-50 text-red-600 border-red-200",
  };
  return map[status] || "bg-slate-100 text-slate-500 border-slate-200";
};

const saleStatusClass = (status) => {
  const map = {
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    partial: "bg-amber-50 text-amber-700 border-amber-200",
    unpaid: "bg-red-50 text-red-600 border-red-200",
    pending: "bg-amber-50 text-amber-700 border-amber-200",
    completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
    cancelled: "bg-slate-100 text-slate-500 border-slate-200",
  };
  return map[status?.toLowerCase()] || "bg-slate-100 text-slate-500 border-slate-200";
};

/* ─── FETCH ───────────────────────────── */
const unwrap = (res) => {
  return res?.data?.data ?? res?.data ?? res ?? [];
};

const initData = async () => {
  pageLoading.value = true;

  try {
    // ─── Customer ─────────────────────
    const customerRes = await CustomerService.getById(customerId);
    customer.value = customerRes?.data?.data ?? customerRes?.data ?? customerRes ?? null;

    // ─── Loading states ───────────────
    salesLoading.value = true;
    paymentsLoading.value = true;

    // ─── Parallel fetch ───────────────
    const [salesRes, paymentsRes] = await Promise.all([
      CustomerService.getSales(customerId),
      CustomerService.getPayments(customerId),
    ]);

    // ─── Normalize responses ──────────
    sales.value = unwrap(salesRes);
    payments.value = unwrap(paymentsRes);

    console.log("SALES RESPONSE:", salesRes);
    console.log("PAYMENTS RESPONSE:", paymentsRes);
  } catch (err) {
    console.error("initData error:", err);

    // fail-safe defaults (IMPORTANT)
    sales.value = [];
    payments.value = [];
    customer.value = null;
  } finally {
    pageLoading.value = false;
    salesLoading.value = false;
    paymentsLoading.value = false;
  }
};

onMounted(initData);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.detail-block {
  display: flex;
  flex-direction: column;
  padding: 14px 16px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
}

.detail-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}
</style>
