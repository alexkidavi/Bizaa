<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Purchases</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Track and manage all stock purchases across branches.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative w-full md:w-64">
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
            v-model="searchQuery"
            type="text"
            placeholder="Search purchases..."
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
          />
        </div>
        <router-link
          :to="{ name: 'NewPurchase' }"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] whitespace-nowrap"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          New Purchase
        </router-link>
      </div>
    </div>

    <!-- STATS ROW -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          Total Purchases
        </p>
        <p class="text-2xl font-extrabold text-slate-900">{{ summary.total_count }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          Total Value
        </p>
        <p class="text-2xl font-extrabold text-[#229DE7]">
          {{ formatCurrency(totalValue) }}
        </p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          Total Paid
        </p>
        <p class="text-2xl font-extrabold text-emerald-600">
          {{ formatCurrency(totalPaid) }}
        </p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          Outstanding
        </p>
        <p class="text-2xl font-extrabold text-red-500">
          {{ formatCurrency(totalBalance) }}
        </p>
      </div>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <!-- Filter bar -->
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mr-1"
          >Filter:</span
        >
        <button
          v-for="f in statusFilters"
          :key="f.value"
          @click="activeFilter = f.value"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition"
          :class="
            activeFilter === f.value
              ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm'
              : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'
          "
        >
          {{ f.label }}
        </button>
      </div>

      <!-- Skeleton -->
      <div v-if="tableLoading" class="p-6 space-y-3">
        <div
          v-for="n in 5"
          :key="n"
          class="h-14 bg-slate-50 animate-pulse rounded-xl"
        ></div>
      </div>

      <!-- Empty -->
      <div v-else-if="filteredPurchases.length === 0" class="py-20 text-center">
        <div
          class="w-16 h-16 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-4"
        >
          <svg
            class="w-8 h-8 text-[#229DE7]"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
            />
          </svg>
        </div>
        <h3 class="font-bold text-base text-slate-900">No purchases found</h3>
        <p class="text-slate-500 text-sm mt-1">
          Create your first purchase to get started.
        </p>
        <router-link
          :to="{ name: 'NewPurchase' }"
          class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-[#229DE7] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/25 transition hover:bg-[#1682C4]"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          New Purchase
        </router-link>
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
              <th class="px-5 py-3.5">Supplier</th>
              <th class="px-5 py-3.5">Branches</th>
              <th class="px-5 py-3.5">Date</th>
              <th class="px-5 py-3.5">Total</th>
              <th class="px-5 py-3.5">Paid</th>
              <th class="px-5 py-3.5">Balance</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr
              v-for="(purchase, index) in paginatedPurchases"
              :key="purchase.id"
              class="group hover:bg-[#F5FAFF] transition-colors"
            >
              <td class="px-5 py-4 text-xs font-bold text-slate-400">
                {{ (currentPage - 1) * itemsPerPage + index + 1 }}
              </td>
              <td class="px-5 py-4">
                <span class="font-extrabold text-sm text-slate-800">
                  {{ purchase.reference }}
                </span>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2.5">
                  <div
                    class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-[10px] flex-shrink-0"
                  >
                    {{ (purchase.supplier?.name || "S").substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-bold text-sm text-slate-800">
                      {{ purchase.supplier?.name || "—" }}
                    </div>
                    <div class="text-[10.5px] text-slate-400">
                      {{ purchase.supplier?.phone || "" }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="b in (purchase.branches || []).slice(0, 2)"
                    :key="b.id"
                    class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10px] font-bold"
                    >{{ b.name }}</span
                  >
                  <span
                    v-if="(purchase.branches || []).length > 2"
                    class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold"
                    >+{{ purchase.branches.length - 2 }}</span
                  >
                </div>
              </td>
              <td
                class="px-5 py-4 text-xs text-slate-500 font-semibold whitespace-nowrap"
              >
                {{ formatDate(purchase.purchase_date || purchase.created_at) }}
              </td>
              <td class="px-5 py-4 font-extrabold text-sm text-slate-800">
                {{ formatCurrency(purchase.grand_total ?? purchase.total_amount ?? 0) }}
              </td>
              <td class="px-5 py-4 font-bold text-sm text-emerald-600">
                {{ formatCurrency(purchase.paid_amount ?? 0) }}
              </td>
              <td
                class="px-5 py-4 font-bold text-sm"
                :class="(purchase.balance ?? 0) > 0 ? 'text-red-500' : 'text-slate-400'"
              >
                {{ formatCurrency(purchase.balance ?? 0) }}
              </td>
              <td class="px-5 py-4">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border"
                  :class="paymentStatusClass(purchase.payment_status)"
                >
                  {{ capitalize(purchase.payment_status ?? "pending") }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <router-link
                    :to="{ name: 'ViewPurchase', params: { id: purchase.id } }"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 border border-transparent hover:bg-slate-100 hover:border-slate-200 transition"
                    title="View"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.2"
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
                  </router-link>
                  <router-link
                    :to="{ name: 'EditPurchase', params: { id: purchase.id } }"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] border border-transparent hover:bg-[#EAF5FD] hover:border-[#B3DEFA] transition"
                    title="Edit"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                      />
                    </svg>
                  </router-link>
                  <router-link
                    :to="{ name: 'PurchaseDocuments', params: { id: purchase.id } }"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-500 border border-transparent hover:bg-blue-50 hover:border-blue-200 transition"
                    title="Documents"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M14 3v5h5"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 13h6M9 17h6M9 9h2"
                      />
                    </svg>
                  </router-link>
                  <button
                    @click="deletePurchase(purchase.id)"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 border border-transparent hover:bg-red-50 hover:border-red-200 transition"
                    title="Delete"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div
        v-if="filteredPurchases.length > 0"
        class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
      >
        <div class="text-sm text-slate-500 font-semibold">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }}–{{
            Math.min(currentPage * itemsPerPage, filteredPurchases.length)
          }}
          of {{ filteredPurchases.length }} purchases
        </div>
        <div class="flex items-center gap-2">
          <button
            :disabled="currentPage === 1"
            @click="currentPage--"
            class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
          >
            ← Previous
          </button>
          <div
            class="flex items-center px-4 py-2 bg-[#229DE7] rounded-xl text-sm font-bold text-white shadow-md shadow-[#229DE7]/30"
          >
            {{ currentPage }} / {{ totalPages }}
          </div>
          <button
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
            class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
          >
            Next →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import Swal from "sweetalert2";
import PurchaseService from "@/services/purchaseService";

/* ─── STATE ───────────────────────────── */
const tableLoading = ref(false);
const purchases = ref([]);
const summary = ref({ total_count: 0, total_value: 0, total_paid: 0, total_balance: 0 });
const searchQuery = ref("");
const activeFilter = ref("all");
const currentPage = ref(1);
const itemsPerPage = 10;

const statusFilters = [
  { label: "All", value: "all" },
  { label: "Paid", value: "paid" },
  { label: "Partial", value: "partial" },
  { label: "Unpaid", value: "unpaid" },
];

/* ─── COMPUTED ────────────────────────── */
const filteredPurchases = computed(() =>
  purchases.value.filter((p) => {
    const matchSearch =
      (p.reference || "").toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (p.supplier?.name || "").toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchFilter =
      activeFilter.value === "all" || p.payment_status === activeFilter.value;
    return matchSearch && matchFilter;
  })
);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredPurchases.value.length / itemsPerPage))
);
const paginatedPurchases = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredPurchases.value.slice(start, start + itemsPerPage);
});

const totalValue = computed(() => Number(summary.value.total_value ?? 0));
const totalPaid = computed(() => Number(summary.value.total_paid ?? 0));
const totalBalance = computed(() => Number(summary.value.total_balance ?? 0));

watch([searchQuery, activeFilter], () => {
  currentPage.value = 1;
});

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

const formatCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

const paymentStatusClass = (status) => {
  const map = {
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    partial: "bg-amber-50 text-amber-700 border-amber-200",
    unpaid: "bg-red-50 text-red-600 border-red-200",
    pending: "bg-slate-100 text-slate-500 border-slate-200",
  };
  return map[status?.toLowerCase()] || "bg-slate-100 text-slate-500 border-slate-200";
};

/* ─── NOTIFY ──────────────────────────── */
const notify = (icon, title) =>
  Swal.fire({
    icon,
    title,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });

/* ─── FETCH ───────────────────────────── */
const loadPurchases = async () => {
  tableLoading.value = true;
  try {
    const res = await PurchaseService.getAll({ per_page: 100 });
    purchases.value = res?.data?.data ?? res?.data ?? res ?? [];
    summary.value = res?.data?.summary ?? summary.value;
  } catch {
    Swal.fire("Error", "Could not load purchases.", "error");
  } finally {
    tableLoading.value = false;
  }
};

onMounted(loadPurchases);

/* ─── DELETE ──────────────────────────── */
const deletePurchase = async (id) => {
  const result = await Swal.fire({
    title: "Delete this purchase?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  try {
    await PurchaseService.delete(id);
    purchases.value = purchases.value.filter((p) => p.id !== id);
    notify("success", "Purchase Deleted");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};
</script>
