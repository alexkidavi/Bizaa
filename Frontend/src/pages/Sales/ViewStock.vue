<template>

  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK -->
    <div class="mb-5">
      <button
        @click="router.back()"
        class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#229DE7] transition mb-5 group"
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
            <!-- <button
              @click="submitStockTake"
              :disabled="submitting"
              class="flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-md transition-all active:scale-95 disabled:opacity-60"
            >
              <svg
                v-if="submitting"
                class="animate-spin h-4 w-4 text-white"
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
                stroke-width="2.2"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              {{ submitting ? "Submitting..." : "Submit Stock Take" }}
            </button> -->
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
            <div class="font-extrabold text-slate-900 text-lg">{{ items.length }}</div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Counted
            </div>
            <div class="font-extrabold text-emerald-600 text-lg">{{ countedItems }}</div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Variances
            </div>
            <div class="font-extrabold text-amber-600 text-lg">{{ varianceItems }}</div>
          </div>
          <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
            <div
              class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
            >
              Uncounted
            </div>
            <div class="font-extrabold text-slate-400 text-lg">{{ uncountedItems }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
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
          v-model="searchQuery"
          type="text"
          placeholder="Search by product name or code..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
      <select
        v-model="branchFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Branches</option>
        <option v-for="b in sessionBranches" :key="b.id" :value="b.id">
          {{ b.name }}
        </option>
      </select>
      <select
        v-model="categoryFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Categories</option>
        <option v-for="c in sessionCategories" :key="c.id" :value="c.id">
          {{ c.name }}
        </option>
      </select>
      <select
        v-model="countFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Items</option>
        <option value="counted">Counted</option>
        <option value="uncounted">Uncounted</option>
        <option value="variance">With Variance</option>
      </select>
    </div>

    <!-- TABLE -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div v-if="itemsLoading" class="p-6 space-y-3">
        <div
          v-for="n in 8"
          :key="n"
          class="h-14 bg-slate-50 animate-pulse rounded-xl"
        ></div>
      </div>

      <div v-else-if="filteredItems.length === 0" class="py-20 text-center">
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
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr
              v-for="(item, index) in paginatedItems"
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
                {{ (itemPage - 1) * itemPerPage + index + 1 }}
              </td>

              <!-- Product -->
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div
                    class="w-8 h-8 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                  >
                    {{ item.product?.name?.charAt(0).toUpperCase() ?? "?" }}
                  </div>
                  <div>
                    <div class="font-bold text-sm text-slate-900">
                      {{ item.product?.name }}
                    </div>
                    <div class="font-mono text-[10px] text-slate-400">
                      {{ item.product?.product_code }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="px-5 py-3">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                >
                  {{ item.product?.category?.name || "—" }}
                </span>
              </td>

              <!-- Branch -->
              <td class="px-5 py-3 text-xs font-semibold text-slate-600">
                {{ item.branch?.name || "—" }}
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

              <!--
                Variance = counted - system
                  > 0  surplus  (have more than expected) → blue
                  = 0  match                              → green
                  < 0  shortage (have less than expected) → red
              -->
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
                  class="w-32 px-2 py-1.5 bg-white border border-[#D9EBF7] rounded-lg text-xs text-slate-600 outline-none focus:border-[#229DE7] transition placeholder:text-slate-300"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div
        v-if="filteredItems.length > 0"
        class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
      >
        <div class="text-sm text-slate-500 font-semibold">
          Showing {{ (itemPage - 1) * itemPerPage + 1 }}–{{
            Math.min(itemPage * itemPerPage, filteredItems.length)
          }}
          of {{ filteredItems.length }} items
        </div>
        <div class="flex items-center gap-2">
          <button
            :disabled="itemPage === 1"
            @click="itemPage--"
            class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
          >
            ← Previous
          </button>
          <div
            class="flex items-center px-4 py-2 bg-[#229DE7] rounded-xl text-sm font-bold text-white shadow-md shadow-[#229DE7]/30"
          >
            {{ itemPage }} / {{ itemTotalPages }}
          </div>
          <button
            :disabled="itemPage >= itemTotalPages"
            @click="itemPage++"
            class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
          >
            Next →
          </button>
        </div>
      </div>

      <!-- SAVE FOOTER -->
      <div
        v-if="hasUnsavedChanges"
        class="px-6 py-4 bg-[#EAF5FD] border-t border-[#B3DEFA] flex items-center justify-between gap-4"
      >
        <div class="flex items-center gap-2 text-sm font-bold text-[#1682C4]">
          <div class="w-2 h-2 rounded-full bg-[#229DE7] animate-pulse"></div>
          You have unsaved counts. Save them to record progress.
        </div>
        <button
          @click="saveCounts"
          :disabled="saving"
          class="flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95 disabled:opacity-60"
        >
          <svg
            v-if="saving"
            class="animate-spin h-4 w-4 text-white"
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
          {{ saving ? "Saving..." : "Save Counts" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import Swal from "sweetalert2";
import StockTakeService from "@/services/StockService";

const router = useRouter();
const route = useRoute();
const stockTakeId = computed(() => route.params.id);

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const itemsLoading = ref(false);
const submitting = ref(false);
const saving = ref(false);
const hasUnsavedChanges = ref(false);

const session = ref(null);
const items = ref([]);

const searchQuery = ref("");
const branchFilter = ref("");
const categoryFilter = ref("");
const countFilter = ref("");
const itemPage = ref(1);
const itemPerPage = 15;

/* ─── FILTER LISTS ────────────────────── */
// Derive unique branches directly from items — session.branches may be empty
const sessionBranches = computed(() => {
  const seen = new Set();
  return items.value
    .filter((i) => {
      const id = i.branch?.id;
      return id !== undefined && id !== null && !seen.has(id) && seen.add(id);
    })
    .map((i) => ({ id: i.branch.id, name: i.branch.name }));
});

const sessionCategories = computed(() => {
  const seen = new Set();
  return items.value
    .filter((i) => {
      const id = i.product?.category?.id;
      return id && !seen.has(id) && seen.add(id);
    })
    .map((i) => ({ id: i.product.category.id, name: i.product.category.name }));
});

/* ─── SUMMARY ─────────────────────────── */
const countedItems = computed(
  () =>
    items.value.filter((i) => i.counted_stock !== null && i.counted_stock !== undefined)
      .length
);
const varianceItems = computed(
  () =>
    items.value.filter(
      (i) => i.counted_stock !== null && i.counted_stock !== undefined && i.variance !== 0
    ).length
);
const uncountedItems = computed(
  () =>
    items.value.filter((i) => i.counted_stock === null || i.counted_stock === undefined)
      .length
);

/* ─── FILTERED + PAGINATED ────────────── */
const filteredItems = computed(() => {
  let list = items.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(
      (i) =>
        (i.product?.name || "").toLowerCase().includes(q) ||
        (i.product?.product_code || "").toLowerCase().includes(q)
    );
  }
  if (branchFilter.value)
    list = list.filter((i) => String(i.branch?.id) === String(branchFilter.value));
  if (categoryFilter.value)
    list = list.filter(
      (i) => String(i.product?.category?.id) === String(categoryFilter.value)
    );
  if (countFilter.value === "counted")
    list = list.filter((i) => i.counted_stock !== null && i.counted_stock !== undefined);
  if (countFilter.value === "uncounted")
    list = list.filter((i) => i.counted_stock === null || i.counted_stock === undefined);
  if (countFilter.value === "variance")
    list = list.filter(
      (i) => i.counted_stock !== null && i.counted_stock !== undefined && i.variance !== 0
    );
  return list;
});

const itemTotalPages = computed(() =>
  Math.max(1, Math.ceil(filteredItems.value.length / itemPerPage))
);
const paginatedItems = computed(() => {
  const start = (itemPage.value - 1) * itemPerPage;
  return filteredItems.value.slice(start, start + itemPerPage);
});

watch([searchQuery, branchFilter, categoryFilter, countFilter], () => {
  itemPage.value = 1;
});

/* ─── INIT ────────────────────────────── */
const initData = async () => {
  pageLoading.value = true;
  try {
    const [sessionRes, itemsRes] = await Promise.all([
      StockTakeService.getById(stockTakeId.value),
      StockTakeService.getItems(stockTakeId.value),
    ]);
    session.value = sessionRes.data;
    items.value = (itemsRes.data?.data || itemsRes.data || itemsRes).map((i) => ({
      ...i,
      counted_stock: i.counted_stock ?? null,
      variance: computeVariance(i.recorded_stock, i.counted_stock),
    }));
  } catch {
    Swal.fire({
      icon: "error",
      title: "Load Failed",
      text: "Could not load stock take details.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    setTimeout(() => {
      pageLoading.value = false;
    }, 400);
  }
};

onMounted(initData);

/* ─── VARIANCE ────────────────────────────────────────────────
   variance = counted − system
     > 0  → surplus  (physically have MORE than system shows)
     = 0  → match
     < 0  → shortage (physically have LESS than system shows)
──────────────────────────────────────────────────────────── */
const computeVariance = (system, counted) => {
  if (counted === null || counted === undefined) return 0;
  return Number(counted) - Number(system ?? 0);
};

const onCountChange = (item) => {
  item.variance = computeVariance(item.recorded_stock, item.counted_stock);
  hasUnsavedChanges.value = true;
};

/* ─── SAVE ────────────────────────────── */
const saveCounts = async () => {
  saving.value = true;
  try {
    const payload = items.value
      .filter((i) => i.counted_stock !== null && i.counted_stock !== undefined)
      .map((i) => ({
        item_id: i.id,
        counted_stock: i.counted_stock,
        notes: i.notes || "",
      }));
    await StockTakeService.saveCounts(stockTakeId.value, { items: payload });
    hasUnsavedChanges.value = false;
    Swal.fire({
      icon: "success",
      title: "Counts Saved",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
    });
  } catch {
    Swal.fire({ icon: "error", title: "Save Failed", confirmButtonColor: "#229DE7" });
  } finally {
    saving.value = false;
  }
};

/* ─── SUBMIT ──────────────────────────── */
// const submitStockTake = async () => {
//   if (uncountedItems.value > 0) {
//     const result = await Swal.fire({
//       icon: "warning",
//       title: `${uncountedItems.value} items uncounted`,
//       text: "Uncounted items will keep their system quantities. Submit anyway?",
//       showCancelButton: true,
//       confirmButtonColor: "#229DE7",
//       cancelButtonColor: "#f43f5e",
//       confirmButtonText: "Submit Anyway",
//     });
//     if (!result.isConfirmed) return;
//   }
//   submitting.value = true;
//   try {
//     await StockTakeService.submit(stockTakeId.value);
//     session.value.status = "Completed";
//     Swal.fire({
//       icon: "success",
//       title: "Stock Take Submitted!",
//       text: "Stock levels have been updated.",
//       confirmButtonColor: "#229DE7",
//     });
//   } catch {
//     Swal.fire({ icon: "error", title: "Submit Failed", confirmButtonColor: "#229DE7" });
//   } finally {
//     submitting.value = false;
//   }
// };

/* ─── EXPORT ──────────────────────────── */
const exportCSV = () => {
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
  const rows = items.value.map((i) => [
    i.product?.name,
    i.product?.product_code,
    i.product?.category?.name,
    i.branch?.name,
    i.recorded_stock,
    i.counted_stock ?? "",
    i.variance,
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

// Session-level status badge
const statusBadge = (status) => {
  const s = (status || "draft").toLowerCase();
  if (s === "completed") return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (s === "in_progress" || s === "in progress")
    return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-amber-50 text-amber-600 border-amber-200";
};

// Variance cell badge  (+surplus → blue | 0 match → green | -shortage → red)
const varianceBadge = (v) => {
  if (v === 0) return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (v > 0) return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-red-50 text-red-600 border-red-200";
};

// Row status label
const itemStatusLabel = (item) => {
  if (item.counted_stock === null || item.counted_stock === undefined) return "Uncounted";
  if (item.variance === 0) return "Match";
  return item.variance > 0 ? "Surplus" : "Shortage";
};

// Row status badge  (matches varianceBadge logic + grey for uncounted)
const itemStatusBadge = (item) => {
  if (item.counted_stock === null || item.counted_stock === undefined)
    return "bg-slate-50 text-slate-400 border-slate-200";
  if (item.variance === 0) return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (item.variance > 0) return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
  return "bg-red-50 text-red-600 border-red-200";
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
