<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Suppliers</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage Supplier profiles and branch assignments.
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
          placeholder="Search Suppliers..."
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
              {{ editingId ? "Edit Supplier" : "Add Supplier" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>

          <form @submit.prevent="saveSupplier" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': vErrors.name }"
                placeholder="e.g. Jane Doe"
              />
              <p v-if="vErrors.name" class="field-error">Full name is required</p>
            </div>
            <div>
              <label class="field-label required">Company Name</label>
              <input
                v-model="form.company_name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': vErrors.company_name }"
                placeholder="e.g. Brimsoft Suppliers"
              />
              <p v-if="vErrors.company_name" class="field-error">
                Company name is required
              </p>
            </div>
            <div>
              <label class="field-label required">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                class="field-input"
                :class="{ 'border-red-400': vErrors.email }"
                placeholder="e.g. jane@example.com"
              />
              <p v-if="vErrors.email" class="field-error">Valid email is required</p>
            </div>
            <div>
              <label class="field-label required">Phone Number</label>
              <input
                v-model="form.phone"
                type="tel"
                class="field-input"
                :class="{ 'border-red-400': vErrors.phone }"
                placeholder="e.g. +254 712 345 678"
              />
              <p v-if="vErrors.phone" class="field-error">Phone number is required</p>
            </div>
            <div>
              <label class="field-label required">National ID</label>
              <input
                v-model="form.national_id"
                type="number"
                class="field-input"
                :class="{ 'border-red-400': vErrors.national_id }"
                placeholder="e.g. 42324242"
              />
              <p v-if="vErrors.national_id" class="field-error">
                National ID is required
              </p>
            </div>
            <div>
              <label class="field-label">Tax ID</label>
              <input
                v-model="form.tax_id"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': vErrors.tax_id }"
                placeholder="e.g. PIN34564"
              />
              <p v-if="vErrors.tax_id" class="field-error">Tax ID is required</p>
            </div>
            <div>
              <label class="field-label required">Status</label>
              <select
                v-model="form.status"
                class="field-input"
                :class="{ 'border-red-400': vErrors.status }"
              >
                <option value="" disabled>Select status...</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
              <p v-if="vErrors.status" class="field-error">Status is required</p>
            </div>
            <div>
              <label class="field-label">
                Notes
                <span class="text-slate-400 font-normal text-[11px]">(Optional)</span>
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="field-input resize-none"
                placeholder="Additional Supplier notes..."
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
              class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60 mt-2"
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
                  ? "Update Supplier"
                  : "Save Supplier"
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
          <!-- Skeleton -->
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-12 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <!-- Empty -->
          <div v-else-if="filteredSuppliers.length === 0" class="py-20 text-center">
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
                  d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a2 2 0 100-4 2 2 0 000 4zM3 16a2 2 0 100-4 2 2 0 000 4z"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No Suppliers found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Adjust your search or add a new Supplier.
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
                  <th class="px-5 py-3.5">Supplier</th>
                  <th class="px-5 py-3.5">Company</th>
                  <th class="px-5 py-3.5">Phone</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5">Branches</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(Supplier, index) in paginatedSuppliers"
                  :key="Supplier.id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-3.5 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </td>
                  <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                      >
                        {{ Supplier.name.substring(0, 2).toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-bold text-sm text-slate-900">
                          {{ Supplier.name }}
                        </div>
                        <div class="text-[10.5px] text-slate-400 font-semibold">
                          {{ Supplier.email || "—" }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3.5 text-xs text-slate-600 font-semibold">
                    {{ Supplier.company_name || "—" }}
                  </td>
                  <td class="px-5 py-3.5 text-xs text-slate-600 font-semibold">
                    {{ Supplier.phone || "—" }}
                  </td>
                  <td class="px-5 py-3.5">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                      :class="statusClass(Supplier.status)"
                    >
                      {{
                        Supplier.status
                          ? Supplier.status.charAt(0).toUpperCase() +
                            Supplier.status.slice(1)
                          : "—"
                      }}
                    </span>
                  </td>
                  <td class="px-5 py-3.5">
                    <div class="flex flex-wrap gap-1.5">
                      <span
                        v-for="b in Supplier.branches"
                        :key="b.id"
                        class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10.5px] font-bold"
                      >
                        {{ b.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-5 py-3.5 text-right">
                    <div class="flex justify-end gap-2">
                      <router-link
                        :to="{ name: 'ViewSupplier', params: { id: Supplier.id } }"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 border border-transparent hover:bg-slate-100 hover:border-slate-200 transition"
                        title="View Details"
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
                      <button
                        @click="editSupplier(Supplier)"
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
                        @click="deleteSupplier(Supplier.id)"
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
            v-if="filteredSuppliers.length > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
          >
            <div class="text-sm text-slate-500 font-semibold">
              Showing {{ (currentPage - 1) * itemsPerPage + 1 }}–{{
                Math.min(currentPage * itemsPerPage, filteredSuppliers.length)
              }}
              of {{ filteredSuppliers.length }} Suppliers
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
import { ref, reactive, computed, watch, onMounted } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import Supplierservice from "@/services/SupplierService";
import BranchesService from "@/services/Branchesservice";

/* ─── STATE ───────────────────────────── */
const tableLoading = ref(false);
const loading = ref(false);
const editingId = ref(null);

const Suppliers = ref([]);
const branches = ref([]);
const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = 8;

const form = reactive({
  name: "",
  email: "",
  phone: "",
  tax_id: "",
  national_id: "",
  company_name: "",
  status: "",
  notes: "",
  branches: [],
});
const vErrors = reactive({
  name: false,
  tax_id: false,
  national_id: false,
  company_name: false,
  email: false,
  phone: false,
  status: false,
  branches: false,
});

/* ─── COMPUTED ────────────────────────── */
const filteredSuppliers = computed(() =>
  Suppliers.value.filter(
    (c) =>
      c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (c.email && c.email.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (c.phone && c.phone.includes(searchQuery.value))
  )
);
const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredSuppliers.value.length / itemsPerPage))
);
const paginatedSuppliers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredSuppliers.value.slice(start, start + itemsPerPage);
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

/* ─── HELPERS ─────────────────────────── */
const statusClass = (status) => {
  const map = {
    active: "bg-emerald-50 text-emerald-700 border-emerald-200",
    inactive: "bg-slate-100 text-slate-500 border-slate-200",
    suspended: "bg-red-50 text-red-600 border-red-200",
  };
  return map[status] || "bg-slate-100 text-slate-500 border-slate-200";
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
const initData = async () => {
  tableLoading.value = true;
  try {
    const [SuppliersRes, branchesRes] = await Promise.all([
      Supplierservice.getAll(),
      BranchesService.getAllBranches(),
    ]);
    Suppliers.value = SuppliersRes.data || SuppliersRes;
    branches.value = branchesRes.data || branchesRes;
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
  vErrors.email =
    form.email.trim() !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
  vErrors.phone = !form.phone;
  vErrors.status = !form.status;
  vErrors.national_id = !form.national_id;
  vErrors.email = !form.email;
  vErrors.company_name = !form.company_name;
  vErrors.branches = form.branches.length === 0;
  return (
    !vErrors.name &&
    !vErrors.email &&
    !vErrors.phone &&
    !vErrors.status &&
    !vErrors.company_name &&
    !vErrors.national_id &&
    !vErrors.branches
  );
};

/* ─── SAVE ────────────────────────────── */
const saveSupplier = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone,
      status: form.status,
      tax_id: form.tax_id,
      national_id: form.national_id,
      company_name: form.company_name,
      notes: form.notes,
      branch_ids: form.branches.map((b) => b.id),
    };
    if (editingId.value) {
      await Supplierservice.update(editingId.value, payload);
      notify("success", "Supplier Updated");
    } else {
      await Supplierservice.create(payload);
      notify("success", "Supplier Added");
    }
    resetForm();
    tableLoading.value = true;
    const res = await Supplierservice.getAll();
    Suppliers.value = res.data || res;
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
const editSupplier = (Supplier) => {
  editingId.value = Supplier.id;
  form.name = Supplier.name;
  form.email = Supplier.email || "";
  form.phone = Supplier.phone || "";
  form.status = Supplier.status || "";
  form.notes = Supplier.notes || "";
  form.branches = Supplier.branches ? [...Supplier.branches] : [];
  window.scrollTo({ top: 0, behavior: "smooth" });
};

/* ─── DELETE ──────────────────────────── */
const deleteSupplier = async (id) => {
  const result = await Swal.fire({
    title: "Delete this Supplier?",
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
    await Supplierservice.delete(id);
    Suppliers.value = Suppliers.value.filter((c) => c.id !== id);
    notify("success", "Supplier Deleted");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── RESET ───────────────────────────── */
const resetForm = () => {
  form.name = "";
  form.email = "";
  form.phone = "";
  form.status = "";
  form.notes = "";
  form.branches = [];
  editingId.value = null;
  vErrors.name = false;
  vErrors.email = false;
  vErrors.phone = false;
  vErrors.status = false;
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
