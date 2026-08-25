<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Roles & Permissions
        </h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage system roles and control access permissions for staff members.
        </p>
      </div>
      <button
        @click="openCreateModal"
        :disabled="isAdminRoleSelected"
        class="cursor-pointer flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        :title="
          isAdminRoleSelected ? 'Cannot create new role while Admin is selected' : ''
        "
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
        New Role
      </button>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- ROLES LIST -->
      <aside class="lg:col-span-5">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>

          <!-- Search -->
          <div class="p-5 border-b border-[#EEF5FB]">
            <div class="relative">
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
                placeholder="Search roles..."
                class="w-full pl-10 pr-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
              />
            </div>
          </div>

          <!-- Roles List -->
          <div v-if="loading" class="p-6 space-y-3">
            <div v-for="n in 4" :key="n" class="flex items-center gap-3">
              <div class="h-10 w-10 bg-slate-100 animate-pulse rounded-xl"></div>
              <div class="flex-1">
                <div class="h-4 bg-slate-100 animate-pulse rounded w-1/2"></div>
                <div class="h-3 bg-slate-50 animate-pulse rounded w-1/3 mt-1"></div>
              </div>
            </div>
          </div>

          <div v-else-if="filteredRoles.length === 0" class="py-16 text-center">
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
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No roles found</h3>
            <p class="text-slate-500 text-sm mt-1">
              Create your first role to get started.
            </p>
          </div>

          <div v-else class="divide-y divide-[#EEF5FB]">
            <div
              v-for="role in filteredRoles"
              :key="role.id"
              @click="selectRole(role)"
              class="p-5 cursor-pointer transition-all hover:bg-[#F5FAFF]"
              :class="{
                'bg-[#EAF5FD] border-l-4 border-l-[#229DE7]':
                  selectedRole?.id === role.id,
              }"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <div
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold"
                      :style="{
                        background: roleColor(role.name).bg,
                        color: roleColor(role.name).text,
                      }"
                    >
                      {{ roleInitials(role.name) }}
                    </div>
                    <div>
                      <h3 class="font-extrabold text-slate-900">
                        {{ role.name }}
                        <span
                          v-if="isAdminRole(role.name)"
                          class="ml-2 text-xs text-red-500 font-normal"
                        >
                          (Protected)
                        </span>
                      </h3>
                      <p class="text-xs text-slate-500 mt-0.5">
                        {{ role.staff_count || 0 }} staff members
                      </p>
                    </div>
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <span
                    class="px-2 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wider"
                    :class="
                      role.is_system
                        ? 'bg-amber-50 text-amber-600'
                        : 'bg-emerald-50 text-emerald-600'
                    "
                  >
                    {{ role.is_system ? "System" : "Custom" }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- PERMISSIONS PANEL -->
      <main class="lg:col-span-7">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>

          <!-- Header -->
          <div
            class="px-6 py-5 border-b border-[#EEF5FB] flex flex-wrap items-center justify-between gap-3"
          >
            <div>
              <h2 class="font-extrabold text-lg text-slate-900">
                {{ selectedRole ? selectedRole.name : "Select a Role" }}
                <span
                  v-if="selectedRole && isAdminRole(selectedRole.name)"
                  class="ml-2 text-xs text-red-500 font-normal"
                >
                  (Protected Role - Cannot Modify)
                </span>
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                {{
                  selectedRole
                    ? isAdminRole(selectedRole.name)
                      ? "Admin role permissions are system protected and cannot be modified"
                      : `Configure permissions for ${selectedRole.name}`
                    : "Choose a role from the left panel"
                }}
              </p>
            </div>
            <div
              v-if="
                selectedRole && !selectedRole.is_system && !isAdminRole(selectedRole.name)
              "
              class="flex gap-2"
            >
              <button
                @click="openEditModal"
                class="px-4 py-2 rounded-xl text-sm font-bold border border-[#D9EBF7] text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
              >
                Edit Role
              </button>
              <button
                @click="deleteRole"
                class="px-4 py-2 rounded-xl text-sm font-bold border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 transition"
              >
                Delete
              </button>
            </div>
            <div
              v-else-if="selectedRole && isAdminRole(selectedRole.name)"
              class="flex gap-2"
            >
              <div
                class="px-4 py-2 rounded-xl text-sm font-bold bg-slate-100 text-slate-400 cursor-not-allowed"
                title="Admin role cannot be edited"
              >
                Edit Role
              </div>
              <div
                class="px-4 py-2 rounded-xl text-sm font-bold bg-slate-100 text-slate-400 cursor-not-allowed"
                title="Admin role cannot be deleted"
              >
                Delete
              </div>
            </div>
          </div>

          <!-- Permissions Content -->
          <div v-if="!selectedRole" class="py-24 text-center">
            <div
              class="w-20 h-20 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-10 h-10 text-[#229DE7]"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">No Role Selected</h3>
            <p class="text-slate-500 text-sm mt-1">
              Select a role from the left panel to manage its permissions.
            </p>
          </div>

          <div v-else-if="permissionsLoading" class="p-6 space-y-4">
            <div
              v-for="n in 6"
              :key="n"
              class="flex items-center justify-between p-4 bg-slate-50 rounded-xl"
            >
              <div class="space-y-2">
                <div class="h-4 bg-slate-200 animate-pulse rounded w-32"></div>
                <div class="h-3 bg-slate-100 animate-pulse rounded w-48"></div>
              </div>
              <div class="h-6 w-12 bg-slate-200 animate-pulse rounded-full"></div>
            </div>
          </div>

          <!-- Admin Role Protection Message -->
          <div
            v-else-if="selectedRole && isAdminRole(selectedRole.name)"
            class="py-16 text-center"
          >
            <div
              class="w-20 h-20 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-10 h-10 text-amber-600"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
            </div>
            <h3 class="font-bold text-base text-slate-900">
              Administrator Role Protected
            </h3>
            <p class="text-slate-500 text-sm mt-2 max-w-md mx-auto">
              The Administrator role has all system permissions by default and cannot be
              modified to maintain system security and integrity.
            </p>
          </div>

          <div v-else class="divide-y divide-[#EEF5FB]">
            <!-- Group by module -->
            <div v-for="(group, module) in groupedPermissions" :key="module" class="py-4">
              <div class="px-6 py-3 bg-[#FAFDFF]">
                <div class="flex items-center gap-2">
                  <span
                    class="text-xs font-extrabold uppercase tracking-wider text-[#1682C4]"
                  >
                    {{ formatModuleName(module) }}
                  </span>
                  <span class="text-[10px] text-slate-400">
                    ({{ group.permissions.length }} permissions)
                  </span>
                </div>
              </div>

              <div class="space-y-2 px-4">
                <label
                  v-for="permission in group.permissions"
                  :key="permission.id"
                  class="flex items-center justify-between p-3 rounded-xl cursor-pointer hover:bg-[#F5FAFF] transition"
                  :class="{
                    'opacity-60':
                      isAdminRole(selectedRole?.name) ||
                      (isSystemRole && !permission.is_assigned),
                  }"
                >
                  <div class="flex-1">
                    <div class="font-semibold text-sm text-slate-800">
                      {{ formatPermissionName(permission.name) }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                      {{ getPermissionDescription(permission.name) }}
                    </div>
                  </div>
                  <div class="relative">
                    <input
                      type="checkbox"
                      :id="`perm-${permission.id}`"
                      :value="permission.id"
                      v-model="selectedPermissions"
                      class="peer sr-only"
                      :disabled="
                        isAdminRole(selectedRole?.name) ||
                        (isSystemRole && !permission.is_assigned)
                      "
                    />
                    <label
                      :for="`perm-${permission.id}`"
                      class="block w-10 h-5 rounded-full bg-slate-300 cursor-pointer transition-all duration-200 peer-checked:bg-[#229DE7] peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all"
                      :class="{
                        'opacity-50 cursor-not-allowed':
                          isAdminRole(selectedRole?.name) ||
                          (isSystemRole && !permission.is_assigned),
                      }"
                    ></label>
                  </div>
                </label>
              </div>
            </div>

            <!-- Save Button - Hidden for Admin Role -->
            <div
              v-if="!isAdminRole(selectedRole?.name)"
              class="px-6 py-5 border-t border-[#EEF5FB] bg-[#FAFDFF]"
            >
              <button
                @click="savePermissions"
                :disabled="savingPermissions"
                class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60"
              >
                <svg
                  v-if="savingPermissions"
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
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                {{ savingPermissions ? "Saving..." : "Save Permissions" }}
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Create/Edit Role Modal - Prevent editing Admin role -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div
        class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-in fade-in zoom-in duration-200"
      >
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7] rounded-t-2xl"
        ></div>

        <div class="p-6">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-extrabold text-lg text-slate-900">
              {{ editingRole ? "Edit Role" : "Create New Role" }}
            </h3>
            <button
              @click="closeModal"
              class="text-slate-400 hover:text-slate-600 transition"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="field-label required">Role Name</label>
              <input
                v-model="roleForm.name"
                type="text"
                class="field-input"
                :class="{
                  'border-red-400': roleErrors.name,
                  'bg-slate-100': editingRole && isAdminRole(editingRole.name),
                }"
                placeholder="e.g., Sales Manager"
                :disabled="editingRole && isAdminRole(editingRole.name)"
              />
              <p v-if="roleErrors.name" class="field-error">Role name is required</p>
              <p
                v-if="editingRole && isAdminRole(editingRole.name)"
                class="text-xs text-amber-600 mt-1"
              >
                Admin role name cannot be changed
              </p>
            </div>

            <div>
              <label class="field-label">Description</label>
              <textarea
                v-model="roleForm.description"
                rows="3"
                class="field-input"
                :class="{ 'bg-slate-100': editingRole && isAdminRole(editingRole.name) }"
                placeholder="Optional: Describe the responsibilities of this role..."
                :disabled="editingRole && isAdminRole(editingRole.name)"
              ></textarea>
            </div>
          </div>

          <div class="flex gap-3 mt-6">
            <button
              @click="closeModal"
              class="flex-1 px-4 py-2.5 rounded-xl border border-[#D9EBF7] text-sm font-bold text-slate-600 hover:bg-slate-50 transition"
            >
              Cancel
            </button>
            <button
              v-if="!editingRole || !isAdminRole(editingRole.name)"
              @click="saveRole"
              :disabled="savingRole"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition active:scale-[0.98] disabled:opacity-60"
            >
              <svg
                v-if="savingRole"
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
              {{ savingRole ? "Saving..." : editingRole ? "Update Role" : "Create Role" }}
            </button>
            <button
              v-else
              disabled
              class="flex-1 px-4 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-sm font-bold cursor-not-allowed"
            >
              Cannot Edit Admin Role
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import Swal from "sweetalert2";
import RoleService from "@/services/RoleService";
import PermissionService from "@/services/PermissionService";

/* ─── STATE ───────────────────────────── */
const loading = ref(true);
const permissionsLoading = ref(false);
const savingPermissions = ref(false);
const savingRole = ref(false);
const searchQuery = ref("");
const selectedRole = ref(null);
const showModal = ref(false);
const editingRole = ref(null);

const roles = ref([]);
const allPermissions = ref([]);
const selectedPermissions = ref([]);
const originalPermissions = ref([]); // Track original permissions to detect changes

const roleForm = reactive({
  name: "",
  description: "",
});

const roleErrors = reactive({
  name: false,
});

/* ─── HELPER FUNCTIONS ────────────────── */
const isAdminRole = (roleName) => {
  if (!roleName) return false;
  const name = roleName.toLowerCase();
  return name === "admin" || name === "administrator";
};

/* ─── COMPUTED ────────────────────────── */
const isAdminRoleSelected = computed(() => {
  return selectedRole.value && isAdminRole(selectedRole.value.name);
});

const filteredRoles = computed(() => {
  if (!searchQuery.value) return roles.value;
  return roles.value.filter((role) =>
    role.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const isSystemRole = computed(() => selectedRole.value?.is_system === true);

const groupedPermissions = computed(() => {
  const groups = {};

  allPermissions.value.forEach((permission) => {
    // Extract module from permission name (e.g., "dashboard.view" -> "dashboard")
    let module = permission.name.split(".")[0];

    // Format module name for special cases
    if (module === "mpesa") module = "M-Pesa";
    if (module === "hr") module = "HR Management";
    if (module === "purchases") module = "Purchases";
    if (module === "expenses") module = "Expenses";
    if (module === "invoices") module = "Invoices";
    if (module === "quotations") module = "Quotations";
    if (module === "customers") module = "Customers";
    if (module === "suppliers") module = "Suppliers";
    if (module === "reports") module = "Reports";
    if (module === "staff") module = "Staff";
    if (module === "roles") module = "Roles & Permissions";
    if (module === "branches") module = "Branches";
    if (module === "stock") module = "Stock Management";

    module = module.charAt(0).toUpperCase() + module.slice(1);

    if (!groups[module]) {
      groups[module] = { permissions: [] };
    }
    groups[module].permissions.push(permission);
  });

  // Sort modules alphabetically
  const sortedGroups = {};
  Object.keys(groups)
    .sort()
    .forEach((key) => {
      sortedGroups[key] = groups[key];
    });

  return sortedGroups;
});

const formatModuleName = (module) => {
  return module.toUpperCase();
};

const formatPermissionName = (name) => {
  // Remove module prefix and format nicely
  let parts = name.split(".");
  let action = parts[parts.length - 1];

  // Special handling for specific permissions
  const specialFormats = {
    "dashboard.view": "View Dashboard",
    "business.view": "View Business Info",
    "business.update": "Update Business Info",
    "products.view": "View Products",
    "products.create": "Create Products",
    "products.update": "Update Products",
    "products.delete": "Delete Products",
    "products.view_single": "View Product Details",
    "categories.manage": "Manage Categories",
    "brands.manage": "Manage Brands",
    "units.manage": "Manage Units",
    "branches.view": "View Branches",
    "branches.create": "Create Branches",
    "branches.update": "Update Branches",
    "branches.delete": "Delete Branches",
    "branches.status_update": "Update Branch Status",
    "sales.view": "View Sales",
    "sales.create": "Create Sales",
    "sales.update": "Update Sales",
    "sales.delete": "Delete Sales",
    "sales.record_payment": "Record Sales Payment",
    "stock.view": "View Stock",
    "stock.adjust": "Adjust Stock",
    "stock.transfer": "Transfer Stock",
    "stock.take": "Stock Take",
    "customers.manage": "Manage Customers",
    "suppliers.manage": "Manage Suppliers",
    "purchases.manage": "Manage Purchases",
    "purchases.view_items": "View Purchase Items",
    "purchases.view_payments": "View Purchase Payments",
    "invoices.manage": "Manage Invoices",
    "quotations.manage": "Manage Quotations",
    "quotations.convert": "Convert Quotations",
    "expenses.manage": "Manage Expenses",
    "expense_categories.manage": "Manage Expense Categories",
    "hr.departments": "Manage Departments",
    "hr.shifts": "Manage Shifts",
    "hr.attendance": "Manage Attendance",
    "hr.overtime": "Manage Overtime",
    "hr.leaves": "Manage Leaves",
    "hr.payroll": "Manage Payroll",
    "reports.view": "View Reports",
    "staff.manage": "Manage Staff",
    "roles.view": "View Roles",
    "roles.manage": "Manage Roles",
    "mpesa.pay": "M-Pesa Payments",
    "mpesa.status": "M-Pesa Status",
  };

  if (specialFormats[name]) {
    return specialFormats[name];
  }

  // Default formatting
  return action
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

const getPermissionDescription = (name) => {
  const descriptions = {
    "dashboard.view": "Access the main dashboard and overview",
    "business.view": "View business information and settings",
    "business.update": "Update business details and configuration",
    "products.view": "View product catalog and details",
    "products.create": "Add new products to the catalog",
    "products.update": "Edit existing product information",
    "products.delete": "Remove products from the catalog",
    "products.view_single": "View detailed product information",
    "categories.manage": "Create, edit, and delete product categories",
    "brands.manage": "Manage product brands",
    "units.manage": "Manage product units of measurement",
    "branches.view": "View branch information",
    "branches.create": "Create new branches",
    "branches.update": "Edit branch details",
    "branches.delete": "Remove branches",
    "branches.status_update": "Activate or deactivate branches",
    "sales.view": "View sales transactions",
    "sales.create": "Process new sales",
    "sales.update": "Edit existing sales",
    "sales.delete": "Cancel/delete sales",
    "sales.record_payment": "Record payments for sales",
    "stock.view": "View current stock levels",
    "stock.adjust": "Adjust stock quantities",
    "stock.transfer": "Transfer stock between branches",
    "stock.take": "Perform stock take/count",
    "customers.manage": "Manage customer information",
    "suppliers.manage": "Manage supplier information",
    "purchases.manage": "Manage purchase orders",
    "purchases.view_items": "View purchase order items",
    "purchases.view_payments": "View purchase payment history",
    "invoices.manage": "Create and manage invoices",
    "quotations.manage": "Create and manage quotations",
    "quotations.convert": "Convert quotations to sales",
    "expenses.manage": "Manage business expenses",
    "expense_categories.manage": "Manage expense categories",
    "hr.departments": "Manage company departments",
    "hr.shifts": "Manage work shifts",
    "hr.attendance": "Manage staff attendance",
    "hr.overtime": "Manage overtime records",
    "hr.leaves": "Manage staff leave requests",
    "hr.payroll": "Process payroll",
    "reports.view": "Access various reports",
    "staff.manage": "Manage staff members",
    "roles.view": "View roles and permissions",
    "roles.manage": "Create and edit roles",
    "mpesa.pay": "Process M-Pesa payments",
    "mpesa.status": "Check M-Pesa transaction status",
  };

  return (
    descriptions[name] || `Permission to ${formatPermissionName(name).toLowerCase()}`
  );
};

/* ─── ROLE CONFIG ─────────────────────── */
const ROLE_COLORS = {
  admin: { bg: "#EAF5FD", text: "#1682C4" },
  manager: { bg: "#ECFDF5", text: "#065f46" },
  cashier: { bg: "#FEF3C7", text: "#92400e" },
  accountant: { bg: "#F3E8FF", text: "#6b21a8" },
};

const roleColor = (name) => {
  const key = (name || "").toLowerCase();
  for (const [roleKey, color] of Object.entries(ROLE_COLORS)) {
    if (key.includes(roleKey)) return color;
  }
  return { bg: "#F1F5F9", text: "#475569" };
};

const roleInitials = (name) => {
  return (name || "R")
    .split(" ")
    .slice(0, 2)
    .map((w) => w[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
};

/* ─── WATCHERS ────────────────────────── */
watch(selectedRole, async (newRole) => {
  if (newRole) {
    await loadRolePermissions(newRole.id);
  }
});

/* ─── API METHODS ─────────────────────── */
const loadRoles = async () => {
  loading.value = true;
  try {
    const res = await RoleService.getAll();
    roles.value = res?.data?.data ?? res?.data ?? [];
  } catch (error) {
    console.error("Error loading roles:", error);
    Swal.fire("Error", "Could not load roles.", "error");
  } finally {
    loading.value = false;
  }
};

const loadPermissions = async () => {
  try {
    const res = await PermissionService.getAll();
    allPermissions.value = res?.data?.data ?? res?.data ?? [];
  } catch (error) {
    console.error("Error loading permissions:", error);
  }
};

const loadRolePermissions = async (roleId) => {
  permissionsLoading.value = true;
  try {
    const res = await RoleService.getPermissions(roleId);
    const assignedPermissions = res?.data?.data ?? res?.data ?? [];

    // Store original permissions for change detection
    originalPermissions.value = assignedPermissions.map((p) => p.id);

    // Mark which permissions are assigned in the permissions list
    allPermissions.value.forEach((perm) => {
      perm.is_assigned = assignedPermissions.some((ap) => ap.id === perm.id);
    });

    // Set selected permissions to the assigned ones
    selectedPermissions.value = assignedPermissions.map((p) => p.id);

    console.log(
      `Loaded ${selectedPermissions.value.length} permissions for role ${roleId}`
    );
  } catch (error) {
    console.error("Error loading role permissions:", error);
    selectedPermissions.value = [];
    originalPermissions.value = [];
  } finally {
    permissionsLoading.value = false;
  }
};

const savePermissions = async () => {
  if (!selectedRole.value) return;

  // Prevent saving permissions for Admin role
  if (isAdminRole(selectedRole.value.name)) {
    Swal.fire({
      icon: "warning",
      title: "Action Not Allowed",
      text: "Administrator role permissions are system protected and cannot be modified.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  savingPermissions.value = true;
  try {
    await RoleService.assignPermissions(selectedRole.value.id, {
      permission_ids: selectedPermissions.value,
    });

    // Update original permissions after successful save
    originalPermissions.value = [...selectedPermissions.value];

    Swal.fire({
      icon: "success",
      title: "Permissions Updated",
      text: `Permissions for "${selectedRole.value.name}" have been saved successfully.`,
      confirmButtonColor: "#229DE7",
      timer: 2000,
      toast: true,
      position: "top-end",
    });

    await loadRolePermissions(selectedRole.value.id);
  } catch (error) {
    console.error("Error saving permissions:", error);
    Swal.fire({
      icon: "error",
      title: "Failed",
      text: error?.response?.data?.message || "Could not save permissions.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    savingPermissions.value = false;
  }
};

const saveRole = async () => {
  roleErrors.name = !roleForm.name.trim();
  if (roleErrors.name) return;

  savingRole.value = true;
  try {
    const payload = {
      name: roleForm.name,
      description: roleForm.description,
    };

    let res;
    if (editingRole.value) {
      res = await RoleService.update(editingRole.value.id, payload);
    } else {
      res = await RoleService.create(payload);
    }

    Swal.fire({
      icon: "success",
      title: editingRole.value ? "Role Updated" : "Role Created",
      confirmButtonColor: "#229DE7",
      timer: 2000,
      toast: true,
      position: "top-end",
    });

    closeModal();
    await loadRoles();

    // Auto-select the new/updated role
    const updatedRole = res?.data?.data ?? res?.data;
    if (updatedRole) {
      selectRole(updatedRole);
    }
  } catch (error) {
    if (error?.response?.status === 422 && error?.response?.data?.errors?.name) {
      roleErrors.name = true;
    }
    Swal.fire({
      icon: "error",
      title: "Failed",
      text: error?.response?.data?.message || "Could not save role.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    savingRole.value = false;
  }
};

const deleteRole = async () => {
  if (!selectedRole.value) return;

  // Prevent deleting Admin role
  if (isAdminRole(selectedRole.value.name)) {
    Swal.fire({
      icon: "warning",
      title: "Action Not Allowed",
      text: "The Administrator role is system protected and cannot be deleted.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  const result = await Swal.fire({
    title: `Delete "${selectedRole.value.name}"?`,
    text: "This action cannot be undone. Staff members with this role will lose access.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#f43f5e",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Yes, delete",
  });

  if (!result.isConfirmed) return;

  try {
    await RoleService.delete(selectedRole.value.id);
    Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Role has been removed.",
      confirmButtonColor: "#229DE7",
      timer: 2000,
      toast: true,
      position: "top-end",
    });

    roles.value = roles.value.filter((r) => r.id !== selectedRole.value.id);
    selectedRole.value = roles.value[0] || null;
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Failed",
      text: error?.response?.data?.message || "Could not delete role.",
      confirmButtonColor: "#229DE7",
    });
  }
};

/* ─── UI METHODS ──────────────────────── */
const selectRole = (role) => {
  selectedRole.value = role;
};

const openCreateModal = () => {
  editingRole.value = null;
  roleForm.name = "";
  roleForm.description = "";
  roleErrors.name = false;
  showModal.value = true;
};

const openEditModal = () => {
  if (!selectedRole.value) return;

  // Prevent editing Admin role
  if (isAdminRole(selectedRole.value.name)) {
    Swal.fire({
      icon: "warning",
      title: "Action Not Allowed",
      text: "The Administrator role is system protected and cannot be edited.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  editingRole.value = selectedRole.value;
  roleForm.name = selectedRole.value.name;
  roleForm.description = selectedRole.value.description || "";
  roleErrors.name = false;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingRole.value = null;
  roleForm.name = "";
  roleForm.description = "";
  roleErrors.name = false;
};

/* ─── INIT ───────────────────────────── */
onMounted(async () => {
  await Promise.all([loadRoles(), loadPermissions()]);

  // Auto-select first role if available
  if (roles.value.length > 0 && !selectedRole.value) {
    selectRole(roles.value[0]);
  }
});
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

/* Custom checkbox animation */
.peer:checked + label {
  background-color: #229de7;
}

/* Modal animation */
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes zoom-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-in {
  animation-duration: 0.2s;
  animation-fill-mode: both;
}

.fade-in {
  animation-name: fade-in;
}

.zoom-in {
  animation-name: zoom-in;
}
</style>
