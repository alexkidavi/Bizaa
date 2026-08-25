<template>
  <div class="p-4 md:p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Stock Take</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage and record stock taking sessions · {{ todayDate }}
        </p>
      </div>
      <div class="relative w-full md:w-72">
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
          placeholder="Search stock takes..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- ── FORM PANEL ── -->
      <aside class="lg:col-span-4 relative z-10">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm lg:sticky lg:top-6 lg:max-h-[calc(100vh-5rem)] lg:overflow-y-auto"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>

          <div
            class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]"
          >
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
              {{ editingId ? "Edit Stock Take" : "New Stock Take" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="cursor-pointer text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="field-label required">Stock Take Name</label>
              <input
                v-model="form.name"
                class="field-input"
                :class="{ 'border-red-400 focus:border-red-500 bg-red-50': vErrors.name }"
                placeholder="e.g. Month-End Stock Take"
              />
              <p v-if="vErrors.name" class="field-error">Name is required</p>
            </div>

            <div>
              <label class="field-label required">Branches</label>
              <Multiselect
                v-model="form.branches"
                :options="branches"
                :multiple="true"
                track-by="id"
                label="name"
                placeholder="Select branches..."
                :class="{ 'ms-invalid': vErrors.branches }"
              />
              <p v-if="vErrors.branches" class="field-error">
                Select at least one branch
              </p>
            </div>

            <div>
              <label class="field-label">
                Categories
                <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
              </label>
              <Multiselect
                v-model="form.categories"
                :options="categories"
                :multiple="true"
                track-by="id"
                label="name"
                placeholder="All categories..."
              />
            </div>

            <div>
              <label class="field-label">
                Notes
                <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="field-input resize-none"
                placeholder="Optional remarks..."
              />
            </div>

            <!-- SUBMIT BUTTON — always visible, outside any overflow-hidden wrapper -->
            <div class="pt-2">
              <button
                type="button"
                :disabled="loading"
                @click="saveStockTake"
                class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <svg
                  v-if="loading"
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
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"
                  />
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
                    d="M12 4v16m8-8H4"
                    v-if="!editingId"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 13l4 4L19 7"
                    v-else
                  />
                </svg>
                {{
                  loading
                    ? "Processing..."
                    : editingId
                    ? "Update Stock Take"
                    : "Create Stock Take"
                }}
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- ── TABLE PANEL ── -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <!-- Skeleton -->
          <div v-if="pageLoading || tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-14 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <!-- Empty -->
          <div v-else-if="stockTakes.length === 0" class="py-20 text-center">
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
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No stock takes found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Create your first stock take session using the form.
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
                  <th class="px-5 py-3.5">Stock Take</th>
                  <th class="px-5 py-3.5">Reference</th>
                  <th class="px-5 py-3.5">Branches</th>
                  <th class="px-5 py-3.5">Created</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(item, index) in stockTakes"
                  :key="item.id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-3.5 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * perPage + index + 1 }}
                  </td>

                  <!-- Name + description -->
                  <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0 group-hover:scale-105 transition-transform"
                      >
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
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"
                          />
                        </svg>
                      </div>
                      <div>
                        <div class="font-bold text-sm text-slate-900">
                          {{ item.name }}
                        </div>
                        <div
                          class="text-[10.5px] text-slate-400 font-semibold mt-0.5 truncate max-w-[180px]"
                        >
                          {{ item.description || "No notes" }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Reference -->
                  <td class="px-5 py-3.5">
                    <span class="font-mono text-xs text-[#229DE7] font-bold">
                      {{ item.reference || "—" }}
                    </span>
                  </td>

                  <!-- Branches -->
                  <td class="px-5 py-3.5">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="b in (item.branches || []).slice(0, 2)"
                        :key="b.id"
                        class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10px] font-bold"
                      >
                        {{ b.name }}
                      </span>
                      <span
                        v-if="(item.branches || []).length > 2"
                        class="px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded-full text-[10px] font-bold"
                      >
                        +{{ item.branches.length - 2 }}
                      </span>
                    </div>
                  </td>

                  <!-- Date -->
                  <td class="px-5 py-3.5 text-xs font-semibold text-slate-500">
                    {{ formatDate(item.created_at) }}
                  </td>

                  <!-- Actions -->
                  <td class="px-5 py-3.5 text-right">
                    <div class="flex justify-end gap-2">
                      <!-- View -->
                      <button
                        @click="viewStockTake(item)"
                        class="cursor-pointer w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] border border-transparent hover:bg-[#EAF5FD] hover:border-[#B3DEFA] transition"
                        title="View Items"
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
                      </button>

                      <!-- Delete -->
                      <button
                        @click="deleteStockTake(item.id)"
                        class="cursor-pointer w-8 h-8 rounded-lg flex items-center justify-center text-red-500 border border-transparent hover:bg-red-50 hover:border-red-200 transition"
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
            v-if="total > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
          >
            <div class="text-sm text-slate-500 font-semibold">
              Showing {{ (currentPage - 1) * perPage + 1 }}–{{
                Math.min(currentPage * perPage, total)
              }}
              of {{ total }} sessions
            </div>
            <div class="flex items-center gap-2">
              <button
                :disabled="currentPage === 1"
                @click="currentPage--"
                class="cursor-pointer px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
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
                class="cursor-pointer px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import StockTakeService from "@/services/stockService";
import BranchesService from "@/services/Branchesservice";
import CategoryService from "@/services/categoryService";

const router = useRouter();

/* ─── DATE ────────────────────────────── */
const todayDate = new Date().toLocaleDateString("en-KE", {
  year: "numeric",
  month: "long",
  day: "numeric",
});

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const tableLoading = ref(false);
const loading = ref(false);
const editingId = ref(null);

const stockTakes = ref([]);
const branches = ref([]);
const categories = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const totalPages = ref(1);
const total = ref(0);
const perPage = 10;

/* ─── FORM ────────────────────────────── */
const defaultForm = () => ({
  name: `Stock Take - ${new Date().toLocaleDateString("en-KE")}`,
  description: "",
  branches: [],
  categories: [],
});

const form = reactive(defaultForm());
const vErrors = reactive({ name: false, branches: false });

/* ─── FETCH STOCK TAKES ───────────────── */
let searchTimer = null;

const fetchStockTakes = async (resetPage = false) => {
  if (resetPage) currentPage.value = 1;
  tableLoading.value = true;
  try {
    const res = await StockTakeService.getAll({
      page: currentPage.value,
      per_page: perPage,
      search: searchQuery.value || undefined,
    });
    const d = res.data;
    stockTakes.value = d.data;
    totalPages.value = d.last_page;
    total.value = d.total;
  } catch {
    Swal.fire({
      icon: "error",
      title: "Load Failed",
      text: "Could not load stock takes. Please refresh.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    tableLoading.value = false;
  }
};

watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchStockTakes(true), 350);
});

watch(currentPage, () => fetchStockTakes());

/* ─── INIT ────────────────────────────── */
const initData = async () => {
  pageLoading.value = true;
  try {
    const [st, ct, br] = await Promise.all([
      StockTakeService.getAll({ page: 1, per_page: perPage }),
      CategoryService.getAll(),
      BranchesService.getAllBranches(),
    ]);
    const d = st.data;
    stockTakes.value = d.data;
    totalPages.value = d.last_page;
    total.value = d.total;
    categories.value = ct.data?.data || ct.data || ct;
    branches.value = br.data?.data || br.data || br;
  } catch {
    Swal.fire({
      icon: "error",
      title: "Load Failed",
      text: "Could not load data. Please refresh.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    pageLoading.value = false;
  }
};

onMounted(initData);

/* ─── VALIDATE ────────────────────────── */
const validate = () => {
  vErrors.name = !form.name.trim();
  vErrors.branches = !form.branches.length;
  return !vErrors.name && !vErrors.branches;
};

/* ─── SAVE ────────────────────────────── */
const saveStockTake = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = {
      name: form.name,
      description: form.description,
      branch_ids: form.branches.map((b) => b.id),
      category_ids: form.categories.map((c) => c.id),
    };
    let res;
    if (editingId.value) {
      res = await StockTakeService.update(editingId.value, payload);
    } else {
      res = await StockTakeService.create(payload);
    }
    Swal.fire({
      icon: "success",
      title: editingId.value ? "Updated!" : "Created!",
      text: res.data?.message || "Stock take saved.",
      timer: 2000,
      showConfirmButton: false,
    });
    await fetchStockTakes(true);
    resetForm();
  } catch (err) {
    console.error("Save error:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err.response?.data?.message || err.message || "Something went wrong.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
  }
};

/* ─── VIEW ────────────────────────────── */
const viewStockTake = (item) => {
  router.push(`/stock/take/${item.id}`);
};

/* ─── DELETE ──────────────────────────── */
const deleteStockTake = async (id) => {
  const result = await Swal.fire({
    title: "Delete this stock take?",
    text: "This will remove the session and all recorded items.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  try {
    await StockTakeService.delete(id);
    await fetchStockTakes();
    Swal.fire({
      icon: "success",
      title: "Deleted",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
    });
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── RESET ───────────────────────────── */
const resetForm = () => {
  Object.assign(form, defaultForm());
  editingId.value = null;
  vErrors.name = false;
  vErrors.branches = false;
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
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.field-label.required::after {
  content: " *";
  color: #ef4444;
}
.field-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
</style>

<style>
.multiselect__tags {
  background: #f5faff !important;
  border: 1.5px solid #d9ebf7 !important;
  border-radius: 12px !important;
  padding: 6px 14px !important;
  min-height: 42px !important;
  font-size: 13.5px !important;
}
.ms-invalid .multiselect__tags {
  border-color: #f87171 !important;
  background: #fff1f2 !important;
}
.multiselect__tags:focus-within {
  border-color: #229de7 !important;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12) !important;
}
.multiselect__tag {
  background: #229de7 !important;
  border-radius: 8px !important;
  font-size: 12px !important;
  font-weight: 600 !important;
}
.multiselect__tag-icon::after {
  color: #fff !important;
}
.multiselect__tag-icon:hover {
  background: #1682c4 !important;
}
.multiselect__option--highlight {
  background: #229de7 !important;
}
.multiselect__option--selected {
  background: #eaf5fd !important;
  color: #1682c4 !important;
  font-weight: 700 !important;
}
.multiselect__placeholder {
  color: #94a3b8 !important;
  font-size: 13.5px !important;
}
.multiselect__input {
  background: transparent !important;
  font-size: 13.5px !important;
}
</style>
