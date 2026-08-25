<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Departments</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage company departments and their staff.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
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
              {{ editingId ? "Edit Department" : "Add Department" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Department Name</label>
              <input
                v-model="form.name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': errors.name }"
                placeholder="e.g. Sales"
              />
              <p v-if="errors.name" class="field-error">Name is required</p>
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea
                v-model="form.description"
                rows="2"
                class="field-input"
                placeholder="Optional description"
              ></textarea>
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60"
            >
              <svg
                v-if="loading"
                class="cursor-pointer animate-spin h-4 w-4"
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
              {{ loading ? "Saving..." : editingId ? "Update" : "Add Department" }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <span class="font-bold text-sm text-slate-700"
              >{{ departments.length }} departments</span
            >
            <input
              v-model="search"
              type="text"
              placeholder="Search..."
              class="w-52 px-3 py-2 text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl outline-none focus:border-[#229DE7] transition"
            />
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-10 bg-slate-100 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else-if="filtered.length === 0" class="py-20 text-center">
            <div
              class="w-14 h-14 rounded-full bg-[#EAF5FD] flex items-center justify-center mx-auto mb-4"
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
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                />
              </svg>
            </div>
            <p class="font-bold text-slate-700">No departments yet</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-5 py-3.5 w-10">#</th>
                  <th class="px-5 py-3.5">Department</th>
                  <th class="px-5 py-3.5">Description</th>
                  <th class="px-5 py-3.5 text-center">Staff</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(dept, i) in filtered"
                  :key="dept.id"
                  class="hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">
                    {{ dept.name }}
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-500">
                    {{ dept.description || "—" }}
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span
                      class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]"
                      >{{ dept.staff_count ?? 0 }}</span
                    >
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="edit(dept)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"
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
                        @click="remove(dept.id)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"
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
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import DepartmentService from "@/services/DepartmentService";

const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);
const search = ref("");
const departments = ref([]);
const form = reactive({ name: "", description: "" });
const errors = reactive({ name: false });

const filtered = computed(() =>
  departments.value.filter((d) =>
    d.name.toLowerCase().includes(search.value.toLowerCase())
  )
);
const toast = (icon, title) =>
  Swal.fire({
    icon,
    title,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });

const load = async () => {
  tableLoading.value = true;
  try {
    const res = await DepartmentService.getAll();
    departments.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};
onMounted(load);

const save = async () => {
  errors.name = !form.name.trim();
  if (errors.name) return;
  loading.value = true;
  try {
    const payload = { name: form.name.trim(), description: form.description };
    if (editingId.value) {
      const res = await DepartmentService.update(editingId.value, payload);
      const idx = departments.value.findIndex((d) => d.id === editingId.value);
      if (idx !== -1) departments.value[idx] = res.data;
      toast("success", "Department Updated");
    } else {
      const res = await DepartmentService.create(payload);
      departments.value.unshift(res.data);
      toast("success", "Department Added");
    }
    resetForm();
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err?.response?.data?.message || "Something went wrong",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
  }
};

const edit = (dept) => {
  editingId.value = dept.id;
  form.name = dept.name;
  form.description = dept.description || "";
};
const remove = async (id) => {
  const r = await Swal.fire({
    title: "Delete department?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!r.isConfirmed) return;
  try {
    await DepartmentService.remove(id);
    departments.value = departments.value.filter((d) => d.id !== id);
    toast("success", "Deleted");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};
const resetForm = () => {
  editingId.value = null;
  form.name = "";
  form.description = "";
  errors.name = false;
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
  transition: border-color 0.15s;
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
