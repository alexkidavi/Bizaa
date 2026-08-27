<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Staff Profiles</h1>
        <p class="text-slate-500 mt-1 text-sm">HR profiles, status, and history.</p>
      </div>
      <router-link
        to="/hrm/employees/new"
        class="flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Add Staff Profile
      </router-link>
    </div>

    <!-- STATUS STATS -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
      <div
        v-for="s in statusStats"
        :key="s.key"
        class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm"
      >
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ s.label }}</p>
        <p class="text-xl font-extrabold" :class="statusTextClass(s.key)">{{ s.count }}</p>
      </div>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <!-- FILTER BAR -->
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-64">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Search name or employee #..."
            class="w-full pl-10 pr-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm outline-none focus:border-[#229DE7] transition"
            @input="debouncedFetch"
          />
        </div>
        <select v-model="filterDepartment" class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]" @change="fetchEmployees">
          <option value="">All Departments</option>
          <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
        <div class="flex flex-wrap items-center gap-2">
          <button
            v-for="f in statusFilters"
            :key="f.value"
            @click="filterStatus = f.value; fetchEmployees();"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition"
            :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'"
          >
            {{ f.label }}
          </button>
        </div>
      </div>

      <div v-if="tableLoading" class="p-6 space-y-3">
        <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
      </div>

      <div v-else-if="employees.length === 0" class="py-20 text-center">
        <div class="w-14 h-14 rounded-full bg-[#EAF5FD] flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <p class="font-bold text-slate-700">No staff profiles found</p>
        <p class="text-sm text-slate-400 mt-1">Add your first staff profile to get started.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5">#</th>
              <th class="px-5 py-3.5">Staff Member</th>
              <th class="px-5 py-3.5">Role</th>
              <th class="px-5 py-3.5">Department</th>
              <th class="px-5 py-3.5">Designation</th>
              <th class="px-5 py-3.5">Employed</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="(e, i) in employees" :key="e.id" class="group hover:bg-[#F5FAFF] transition-colors">
              <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ (currentPage - 1) * 50 + i + 1 }}</td>
              <td class="px-5 py-4">
                <router-link :to="`/hrm/employees/${e.id}`" class="flex items-center gap-3">
                  <img v-if="e.photo" :src="photoUrl(e.photo)" class="w-10 h-10 rounded-xl object-cover flex-shrink-0" />
                  <div v-else class="w-10 h-10 rounded-xl bg-[#EAF5FD] text-[#1682C4] flex items-center justify-center font-extrabold text-sm flex-shrink-0">
                    {{ initials(e.full_name) }}
                  </div>
                  <div>
                    <div class="font-bold text-sm text-slate-900 group-hover:text-[#229DE7] transition">{{ e.full_name }}</div>
                    <div class="text-[10.5px] text-slate-400 font-semibold">{{ e.employee_number }}</div>
                  </div>
                </router-link>
              </td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ e.user?.role?.name || "—" }}</span>
              </td>
              <td class="px-5 py-4 text-xs text-slate-600 font-semibold">{{ e.department?.name || "—" }}</td>
              <td class="px-5 py-4 text-xs text-slate-500">{{ e.designation || "—" }}</td>
              <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(e.employment_date) }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusBadgeClass(e.employment_status)">
                  {{ (e.employment_status || "").replace("_", " ") }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <router-link
                    :to="`/hrm/employees/${e.id}`"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition"
                    title="View"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </router-link>
                  <router-link
                    :to="`/hrm/employees/${e.id}/edit`"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </router-link>
                  <button
                    @click="deleteEmployee(e)"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"
                    title="Remove"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="px-6 py-4 border-t border-[#EEF5FB] flex items-center justify-between gap-3">
        <span class="text-sm text-slate-500 font-semibold">Page {{ currentPage }} of {{ totalPages }}</span>
        <div class="flex gap-2">
          <button :disabled="currentPage === 1" @click="currentPage--; fetchEmployees();" class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition">← Prev</button>
          <button :disabled="currentPage >= totalPages" @click="currentPage++; fetchEmployees();" class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition">Next →</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import EmployeeProfileService from "@/services/EmployeeProfileService";
import DepartmentService from "@/services/DepartmentService";

const tableLoading = ref(true);
const employees = ref([]);
const departments = ref([]);
const search = ref("");
const filterDepartment = ref("");
const filterStatus = ref("");
const currentPage = ref(1);
const totalPages = ref(1);

const BASE_URL = import.meta.env.VITE_APP_URL ?? "";
const photoUrl = (path) => `${BASE_URL}/storage/${path}`;

const STATUS_STYLES = {
  active: "bg-emerald-50 text-emerald-700 border-emerald-200",
  on_leave: "bg-amber-50 text-amber-700 border-amber-200",
  suspended: "bg-orange-50 text-orange-700 border-orange-200",
  resigned: "bg-slate-100 text-slate-500 border-slate-200",
  terminated: "bg-red-50 text-red-600 border-red-200",
  retired: "bg-violet-50 text-violet-700 border-violet-200",
};
const statusBadgeClass = (s) => STATUS_STYLES[s] || "bg-slate-100 text-slate-500 border-slate-200";
const statusTextClass = (s) => (statusBadgeClass(s).match(/text-\S+/) || [""])[0];

const statusFilters = [
  { label: "All", value: "" },
  { label: "Active", value: "active" },
  { label: "On Leave", value: "on_leave" },
  { label: "Suspended", value: "suspended" },
  { label: "Resigned", value: "resigned" },
  { label: "Terminated", value: "terminated" },
  { label: "Retired", value: "retired" },
];

const statusStats = computed(() =>
  statusFilters.slice(1).map((f) => ({
    key: f.value,
    label: f.label,
    count: employees.value.filter((e) => e.employment_status === f.value).length,
  }))
);

const initials = (name) =>
  (name || "?").split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase();
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");

let debounceTimer = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchEmployees(), 350);
};

const fetchEmployees = async () => {
  tableLoading.value = true;
  try {
    const params = { page: currentPage.value, per_page: 50 };
    if (search.value) params.search = search.value;
    if (filterDepartment.value) params.department_id = filterDepartment.value;
    if (filterStatus.value) params.employment_status = filterStatus.value;
    const res = await EmployeeProfileService.getAll(params);
    employees.value = res?.data ?? [];
    totalPages.value = res?.last_page ?? 1;
  } finally {
    tableLoading.value = false;
  }
};

const deleteEmployee = async (e) => {
  const result = await Swal.fire({
    title: `Remove ${e.full_name}?`,
    text: "This removes the staff profile. It can be restored by support if needed.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, remove",
  });
  if (!result.isConfirmed) return;
  try {
    await EmployeeProfileService.remove(e.id);
    await fetchEmployees();
    Swal.fire({ icon: "success", title: "Staff profile removed", toast: true, position: "top-end", showConfirmButton: false, timer: 2000 });
  } catch {
    Swal.fire({ icon: "error", title: "Remove failed", confirmButtonColor: "#229DE7" });
  }
};

onMounted(async () => {
  const [deptRes] = await Promise.all([DepartmentService.getAll(), fetchEmployees()]);
  departments.value = deptRes?.data ?? [];
});
</script>
