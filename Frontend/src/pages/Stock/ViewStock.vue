<template>
  <div class="p-4 md:p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK -->
    <div class="mb-5">
      <button
        @click="router.back()"
        class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#229DE7] transition group"
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
        Back to Stock Takes
      </button>
    </div>

    <!-- SESSION HERO CARD -->
    <div
      class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden mb-5"
      v-if="session"
    >
      <div
        class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
      ></div>
      <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div class="flex items-start gap-4">
            <div
              class="w-12 h-12 rounded-2xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"
                />
              </svg>
            </div>
            <div>
              <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                {{ session.name }}
              </h1>
              <div class="flex flex-wrap items-center gap-2 mt-1.5">
                <span class="font-mono text-xs text-[#229DE7] font-bold">{{
                  session.reference
                }}</span>
                <span class="text-slate-300">·</span>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border"
                  :class="statusBadge(session.status)"
                >
                  {{ session.status || "Draft" }}
                </span>
                <span class="text-slate-300">·</span>
                <span class="text-xs text-slate-400 font-semibold">{{
                  formatDate(session.created_at)
                }}</span>
              </div>
              <p
                v-if="session.description"
                class="text-xs text-slate-400 mt-1.5 font-semibold"
              >
                {{ session.description }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 flex-shrink-0">
            <button
              @click="exportCSV"
              class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-[#D9EBF7] rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4-4-4M12 14V4"
                />
              </svg>
              Export
            </button>
          </div>
        </div>

        <!-- Summary -->
        <div
          class="mt-5 pt-4 border-t border-[#EEF5FB] grid grid-cols-2 sm:grid-cols-4 gap-4"
        >
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Total Items
            </div>
            <div class="font-extrabold text-slate-900 text-lg">
              {{ pagination.total }}
            </div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Counted
            </div>
            <div class="font-extrabold text-emerald-600 text-lg">
              {{ session.counted_count ?? "—" }}
            </div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Variances
            </div>
            <div class="font-extrabold text-amber-600 text-lg">
              {{ session.variance_count ?? "—" }}
            </div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Uncounted
            </div>
            <div class="font-extrabold text-slate-400 text-lg">
              {{ session.uncounted_count ?? "—" }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTERS -->
    <!-- <div class="flex flex-col sm:flex-row gap-3 mb-5">
      <div class="relative flex-1">
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
          placeholder="Search by product name or code..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
      <select
        v-model="filters.branch_id"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Branches</option>
        <option v-for="b in sessionBranches" :key="b.id" :value="b.id">
          {{ b.name }}
        </option>
      </select>
      <select
        v-model="filters.category_id"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Categories</option>
        <option v-for="c in sessionCategories" :key="c.id" :value="c.id">
          {{ c.name }}
        </option>
      </select>
      <select
        v-model="filters.status"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Items</option>
        <option value="counted">Counted</option>
        <option value="uncounted">Uncounted</option>
        <option value="variance">With Variance</option>
      </select>
    </div> -->

    <!-- TABLE CARD -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm">
      <!-- Skeleton -->
      <div v-if="itemsLoading" class="p-6 space-y-3">
        <div
          v-for="n in 8"
          :key="n"
          class="h-14 bg-slate-50 animate-pulse rounded-xl"
        ></div>
      </div>

      <!-- Empty -->
      <div v-else-if="items.length === 0" class="py-20 text-center">
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
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
            />
          </svg>
        </div>
        <h3 class="font-bold text-base text-slate-900">No items found</h3>
        <p class="text-slate-500 text-sm mt-1">Try adjusting your filters.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
            >
              <th class="px-5 py-3.5 w-10">#</th>
              <th class="px-5 py-3.5">Product</th>
              <th class="px-5 py-3.5">Category</th>
              <th class="px-5 py-3.5">Branch</th>
              <th class="px-5 py-3.5 text-center">System Qty</th>
              <th class="px-5 py-3.5 text-center">Counted Qty</th>
              <th class="px-5 py-3.5 text-center">Variance</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5">Notes</th>
              <th class="px-3 py-3.5 w-8"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr
              v-for="(item, index) in items"
              :key="item.id"
              class="group hover:bg-[#F5FAFF] transition-colors"
              :class="{
                'bg-red-50/30':
                  item.counted_stock !== null &&
                  item.counted_stock !== undefined &&
                  item.variance < 0,
                'bg-[#EAF5FD]/30':
                  item.counted_stock !== null &&
                  item.counted_stock !== undefined &&
                  item.variance > 0,
              }"
            >
              <td class="px-5 py-3 text-xs font-bold text-slate-400">
                {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
              </td>

              <!-- Product -->
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div
                    class="w-8 h-8 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                  >
                    {{ item.product_name?.charAt(0).toUpperCase() ?? "?" }}
                  </div>
                  <div>
                    <div class="font-bold text-sm text-slate-900">
                      {{ item.product_name }}
                    </div>
                    <div class="font-mono text-[10px] text-slate-400">
                      {{ item.product_code }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="px-5 py-3">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                >
                  {{ item.category_name || "—" }}
                </span>
              </td>

              <!-- Branch -->
              <td class="px-5 py-3 text-xs font-semibold text-slate-600">
                {{ item.branch_name || "—" }}
              </td>

              <!-- System Qty -->
              <td class="px-5 py-3 text-center">
                <span class="font-extrabold text-sm text-slate-700">{{
                  item.recorded_stock ?? "—"
                }}</span>
              </td>

              <!-- Counted Qty -->
              <td class="px-5 py-3 text-center">
                <input
                  type="number"
                  v-model.number="item.counted_stock"
                  min="0"
                  @change="onCountChange(item)"
                  class="w-20 text-center px-2 py-1.5 bg-white border border-[#D9EBF7] rounded-lg text-sm font-bold text-slate-900 outline-none focus:border-[#229DE7] focus:ring-2 focus:ring-[#229DE7]/15 transition"
                  placeholder="—"
                />
              </td>

              <!-- Variance -->
              <td class="px-5 py-3 text-center">
                <span
                  v-if="item.counted_stock !== null && item.counted_stock !== undefined"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                  :class="varianceBadge(item.variance)"
                >
                  {{ item.variance > 0 ? "+" : "" }}{{ item.variance }}
                </span>
                <span v-else class="text-slate-300 text-xs font-semibold">—</span>
              </td>

              <!-- Status -->
              <td class="px-5 py-3">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold border"
                  :class="itemStatusBadge(item)"
                >
                  {{ itemStatusLabel(item) }}
                </span>
              </td>

              <!-- Notes -->
              <td class="px-5 py-3">
                <input
                  type="text"
                  v-model="item.notes"
                  placeholder="Add note..."
                  @input="onNotesChange(item)"
                  class="w-32 px-2 py-1.5 bg-white border border-[#D9EBF7] rounded-lg text-xs text-slate-600 outline-none focus:border-[#229DE7] transition placeholder:text-slate-300"
                />
              </td>

              <!-- Save Status -->
              <td class="px-3 py-3 w-8">
                <span v-if="item.saveStatus === 'saving'" title="Saving...">
                  <svg
                    class="animate-spin w-4 h-4 text-[#229DE7]"
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
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8v8H4z"
                    />
                  </svg>
                </span>
                <span v-else-if="item.saveStatus === 'pending'" title="Pending...">
                  <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                </span>
                <span v-else-if="item.saveStatus === 'saved'" title="Saved">
                  <svg
                    class="w-4 h-4 text-emerald-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </span>
                <span
                  v-else-if="item.saveStatus === 'error'"
                  title="Save failed — click to retry"
                  @click="autoSave(item)"
                  class="cursor-pointer"
                >
                  <svg
                    class="w-4 h-4 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div
        v-if="pagination.total > 0"
        class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
      >
        <!-- Left: count + per-page selector -->
        <div class="flex items-center gap-3">
          <span class="text-sm text-slate-500 font-semibold">
            Showing {{ pagination.from }}–{{ pagination.to }} of
            {{ pagination.total }} items
          </span>
          <select
            v-model.number="pagination.per_page"
            @change="fetchItems(1)"
            class="px-2 py-1 bg-white border border-[#D9EBF7] rounded-lg text-xs text-slate-600 outline-none focus:border-[#229DE7] transition cursor-pointer"
          >
            <option :value="15">15 / page</option>
            <option :value="30">30 / page</option>
            <option :value="50">50 / page</option>
            <option :value="100">100 / page</option>
          </select>
        </div>

        <!-- Right: page controls -->
        <div class="flex items-center gap-1.5">
          <!-- First -->
          <button
            :disabled="pagination.current_page === 1 || itemsLoading"
            @click="fetchItems(1)"
            class="w-8 h-8 flex items-center justify-center border border-[#D9EBF7] rounded-lg bg-white text-slate-500 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition text-xs font-bold"
            title="First page"
          >
            «
          </button>

          <!-- Prev -->
          <button
            :disabled="!pagination.prev_page_url || itemsLoading"
            @click="fetchItems(pagination.current_page - 1)"
            class="px-3 h-8 flex items-center border border-[#D9EBF7] rounded-lg bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition"
          >
            ← Prev
          </button>

          <!-- Page pills -->
          <template v-for="page in visiblePages" :key="page">
            <span
              v-if="page === '...'"
              class="w-8 h-8 flex items-center justify-center text-slate-400 text-xs font-bold"
              >…</span
            >
            <button
              v-else
              @click="fetchItems(page)"
              :disabled="itemsLoading"
              class="w-8 h-8 flex items-center justify-center border rounded-lg text-sm font-bold transition"
              :class="
                page === pagination.current_page
                  ? 'bg-[#229DE7] border-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
                  : 'border-[#D9EBF7] bg-white text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD]'
              "
            >
              {{ page }}
            </button>
          </template>

          <!-- Next -->
          <button
            :disabled="!pagination.next_page_url || itemsLoading"
            @click="fetchItems(pagination.current_page + 1)"
            class="px-3 h-8 flex items-center border border-[#D9EBF7] rounded-lg bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition"
          >
            Next →
          </button>

          <!-- Last -->
          <button
            :disabled="pagination.current_page === pagination.last_page || itemsLoading"
            @click="fetchItems(pagination.last_page)"
            class="w-8 h-8 flex items-center justify-center border border-[#D9EBF7] rounded-lg bg-white text-slate-500 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition text-xs font-bold"
            title="Last page"
          >
            »
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import Swal from "sweetalert2";
import StockTakeService from "@/services/stockService";

const router = useRouter();
const route = useRoute();
const stockTakeId = computed(() => route.params.id);

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const itemsLoading = ref(false);
const session = ref(null);
const items = ref([]);
const selectedBranchId = ref(null); // Add branch selection state

/* ─── FILTERS ─────────────────────────── */
const filters = ref({
  search: "",
  branch_id: "",
  category_id: "",
  status: "",
});

/* ─── PAGINATION (mirrors Laravel paginator) ── */
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null,
});

/* ─── FILTER OPTION LISTS ─────────────── */
const sessionBranches = computed(() => session.value?.branches ?? []);
const sessionCategories = computed(() => session.value?.categories ?? []);

/* ─── PAGE PILLS ──────────────────────── */
const visiblePages = computed(() => {
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 4) return [1, 2, 3, 4, 5, "...", total];
  if (current >= total - 3)
    return [1, "...", total - 4, total - 3, total - 2, total - 1, total];
  return [1, "...", current - 1, current, current + 1, "...", total];
});

/* ─── FETCH ITEMS ─────────────────────── */
const fetchItems = async (page = 1) => {
  itemsLoading.value = true;
  try {
    const res = await StockTakeService.getItems(stockTakeId.value, {
      page,
      per_page: pagination.value.per_page,
      search: filters.value.search,
      branch_id: filters.value.branch_id,
      category_id: filters.value.category_id,
      status: filters.value.status,
    });

    const data = res.data;

    items.value = (data.data || []).map((i) => ({
      ...i,
      counted_stock: i.counted_stock ?? null,
      variance: computeVariance(i.recorded_stock, i.counted_stock),
      saveStatus: null,
      branch_id: i.branch_id, // Ensure branch_id is preserved
    }));

    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
      from: data.from ?? 0,
      to: data.to ?? 0,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url,
    };
  } catch {
    Swal.fire({
      icon: "error",
      title: "Load Failed",
      text: "Could not load stock take items.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    itemsLoading.value = false;
  }
};

/* ─── INIT ────────────────────────────── */
const initData = async () => {
  pageLoading.value = true;
  try {
    const sessionRes = await StockTakeService.getById(stockTakeId.value);
    session.value = sessionRes.data;
    await fetchItems(1);
  } catch {
    Swal.fire({
      icon: "error",
      title: "Load Failed",
      text: "Could not load stock take details.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    pageLoading.value = false;
  }
};

onMounted(initData);

/* ─── WATCH FILTERS — debounced re-fetch ─ */
let filterTimer = null;
watch(
  filters,
  () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => fetchItems(1), 400);
  },
  { deep: true }
);

/* ─── VARIANCE ────────────────────────── */
const computeVariance = (system, counted) => {
  if (counted === null || counted === undefined) return 0;
  return Number(counted) - Number(system ?? 0);
};

/* ─── AUTO-SAVE (debounced per item) ─── */
const saveTimers = {};

const autoSave = (item) => {
  // Validate that we have a branch_id
  if (!item.branch_id) {
    console.error("No branch_id found for item:", item);
    item.saveStatus = "error";
    Swal.fire({
      icon: "error",
      title: "Save Failed",
      text: "Branch information is missing for this item.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  item.saveStatus = "pending";
  if (saveTimers[item.id]) clearTimeout(saveTimers[item.id]);

  saveTimers[item.id] = setTimeout(async () => {
    item.saveStatus = "saving";
    try {
      await StockTakeService.saveCounts(stockTakeId.value, {
        items: [
          {
            item_id: item.id,
            counted_stock: item.counted_stock,
            notes: item.notes || "",
            branch_id: item.branch_id, // ✅ Pass branch_id here
          },
        ],
      });
      item.saveStatus = "saved";

      // Refresh session summary counts after save
      const sessionRes = await StockTakeService.getById(stockTakeId.value);
      session.value = sessionRes.data;

      setTimeout(() => {
        item.saveStatus = null;
      }, 2000);
    } catch (error) {
      console.error("Save error:", error);
      item.saveStatus = "error";
    }
  }, 800);
};

const onCountChange = (item) => {
  item.variance = computeVariance(item.recorded_stock, item.counted_stock);
  autoSave(item);
};

const onNotesChange = (item) => {
  autoSave(item);
};

/* ─── EXPORT (fetches all pages for current filters) ─── */
const exportCSV = async () => {
  try {
    const res = await StockTakeService.getItems(stockTakeId.value, {
      per_page: 99999,
      search: filters.value.search,
      branch_id: filters.value.branch_id,
      category_id: filters.value.category_id,
      status: filters.value.status,
    });
    const allItems = res.data?.data || [];
    const headers = [
      "Product",
      "Code",
      "Category",
      "Branch",
      "System Qty",
      "Counted Qty",
      "Variance",
      "Status",
      "Notes",
    ];
    const rows = allItems.map((i) => [
      i.product?.name || i.product_name,
      i.product?.product_code || i.product_code,
      i.product?.category?.name || i.category_name,
      i.branch?.name || i.branch_name,
      i.recorded_stock,
      i.counted_stock ?? "",
      computeVariance(i.recorded_stock, i.counted_stock),
      itemStatusLabel(i),
      i.notes || "",
    ]);
    const csv =
      "data:text/csv;charset=utf-8," +
      [headers, ...rows].map((r) => r.join(",")).join("\n");
    const link = Object.assign(document.createElement("a"), {
      href: encodeURI(csv),
      download: `stock_take_${stockTakeId.value}.csv`,
    });
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch {
    Swal.fire({ icon: "error", title: "Export Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── HELPERS ─────────────────────────── */
const formatDate = (d) =>
  d
    ? new Date(d).toLocaleDateString("en-KE", {
        year: "numeric",
        month: "short",
        day: "numeric",
      })
    : "—";

const statusBadge = (status) => {
  const s = (status || "draft").toLowerCase();
  if (s === "completed") return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (s === "in_progress" || s === "in progress")
    return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-amber-50 text-amber-600 border-amber-200";
};

const varianceBadge = (v) => {
  if (v === 0) return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (v > 0) return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-red-50 text-red-600 border-red-200";
};

const itemStatusLabel = (item) => {
  if (item.counted_stock === null || item.counted_stock === undefined) return "Uncounted";
  if (item.variance === 0) return "Match";
  return item.variance > 0 ? "Surplus" : "Shortage";
};

const itemStatusBadge = (item) => {
  if (item.counted_stock === null || item.counted_stock === undefined)
    return "bg-slate-50 text-slate-400 border-slate-200";
  if (item.variance === 0) return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (item.variance > 0) return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-red-50 text-red-600 border-red-200";
};
</script>

<style scoped></style>
