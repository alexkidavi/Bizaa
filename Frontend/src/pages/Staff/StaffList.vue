<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Staff Management
        </h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your team members, roles and access.
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
            placeholder="Search staff..."
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
          />
        </div>
      </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div
        v-for="role in roleStats"
        :key="role.name"
        class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm"
      >
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          {{ role.name }}
        </p>
        <p class="text-xl font-extrabold" :class="role.color">{{ role.count }}</p>
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
              {{ editingId ? "Edit Staff Member" : "Add Staff Member" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>

          <form @submit.prevent="saveStaff" class="p-6 space-y-4">
            <!-- Name -->
            <div>
              <label class="field-label required">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': vErrors.name }"
                placeholder="e.g. John Doe"
              />
              <p v-if="vErrors.name" class="field-error">Full name is required</p>
            </div>

            <!-- Email -->
            <div>
              <label class="field-label required">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                class="field-input"
                :class="{ 'border-red-400': vErrors.email }"
                placeholder="e.g. john@example.com"
              />
              <p v-if="vErrors.email" class="field-error">
                {{ vErrors.emailMsg || "Valid email is required" }}
              </p>
            </div>

            <!-- Phone -->
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

            <!-- Role -->
            <div>
              <label class="field-label required">Role</label>
              <select
                v-model="form.role_id"
                class="field-input"
                :class="{ 'border-red-400': vErrors.role_id }"
              >
                <option value="" disabled>Select role...</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
              <p v-if="vErrors.role_id" class="field-error">Role is required</p>
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
                  ? "Processing..."
                  : editingId
                  ? "Update Staff"
                  : "Add Staff Member"
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
          <!-- Role filter -->
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-2"
          >
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mr-1"
              >Role:</span
            >
            <button
              v-for="f in roleFilters"
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
          <div v-if="tableLoading" class="p-6 space-y-4">
            <div v-for="n in 8" :key="n" class="flex items-center gap-4">
              <div class="h-10 w-10 bg-slate-100 animate-pulse rounded-xl"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-slate-100 animate-pulse rounded w-1/3"></div>
                <div class="h-3 bg-slate-50 animate-pulse rounded w-1/4"></div>
              </div>
              <div class="h-8 w-20 bg-slate-50 animate-pulse rounded-lg"></div>
              <div class="h-8 w-10 bg-slate-50 animate-pulse rounded-lg"></div>
            </div>
          </div>

          <!-- Empty -->
          <div v-else-if="filteredStaff.length === 0" class="py-20 text-center">
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
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No staff members found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Add your first team member to get started.
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
                  <th class="px-5 py-3.5">Staff Member</th>
                  <th class="px-5 py-3.5">Role</th>
                  <th class="px-5 py-3.5">Phone</th>
                  <th class="px-5 py-3.5">Registered By</th>
                  <th class="px-5 py-3.5">Joined</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(member, index) in paginatedStaff"
                  :key="member.id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </td>

                  <!-- Avatar + name + email -->
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-sm flex-shrink-0 group-hover:scale-105 transition-transform"
                        :style="{
                          background: roleColor(member.role?.name).bg,
                          color: roleColor(member.role?.name).text,
                        }"
                      >
                        {{ initials(member.name) }}
                      </div>
                      <div>
                        <div class="font-bold text-sm text-slate-900">
                          {{ member.name }}
                        </div>
                        <div class="text-[10.5px] text-slate-400 font-semibold">
                          {{ member.email }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Role badge -->
                  <td class="px-5 py-4">
                    <span
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10.5px] font-bold border"
                      :class="roleBadgeClass(member.role?.name)"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full"
                        :class="roleDotClass(member.role?.name)"
                      ></span>
                      {{ member.role?.name || "—" }}
                    </span>
                  </td>

                  <!-- Phone -->
                  <td class="px-5 py-4 text-xs text-slate-600 font-semibold">
                    {{ member.phone || "—" }}
                  </td>

                  <!-- Registered by -->
                  <td class="px-5 py-4 text-xs text-slate-500">
                    {{ member.registered_by_user?.name || "System" }}
                  </td>

                  <!-- Joined -->
                  <td
                    class="px-5 py-4 text-xs text-slate-500 font-semibold whitespace-nowrap"
                  >
                    {{ formatDate(member.created_at) }}
                  </td>

                  <!-- Actions -->
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="editStaff(member)"
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
                        @click="toggleStatus(member)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center border border-transparent transition"
                        :class="
                          member.is_active
                            ? 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200'
                            : 'text-slate-400 hover:bg-slate-100 hover:border-slate-200'
                        "
                        :title="member.is_active ? 'Deactivate' : 'Activate'"
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                          />
                        </svg>
                      </button>
                      <button
                        @click="deleteStaff(member.id)"
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
            v-if="filteredStaff.length > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3"
          >
            <div class="text-sm text-slate-500 font-semibold">
              Showing {{ (currentPage - 1) * itemsPerPage + 1 }}–{{
                Math.min(currentPage * itemsPerPage, filteredStaff.length)
              }}
              of {{ filteredStaff.length }} staff members
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
import Swal from "sweetalert2";
import StaffService from "@/services/StaffService";
import RoleService from "@/services/RoleService";

/* ─── STATE ───────────────────────────── */
const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);
const showPassword = ref(false);

const staff = ref([]);
const roles = ref([]);
const searchQuery = ref("");
const activeFilter = ref("all");
const currentPage = ref(1);
const itemsPerPage = 10;

const form = reactive({
  name: "",
  email: "",
  phone: "",
  role_id: "",
});

const vErrors = reactive({
  name: false,
  email: false,
  emailMsg: "",
  phone: false,
  role_id: false,
});

/* ─── ROLE CONFIG ─────────────────────── */
const ROLE_CONFIG = {
  admin: {
    bg: "#EAF5FD",
    text: "#1682C4",
    dot: "bg-[#229DE7]",
    badge: "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]",
  },
  cashier: {
    bg: "#ECFDF5",
    text: "#065f46",
    dot: "bg-emerald-500",
    badge: "bg-emerald-50 text-emerald-700 border-emerald-200",
  },
  accountant: {
    bg: "#FEF3C7",
    text: "#92400e",
    dot: "bg-amber-500",
    badge: "bg-amber-50 text-amber-700 border-amber-200",
  },
  "customer service": {
    bg: "#F3E8FF",
    text: "#6b21a8",
    dot: "bg-violet-500",
    badge: "bg-violet-50 text-violet-700 border-violet-200",
  },
};

const roleColor = (name) => {
  const key = (name || "").toLowerCase();
  return ROLE_CONFIG[key] || { bg: "#F1F5F9", text: "#475569" };
};
const roleBadgeClass = (name) => {
  const key = (name || "").toLowerCase();
  return ROLE_CONFIG[key]?.badge || "bg-slate-100 text-slate-500 border-slate-200";
};
const roleDotClass = (name) => {
  const key = (name || "").toLowerCase();
  return ROLE_CONFIG[key]?.dot || "bg-slate-400";
};

/* ─── ROLE FILTERS ────────────────────── */
const roleFilters = computed(() => [
  { label: "All", value: "all" },
  ...roles.value.map((r) => ({ label: r.name, value: r.name.toLowerCase() })),
]);

const roleStats = computed(() =>
  roles.value.map((r) => ({
    name: r.name,
    count: staff.value.filter((s) => s.role?.name?.toLowerCase() === r.name.toLowerCase())
      .length,
    color: roleBadgeClass(r.name).split(" ")[1] || "text-slate-800",
  }))
);

/* ─── COMPUTED ────────────────────────── */
const filteredStaff = computed(() =>
  staff.value.filter((s) => {
    const matchSearch =
      s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      s.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (s.phone || "").includes(searchQuery.value);
    const matchRole =
      activeFilter.value === "all" ||
      (s.role?.name || "").toLowerCase() === activeFilter.value;
    return matchSearch && matchRole;
  })
);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredStaff.value.length / itemsPerPage))
);

const paginatedStaff = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredStaff.value.slice(start, start + itemsPerPage);
});

watch([searchQuery, activeFilter], () => {
  currentPage.value = 1;
});

/* ─── HELPERS ─────────────────────────── */
const initials = (name) =>
  (name || "?")
    .split(" ")
    .slice(0, 2)
    .map((w) => w[0])
    .join("")
    .toUpperCase();
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
const loadStaff = async () => {
  tableLoading.value = true;
  try {
    const res = await StaffService.getAllStaff();
    staff.value = res?.data?.data ?? res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

onMounted(async () => {
  tableLoading.value = true;
  try {
    const [staffRes, rolesRes] = await Promise.all([
      StaffService.getAllStaff(),
      RoleService.getAll(),
    ]);
    staff.value = staffRes?.data?.data ?? staffRes?.data ?? [];
    roles.value = rolesRes?.data?.data ?? rolesRes?.data ?? [];
  } catch {
    Swal.fire("Error", "Could not load staff data.", "error");
  } finally {
    tableLoading.value = false;
  }
});

/* ─── VALIDATE ────────────────────────── */
const validate = () => {
  let valid = true;

  vErrors.name = !form.name.trim();
  vErrors.email = !form.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
  vErrors.emailMsg = !form.email.trim()
    ? "Email is required"
    : "Enter a valid email address";
  vErrors.phone = !form.phone.trim();
  vErrors.role_id = !form.role_id;

  if (vErrors.name || vErrors.email || vErrors.phone || vErrors.role_id) {
    valid = false;
  }

  return valid;
};
/* ─── SAVE ────────────────────────────── */
const saveStaff = async () => {
  if (!validate()) return;

  loading.value = true;

  try {
    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone,
      role_id: form.role_id,
    };

    let res;

    if (editingId.value) {
      res = await StaffService.updateStaff(editingId.value, payload);
    } else {
      res = await StaffService.create(payload);
    }

    Swal.fire({
      icon: "success",
      title: res?.data?.message || "Success",
      confirmButtonColor: "#229DE7",
    });

    resetForm();
    await loadStaff();
  } catch (err) {
    handleError(err);
  } finally {
    loading.value = false;
  }
};
const handleError = (err) => {
  const res = err?.response;

  if (res?.status === 422 && res.data?.errors) {
    const errors = res.data.errors;

    Object.keys(vErrors).forEach((k) => (vErrors[k] = false));

    if (errors.name) vErrors.name = true;
    if (errors.email) {
      vErrors.email = true;
      vErrors.emailMsg = errors.email[0];
    }
    if (errors.phone) vErrors.phone = true;
    if (errors.role_id) vErrors.role_id = true;

    Swal.fire({
      icon: "error",
      title: "Validation Error",
      text: "Please fix the highlighted fields.",
      confirmButtonColor: "#229DE7",
    });

    return;
  }

  Swal.fire({
    icon: "error",
    title: "Error",
    text: res?.data?.message || "Something went wrong.",
    confirmButtonColor: "#229DE7",
  });
};
/* ─── EDIT ────────────────────────────── */
const openCreate = () => resetForm();

const editStaff = (member) => {
  editingId.value = member.id;
  form.name = member.name;
  form.email = member.email;
  form.phone = member.phone || "";
  form.role_id = member.role_id;
  window.scrollTo({ top: 0, behavior: "smooth" });
};

/* ─── TOGGLE STATUS ───────────────────── */
const toggleStatus = async (member) => {
  const action = member.is_active ? "deactivate" : "activate";
  const result = await Swal.fire({
    title: `${member.is_active ? "Deactivate" : "Activate"} ${member.name}?`,
    text: member.is_active
      ? "This staff member will lose access to the system."
      : "This staff member will regain system access.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: `Yes, ${action}`,
  });
  if (!result.isConfirmed) return;
  try {
    await StaffService.toggleStatus(member.id);
    member.is_active = !member.is_active;
    notify("success", `Staff ${member.is_active ? "Activated" : "Deactivated"}`);
  } catch {
    Swal.fire({ icon: "error", title: "Action Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── DELETE ──────────────────────────── */
const deleteStaff = async (id) => {
  const result = await Swal.fire({
    title: "Remove this staff member?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, remove",
  });
  if (!result.isConfirmed) return;
  try {
    await StaffService.delete(id);
    staff.value = staff.value.filter((s) => s.id !== id);
    notify("success", "Staff Member Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── RESET ───────────────────────────── */
const resetForm = () => {
  editingId.value = null;
  form.name = "";
  form.email = "";
  form.phone = "";
  form.role_id = "";
  showPassword.value = false;
  Object.keys(vErrors).forEach((k) => {
    vErrors[k] = false;
  });
  vErrors.emailMsg = "";
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
