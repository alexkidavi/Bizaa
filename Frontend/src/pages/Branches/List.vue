<template>

  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Branches Management
        </h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your business locations and branch offices.
        </p>
      </div>
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
          placeholder="Search branches..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM PANEL -->
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
              {{ editingBranch ? "Edit Branch" : "Add New Branch" }}
            </h2>
            <button
              v-if="editingBranch"
              @click="cancelEdit"
              class="cursor-pointer text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>

          <form @submit.prevent="submitBranch" class="p-6 space-y-4">
            <!-- Branch Name -->
            <div>
              <label class="field-label required">Branch Name</label>
              <input
                v-model="branchName"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': nameError }"
                placeholder="e.g. Westlands Branch"
              />
              <p v-if="nameError" class="field-error">Branch name is required</p>
            </div>

            <!-- Location -->
            <div>
              <label class="field-label">Location</label>
              <div class="relative">
                <svg
                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <input
                  v-model="branchLocation"
                  type="text"
                  class="field-input pl-10"
                  placeholder="e.g. Nairobi, Kenya"
                />
              </div>
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
              <svg
                v-else
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              {{
                loading
                  ? editingBranch
                    ? "Updating..."
                    : "Adding..."
                  : editingBranch
                  ? "Update Branch"
                  : "Add Branch"
              }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE PANEL -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <!-- Skeleton -->
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-16 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <!-- Empty -->
          <div v-else-if="filteredBranches.length === 0" class="py-20 text-center">
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
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No branches found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Add your first branch location to get started.
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
                  <th class="px-5 py-3.5">Branch Name</th>
                  <th class="px-5 py-3.5">Location</th>
                  <th class="px-5 py-3.5">Added By</th>
                  <th class="px-5 py-3.5">Date Added</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(branch, index) in paginatedBranches"
                  :key="branch.id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * pageSize + index + 1 }}
                  </td>

                  <!-- Branch name + icon -->
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform"
                      >
                        <svg
                          class="w-4 h-4 text-[#229DE7]"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                          />
                        </svg>
                      </div>
                      <span class="font-bold text-sm text-slate-900">{{
                        branch.name
                      }}</span>
                    </div>
                  </td>

                  <!-- Location -->
                  <td class="px-5 py-4">
                    <div
                      class="flex items-center gap-1.5 text-xs text-slate-600 font-semibold"
                    >
                      <svg
                        class="w-3.5 h-3.5 text-slate-400 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                      </svg>
                      {{ branch.location || "—" }}
                    </div>
                  </td>

                  <!-- Added by -->
                  <td class="px-5 py-4 text-xs text-slate-500">
                    {{ branch.user?.name || "System" }}
                  </td>

                  <!-- Date -->
                  <td
                    class="px-5 py-4 text-xs text-slate-500 font-semibold whitespace-nowrap"
                  >
                    {{ formatDate(branch.created_at) }}
                  </td>

                  <!-- Actions -->
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="editBranch(branch)"
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
                      </button>
                      <button
                        @click="deleteBranch(branch.id)"
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
            v-if="filteredBranches.length > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
          >
            <div class="text-sm text-slate-500 font-semibold">
              Showing {{ (currentPage - 1) * pageSize + 1 }}–{{
                Math.min(currentPage * pageSize, filteredBranches.length)
              }}
              of {{ filteredBranches.length }} branches
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
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import BranchesService from "@/services/Branchesservice";
import Swal from "sweetalert2";

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const tableLoading = ref(false);
const loading = ref(false);

const branchName = ref("");
const branchLocation = ref("");
const editingBranch = ref(null);
const nameError = ref(false);

const branches = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const pageSize = 10;

/* ─── HELPERS ─────────────────────────── */
const formatDate = (val) =>
  val
    ? new Date(val).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : "—";

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
const loadBranches = async () => {
  tableLoading.value = true;
  try {
    const data = await BranchesService.getAllBranches();
    branches.value = data?.data?.data ?? data?.data ?? data ?? [];
  } catch {
    Swal.fire("Error", "Failed to load branches.", "error");
  } finally {
    tableLoading.value = false;
  }
};

onMounted(async () => {
  pageLoading.value = true;
  await loadBranches();
  setTimeout(() => (pageLoading.value = false), 500);
});

/* ─── COMPUTED ────────────────────────── */
const filteredBranches = computed(() => {
  if (!searchQuery.value) return branches.value;
  return branches.value.filter(
    (b) =>
      b.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (b.location || "").toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredBranches.value.length / pageSize))
);

const paginatedBranches = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredBranches.value.slice(start, start + pageSize);
});

watch(searchQuery, () => (currentPage.value = 1));

/* ─── SUBMIT ──────────────────────────── */
const submitBranch = async () => {
  nameError.value = !branchName.value.trim();
  if (nameError.value) return;

  loading.value = true;
  try {
    const payload = { name: branchName.value, location: branchLocation.value };

    if (editingBranch.value) {
      await BranchesService.updateBranch(editingBranch.value.id, payload);
      Swal.fire({
        icon: "success",
        title: "Branch updated successfully.",
        confirmButtonColor: "#229DE7",
      });
      editingBranch.value = null;
    } else {
      await BranchesService.createBranch(payload);
      Swal.fire({
        icon: "success",
        title: "Branch added successfully.",
        confirmButtonColor: "#229DE7",
      });
    }

    branchName.value = "";
    branchLocation.value = "";
    await loadBranches();
  } catch (err) {
    const msg = err?.response?.data?.message || "Operation failed.";
    Swal.fire({
      icon: "error",
      title: "Error",
      text: msg,
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
  }
};

/* ─── EDIT ────────────────────────────── */
const editBranch = (branch) => {
  editingBranch.value = branch;
  branchName.value = branch.name;
  branchLocation.value = branch.location || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const cancelEdit = () => {
  editingBranch.value = null;
  branchName.value = "";
  branchLocation.value = "";
  nameError.value = false;
};

/* ─── DELETE ──────────────────────────── */
const deleteBranch = async (id) => {
  const result = await Swal.fire({
    title: "Remove this branch?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, remove",
  });
  if (!result.isConfirmed) return;
  try {
    await BranchesService.deleteBranch(id);
    branches.value = branches.value.filter((b) => b.id !== id);
    notify("success", "Branch Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
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
