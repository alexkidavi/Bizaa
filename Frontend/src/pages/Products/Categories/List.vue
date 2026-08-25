<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Categories</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Configure your global category identities.
        </p>
      </div>

      <!-- Search -->
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
          placeholder="Search categories..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- ── FORM PANEL ── -->
      <aside class="lg:col-span-4">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>

          <div
            class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]"
          >
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              {{ editingId ? "Edit Category" : "Create Category" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="cursor-pointer text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>

          <form @submit.prevent="saveCategory" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Category Name</label>
              <input
                v-model="form.name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400 focus:border-red-500': vErrors.name }"
                placeholder="e.g. Electronics"
              />
              <p v-if="vErrors.name" class="field-error">Category name is required</p>
            </div>

            <div>
              <label class="field-label">
                Description
                <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="field-input resize-none"
                placeholder="Optional category details..."
              ></textarea>
            </div>

            <div>
              <label class="field-label required">Branches</label>
              <Multiselect
                v-model="form.branches"
                :options="branches"
                :multiple="true"
                track-by="id"
                label="name"
                placeholder="Select one or more..."
                :class="{ 'ms-invalid': vErrors.branches }"
              />
              <p v-if="vErrors.branches" class="field-error">
                Select at least one branch
              </p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60 mt-2"
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
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              {{
                loading
                  ? "Processing..."
                  : editingId
                  ? "Update Category"
                  : "Save Category"
              }}
            </button>
          </form>
        </div>
      </aside>

      <!-- ── TABLE PANEL ── -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <!-- Loading skeleton -->
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-12 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <!-- Empty state -->
          <div v-else-if="filteredCategories.length === 0" class="py-20 text-center">
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
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No categories found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Adjust your search or create a new category.
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
                  <th class="px-5 py-3.5">Category</th>
                  <th class="px-5 py-3.5">Description</th>
                  <th class="px-5 py-3.5">Branches</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(category, index) in paginatedCategories"
                  :key="category.id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-3.5 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </td>

                  <!-- Category name + avatar -->
                  <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                      >
                        {{ category.name.substring(0, 2).toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-bold text-sm text-slate-900">
                          {{ category.name }}
                        </div>
                        <div class="text-[10.5px] text-slate-400 font-semibold">
                          By {{ category.user?.name || "Admin" }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Description -->
                  <td
                    class="px-5 py-3.5 text-xs text-slate-500 max-w-[160px] truncate italic"
                  >
                    {{ category.description || "No description" }}
                  </td>

                  <!-- Branches -->
                  <td class="px-5 py-3.5">
                    <div class="flex flex-wrap gap-1.5">
                      <span
                        v-for="b in category.branches"
                        :key="b.id"
                        class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10.5px] font-bold"
                      >
                        {{ b.name }}
                      </span>
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="px-5 py-3.5 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="editCategory(category)"
                        class="cursor-pointer w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] border border-transparent hover:bg-[#EAF5FD] hover:border-[#B3DEFA] transition"
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
                      </button>
                      <button
                        @click="deleteCategory(category.id)"
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
            v-if="filteredCategories.length > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
          >
            <div class="text-sm text-slate-500 font-semibold">
              Showing {{ (currentPage - 1) * itemsPerPage + 1 }}–{{
                Math.min(currentPage * itemsPerPage, filteredCategories.length)
              }}
              of {{ filteredCategories.length }} categories
            </div>
            <div class="flex items-center gap-2">
              <button
                :disabled="currentPage === 1"
                @click="currentPage--"
                class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 disabled:hover:bg-white disabled:hover:border-[#D9EBF7] disabled:hover:text-slate-600 transition shadow-sm active:scale-95"
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
                class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 disabled:hover:bg-white disabled:hover:border-[#D9EBF7] disabled:hover:text-slate-600 transition shadow-sm active:scale-95"
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
import { ref, reactive, computed, watch, onMounted } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import CategoryService from "@/services/categoryService";
import BranchesService from "@/services/Branchesservice";

/* ─── STATE ───────────────────────────── */
const tableLoading = ref(false);
const loading = ref(false);
const editingId = ref(null);

const categories = ref([]);
const branches = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = 8;

const form = reactive({ name: "", description: "", branches: [] });
const vErrors = reactive({ name: false, branches: false });

/* ─── COMPUTED ────────────────────────── */
const filteredCategories = computed(() =>
  categories.value.filter(
    (c) =>
      c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (c.description &&
        c.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredCategories.value.length / itemsPerPage))
);

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredCategories.value.slice(start, start + itemsPerPage);
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

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
const initData = async () => {
  tableLoading.value = true;
  try {
    const [catRes, branchRes] = await Promise.all([
      CategoryService.getAll(),
      BranchesService.getAllBranches(),
    ]);
    categories.value = catRes.data || catRes;
    branches.value = branchRes.data || branchRes;
  } catch {
    Swal.fire("Error", "Could not load data. Please refresh.", "error");
  } finally {
    tableLoading.value = false;
  }
};

onMounted(initData);

/* ─── VALIDATE ────────────────────────── */
const validate = () => {
  vErrors.name = !form.name.trim();
  vErrors.branches = form.branches.length === 0;
  return !vErrors.name && !vErrors.branches;
};

/* ─── SAVE ────────────────────────────── */
const saveCategory = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = {
      name: form.name,
      description: form.description,
      branch_ids: form.branches.map((b) => b.id),
    };
    if (editingId.value) {
      await CategoryService.update(editingId.value, payload);
      notify("success", "Category Updated");
    } else {
      await CategoryService.create(payload);
      notify("success", "Category Created");
    }
    resetForm();
    tableLoading.value = true;
    const res = await CategoryService.getAll();
    categories.value = res.data || res;
  } catch {
    Swal.fire({
      icon: "error",
      title: "Save Failed",
      text: "An error occurred while saving.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
    tableLoading.value = false;
  }
};

/* ─── EDIT ────────────────────────────── */
const editCategory = (category) => {
  editingId.value = category.id;
  form.name = category.name;
  form.description = category.description || "";
  form.branches = category.branches ? [...category.branches] : [];
  window.scrollTo({ top: 0, behavior: "smooth" });
};

/* ─── DELETE ──────────────────────────── */
const deleteCategory = async (id) => {
  const result = await Swal.fire({
    title: "Delete this category?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
    cancelButtonText: "Cancel",
  });
  if (!result.isConfirmed) return;
  try {
    await CategoryService.delete(id);
    categories.value = categories.value.filter((c) => c.id !== id);
    notify("success", "Category Deleted");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── RESET ───────────────────────────── */
const resetForm = () => {
  form.name = "";
  form.description = "";
  form.branches = [];
  editingId.value = null;
  vErrors.name = false;
  vErrors.branches = false;
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
