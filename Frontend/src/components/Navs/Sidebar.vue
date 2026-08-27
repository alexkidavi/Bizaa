<template>
  <div>
    <!-- MOBILE OVERLAY -->
    <transition name="overlay">
      <div
        v-if="mobileOpen"
        class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden"
        @click="$emit('close')"
      />
    </transition>

    <!-- SIDEBAR -->
    <aside
      :class="[
        'fixed lg:relative z-40 h-full bg-white border-r border-[#D9EBF7] flex flex-col transition-all duration-300 ease-in-out',
        collapsed ? 'lg:w-20' : 'lg:w-64',
        mobileOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- LOGO + TOGGLE -->
      <div
        class="p-4 flex items-center justify-between border-b border-[#EEF5FB] flex-shrink-0"
      >
        <div class="flex items-center gap-2 min-w-0">
          <div
            class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#229DE7] to-[#5BB8F5] flex items-center justify-center shadow-md flex-shrink-0"
          >
            <span class="text-white font-extrabold text-base">B</span>
          </div>
          <div v-if="showLabel" class="min-w-0">
            <div
              class="font-extrabold text-sm text-slate-900 leading-none whitespace-nowrap"
            >
              Bizaa
            </div>
            <div class="text-[9px] font-bold text-[#229DE7] uppercase tracking-widest">
              Inventory
            </div>
          </div>
        </div>
        <button
          @click="collapsed = !collapsed"
          class="hidden lg:flex w-7 h-7 rounded-lg items-center justify-center hover:bg-[#EAF5FD] transition group flex-shrink-0"
        >
          <ChevronDownIcon
            :class="[
              'w-4 h-4 text-slate-400 group-hover:text-[#229DE7] transform transition-all',
              collapsed ? 'rotate-90' : '-rotate-90',
            ]"
          />
        </button>
        <button
          @click="$emit('close')"
          class="lg:hidden flex w-7 h-7 rounded-lg items-center justify-center hover:bg-[#EAF5FD] transition flex-shrink-0"
        >
          <XMarkIcon class="w-4 h-4 text-slate-400" />
        </button>
      </div>

      <!-- NAVIGATION -->
      <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto scrollbar-theme">
        <!-- Dashboard -->
        <router-link v-if="showDashboard()" to="/dashboard" class="nav-item group">
          <HomeIcon class="nav-icon group-hover:text-[#229DE7]" />
          <span v-if="showLabel" class="nav-label">Dashboard</span>
        </router-link>

        <!-- Products -->
        <template v-if="showProducts()">
          <button
            @click="toggleDropdown('products')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.products }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <CubeIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Products</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.products ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.products" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in productsMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Stock -->
        <template v-if="showStock()">
          <button
            @click="toggleDropdown('stock')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.stock }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <ArchiveBoxIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Stock</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.stock ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.stock" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in stockMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Purchases -->
        <template v-if="showPurchases()">
          <button
            @click="toggleDropdown('purchases')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.purchases }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <ShoppingBagIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Purchases</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.purchases ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.purchases" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in purchasesMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Sales -->
        <template v-if="showSales()">
          <button
            @click="toggleDropdown('sales')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.sales }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <ShoppingCartIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Sales</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.sales ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.sales" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in salesMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Ecommerce -->
        <template v-if="showEcommerce()">
          <button
            @click="toggleDropdown('ecommerce')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.ecommerce }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <GlobeAltIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Ecommerce</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.ecommerce ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.ecommerce" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in ecommerceMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Expenses -->
        <template v-if="showExpenses()">
          <button
            @click="toggleDropdown('expenses')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.expenses }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <BanknotesIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Expenses</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.expenses ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.expenses" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in expensesMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Simple Links -->
        <template v-for="item in simpleLinks" :key="item.to">
          <router-link
            v-if="!item?.perm || can(item.perm)"
            :to="item.to"
            class="nav-item group"
            active-class="nav-item-active"
            @click="$emit('close')"
          >
            <component :is="item.icon" class="nav-icon group-hover:text-[#229DE7]" />
            <span v-if="showLabel" class="nav-label">{{ item.label }}</span>
          </router-link>
        </template>
        <!-- HRM -->
        <template v-if="showHRM()">
          <button
            @click="toggleDropdown('hrm')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.hrm }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <UserGroupIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">HRM</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.hrm ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.hrm" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in hrmMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- CRM -->
        <template v-if="showCRM()">
          <button
            @click="toggleDropdown('crm')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.crm }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <IdentificationIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">CRM</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.crm ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.crm" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in crmMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>

        <!-- Settings -->
        <template v-if="showSettings()">
          <button
            @click="toggleDropdown('settings')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.settings }"
          >
            <div class="flex items-center gap-2 flex-1 min-w-0">
              <Cog6ToothIcon class="nav-icon group-hover:text-[#229DE7]" />
              <span class="nav-label">Settings</span>
            </div>
            <ChevronDownIcon
              :class="[
                'w-3.5 h-3.5 flex-shrink-0 transform transition-transform text-slate-400',
                dropdown.settings ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.settings" class="ml-7 mt-0.5 space-y-0.5">
            <router-link
              v-for="item in settingMenu"
              :key="item.to"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
              @click="$emit('close')"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </template>
      </nav>

      <!-- SUPPORT LINK -->
      <div class="px-3 pb-1 flex-shrink-0">
        <router-link to="/support" class="nav-item group" active-class="nav-item-active" @click="$emit('close')">
          <ChatBubbleLeftEllipsisIcon class="nav-icon group-hover:text-[#229DE7]" />
          <span v-if="showLabel" class="nav-label">Support</span>
        </router-link>
      </div>

      <!-- SUBSCRIPTION BANNER + LINK -->
      <div class="px-3 pb-2 flex-shrink-0">
        <!-- Expired alert -->
        <router-link v-if="subExpired" to="/subscription" @click="$emit('close')"
          class="flex items-center gap-2 w-full px-3 py-2.5 rounded-xl bg-red-50 border border-red-200 hover:bg-red-100 transition group mb-1">
          <ExclamationTriangleIcon class="w-4 h-4 text-red-500 flex-shrink-0" />
          <span v-if="showLabel" class="text-xs font-bold text-red-600 leading-tight">Subscription expired</span>
        </router-link>

        <!-- Warning (≤5 days) -->
        <router-link v-else-if="subWarning" to="/subscription" @click="$emit('close')"
          class="flex items-center gap-2 w-full px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 transition mb-1">
          <ExclamationTriangleIcon class="w-4 h-4 text-amber-500 flex-shrink-0" />
          <span v-if="showLabel" class="text-xs font-bold text-amber-600 leading-tight">{{ subDays }} day{{ subDays === 1 ? '' : 's' }} left</span>
        </router-link>

        <!-- Normal subscription link -->
        <router-link v-else to="/subscription" class="nav-item group" active-class="nav-item-active" @click="$emit('close')">
          <StarIcon class="nav-icon group-hover:text-[#229DE7]" />
          <span v-if="showLabel" class="nav-label">Subscription</span>
          <span v-if="showLabel && sub?.status === 'trial'"
            class="ml-auto text-[9px] font-bold bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full whitespace-nowrap">
            Trial
          </span>
        </router-link>
      </div>

      <!-- LOGOUT -->
      <div class="p-3 border-t border-[#EEF5FB] flex-shrink-0">
        <button
          @click="logout"
          class="cursor-pointer flex items-center gap-2 w-full px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 shadow-sm transition-all group"
        >
          <ArrowRightOnRectangleIcon
            class="w-4 h-4 flex-shrink-0 group-hover:scale-110 transition-transform"
          />
          <span v-if="showLabel" class="font-bold text-sm">Logout</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import {
  HomeIcon,
  CubeIcon,
  ArchiveBoxIcon,
  ShoppingBagIcon,
  ShoppingCartIcon,
  UsersIcon,
  UserGroupIcon,
  IdentificationIcon,
  ChartBarIcon,
  BanknotesIcon,
  Cog6ToothIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
  XMarkIcon,
  StarIcon,
  ExclamationTriangleIcon,
  ChatBubbleLeftEllipsisIcon,
  GlobeAltIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  mobileOpen: { type: Boolean, default: false },
});
defineEmits(["close"]);

const router = useRouter();
const auth = useAuthStore();

const sub = computed(() => auth.subscription);
const subDays = computed(() => sub.value?.days_remaining ?? 0);
const subExpired = computed(() => sub.value && !sub.value.is_active);
const subWarning = computed(() => sub.value?.is_active && subDays.value <= 5);

const collapsed = ref(false);

const dropdown = reactive({
  products: false,
  stock: false,
  purchases: false,
  sales: false,
  expenses: false,
  ecommerce: false,
  hrm: false,
  crm: false,
  settings: false,
});

const showLabel = computed(() => !collapsed.value || props.mobileOpen);

const toggleDropdown = (menu) => {
  dropdown[menu] = !dropdown[menu];
};

const logout = () => {
  auth.clearToken();
  router.push("/login");
};

/* =========================
   PERMISSION HELPERS
========================= */
const can = (perm) => auth.hasPermission(perm);

const canAny = (perms) => auth.hasAnyPermission(perms);

/* =========================
   MENU PERMISSION GROUPS
========================= */

const showDashboard = () => can("dashboard.view");

const showProducts = () =>
  canAny(["products.view", "categories.manage", "brands.manage", "units.manage"]);

const showStock = () => canAny(["stock.take", "stock.transfer", "stock.view"]);

const showPurchases = () => can("purchases.manage");

const showSales = () => canAny(["sales.view", "sales.create"]);

const showExpenses = () => can("expenses.manage");

const showCustomers = () => can("customers.manage");

const showSuppliers = () => can("suppliers.manage");

const showReports = () => can("reports.view");

const showHRM = () =>
  canAny([
    "staff.manage",
    // "hr.departments",
    // "hr.shifts",
    // "hr.attendance",
    // "roles.manage",
  ]);

const showCRM = () => canAny(["crm.contacts", "crm.companies", "crm.tags", "crm.sources", "crm.deals", "crm.pipeline_settings", "crm.activities", "crm.campaigns", "crm.dashboard"]);

const showEcommerce = () => can("ecommerce.manage");

const showSettings = () => canAny(["business.update", "branches.view"]);

/* =========================
   MENUS
========================= */

const productsMenu = [
  { label: "Items List", to: "/products/list", perm: "products.view" },
  { label: "Units", to: "/products/units", perm: "units.manage" },
  { label: "Categories", to: "/products/categories", perm: "categories.manage" },
  { label: "Brands", to: "/products/brands", perm: "brands.manage" },
];

const stockMenu = [
  { label: "Stock Take", to: "/stock/take", perm: "stock.take" },
  { label: "Stock Transfer", to: "/stock/transfer", perm: "stock.transfer" },
];

const purchasesMenu = [
  { label: "Purchase Orders", to: "/purchases/list", perm: "purchases.manage" },
];

const salesMenu = [
  { label: "POS Window", to: "/sales/pos", perm: "sales.create" },

  { label: "Sales List", to: "/sales/list", perm: "sales.view" },

  { label: "Invoices", to: "/sales/invoices", perm: "invoices.view" },

  { label: "Quotations", to: "/sales/quotations", perm: "quotations.view" },
];

const expensesMenu = [
  { label: "Expense List", to: "/expenses/list" },
  { label: "Categories", to: "/expenses/categories" },
];

const hrmMenu = [
  { label: "Staff Profiles", to: "/hrm/employees", perm: "staff.manage" },
  { label: "Awards", to: "/hrm/awards", perm: "staff.manage" },
  { label: "Warnings", to: "/hrm/warnings", perm: "staff.manage" },
  { label: "Resignations", to: "/hrm/resignations", perm: "staff.manage" },
  { label: "Terminations", to: "/hrm/terminations", perm: "staff.manage" },
  { label: "Performance", to: "/hrm/performance", perm: "staff.manage" },
  { label: "Roles", to: "/roles/list", perm: "roles.manage" },
  { label: "Departments", to: "/hrm/departments", perm: "staff.manage" },
  { label: "Shifts", to: "/hrm/shifts", perm: "staff.manage" },
  { label: "Attendance", to: "/hrm/attendance", perm: "staff.manage" },
  { label: "Leave Types", to: "/hrm/leave-types", perm: "staff.manage" },
  { label: "Leaves", to: "/hrm/leaves", perm: "staff.manage" },
  { label: "Leave Balances", to: "/hrm/leave-balances", perm: "staff.manage" },
  { label: "Payroll", to: "/hrm/payroll", perm: "staff.manage" },
  { label: "Salary Advances", to: "/hrm/salary-advances", perm: "staff.manage" },
  { label: "Payroll Config", to: "/hrm/payroll/config", perm: "staff.manage" },
];

const crmMenu = [
  { label: "Dashboard", to: "/crm/dashboard", perm: "crm.dashboard" },
  { label: "My Tasks", to: "/crm/my-tasks", perm: "crm.activities" },
  { label: "Deals", to: "/crm/deals", perm: "crm.deals" },
  { label: "Contacts", to: "/crm/contacts", perm: "crm.contacts" },
  { label: "Companies", to: "/crm/companies", perm: "crm.companies" },
  { label: "Sources", to: "/crm/sources", perm: "crm.sources" },
  { label: "Tags", to: "/crm/tags", perm: "crm.tags" },
  { label: "Pipeline Stages", to: "/crm/pipeline-stages", perm: "crm.pipeline_settings" },
  { label: "Campaigns", to: "/crm/campaigns", perm: "crm.campaigns" },
];

const ecommerceMenu = [
  { label: "Stores", to: "/ecommerce/stores", perm: "ecommerce.manage" },
];

const settingMenu = [
  { label: "Settings", to: "/settings" },
  { label: "Branches", to: "/branches/list", perm: "branches.view" },
];

const simpleLinks = [
  {
    label: "Suppliers",
    to: "/suppliers/list",
    icon: UsersIcon,
    perm: "suppliers.manage",
  },
  {
    label: "Customers",
    to: "/customers/list",
    icon: UserGroupIcon,
    perm: "customers.manage",
  },
  { label: "Reports", to: "/reports/general", icon: ChartBarIcon, perm: "reports.view" },
  {
    label: "Transactions",
    to: "/reports/transactions",
    icon: BanknotesIcon,
    perm: "reports.view",
  },
];
</script>
<style scoped>
.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 12px;
  font-size: 13.5px;
  color: #475569;
  background: transparent;
  border: none;
  cursor: pointer;
  text-decoration: none;
  width: 100%;
  text-align: left;
  transition: background 0.15s ease, color 0.15s ease;
}
.nav-item:hover {
  background-color: #eaf5fd;
  color: #229de7;
}
.nav-item-active {
  background: linear-gradient(to right, #eaf5fd, #f5faff);
  color: #229de7;
  font-weight: 700;
  border-left: 2.5px solid #229de7;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.nav-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}
.nav-label {
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
}

.sub-nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 10px;
  border-radius: 8px;
  font-size: 12px;
  color: #64748b;
  text-decoration: none;
  transition: background 0.15s ease, color 0.15s ease;
}
.sub-nav-item:hover {
  background-color: #f5faff;
  color: #229de7;
}
.sub-nav-item-active {
  background-color: #f5faff;
  color: #229de7;
  font-weight: 700;
}

.scrollbar-theme::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-theme::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-theme::-webkit-scrollbar-thumb {
  background-color: #b3defa;
  border-radius: 4px;
}
.scrollbar-theme::-webkit-scrollbar-thumb:hover {
  background-color: #229de7;
}

.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.25s ease;
}
.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}
</style>
