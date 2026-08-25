<template>
  <div class="flex h-screen">
    <!-- SIDEBAR -->
    <aside
      :class="[
        'bg-white border-r border-[#D9EBF7] flex flex-col transition-all duration-300',
        collapsed ? 'w-20' : 'w-64',
      ]"
    >
      <!-- LOGO + TOGGLE -->
      <div class="p-5 flex items-center justify-between border-b border-[#EEF5FB]">
        <div v-if="!collapsed" class="flex items-center gap-2">
          <div
            class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#229DE7] to-[#5BB8F5] flex items-center justify-center shadow-md"
          >
            <span class="text-white font-extrabold text-base">B</span>
          </div>
          <div>
            <div class="font-extrabold text-sm text-slate-900 leading-none">Bizaa</div>
            <div class="text-[9px] font-bold text-[#229DE7] uppercase tracking-widest">
              Inventory
            </div>
          </div>
        </div>
        <button
          @click="collapsed = !collapsed"
          class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-[#EAF5FD] transition group"
        >
          <ChevronDownIcon
            :class="[
              'w-4 h-4 text-slate-400 group-hover:text-[#229DE7] transform transition-all',
              collapsed ? 'rotate-90' : '-rotate-90',
            ]"
          />
        </button>
      </div>

      <!-- NAVIGATION -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-theme">
        <!-- Dashboard -->
        <router-link
          to="/dashboard"
          class="nav-item group"
          active-class="nav-item-active"
        >
          <HomeIcon class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]" />
          <span v-if="!collapsed" class="font-semibold">Dashboard</span>
        </router-link>

        <!-- Products -->
        <div>
          <button
            @click="toggleDropdown('products')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.products }"
          >
            <div class="flex items-center gap-2 flex-1">
              <CubeIcon class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]" />
              <span v-if="!collapsed" class="font-semibold">Products</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.products ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.products && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in productsMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Stock -->
        <div>
          <button
            @click="toggleDropdown('stock')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.stock }"
          >
            <div class="flex items-center gap-2 flex-1">
              <ArchiveBoxIcon class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]" />
              <span v-if="!collapsed" class="font-semibold">Stock</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.stock ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.stock && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in stockMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Purchases -->
        <div>
          <button
            @click="toggleDropdown('purchases')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.purchases }"
          >
            <div class="flex items-center gap-2 flex-1">
              <ShoppingBagIcon class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]" />
              <span v-if="!collapsed" class="font-semibold">Purchases</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.purchases ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.purchases && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in purchasesMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Suppliers -->

        <!-- Sales -->
        <div>
          <button
            @click="toggleDropdown('sales')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.sales }"
          >
            <div class="flex items-center gap-2 flex-1">
              <ShoppingCartIcon
                class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]"
              />
              <span v-if="!collapsed" class="font-semibold">Sales</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.sales ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.sales && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in salesMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Accounting -->
        <div>
          <button
            @click="toggleDropdown('accounting')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.accounting }"
          >
            <div class="flex items-center gap-2 flex-1">
              <CurrencyDollarIcon
                class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]"
              />
              <span v-if="!collapsed" class="font-semibold">Accounting</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.accounting ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.accounting && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in accountingMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Simple Links -->
        <router-link
          v-for="(item, index) in simpleLinks"
          :key="index"
          :to="item.to"
          class="nav-item group"
          active-class="nav-item-active"
        >
          <component
            :is="item.icon"
            class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]"
          />
          <span v-if="!collapsed" class="font-semibold">{{ item.label }}</span>
        </router-link>

        <!-- Settings -->
        <div>
          <button
            @click="toggleDropdown('settings')"
            class="nav-item group w-full"
            :class="{ 'bg-[#F5FAFF] text-[#229DE7]': dropdown.settings }"
          >
            <div class="flex items-center gap-2 flex-1">
              <Cog6ToothIcon class="w-4 h-4 flex-shrink-0 group-hover:text-[#229DE7]" />
              <span v-if="!collapsed" class="font-semibold">Settings</span>
            </div>
            <ChevronDownIcon
              v-if="!collapsed"
              :class="[
                'w-3.5 h-3.5 transform transition-transform',
                dropdown.settings ? 'rotate-180' : '',
              ]"
            />
          </button>
          <div v-show="dropdown.settings && !collapsed" class="ml-7 mt-1 space-y-0.5">
            <router-link
              v-for="(item, index) in settingMenu"
              :key="index"
              :to="item.to"
              class="sub-nav-item"
              active-class="sub-nav-item-active"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#229DE7] flex-shrink-0"></span>
              <span>{{ item.label }}</span>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- LOGOUT BUTTON -->
      <div class="p-4 border-t border-[#EEF5FB]">
        <button
          @click="logout"
          class="flex items-center gap-2 w-full px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 shadow-sm transition-all group"
        >
          <LockClosedIcon
            class="w-4 h-4 flex-shrink-0 group-hover:scale-110 transition-transform"
          />
          <span v-if="!collapsed" class="font-bold text-sm">Logout</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import {
  HomeIcon,
  ShoppingCartIcon,
  CubeIcon,
  ArchiveBoxIcon,
  ShoppingBagIcon,
  UsersIcon,
  UserGroupIcon,
  UserIcon,
  ChartBarIcon,
  CurrencyDollarIcon,
  BanknotesIcon,
  Cog6ToothIcon,
  ChevronDownIcon,
  LockClosedIcon,
} from "@heroicons/vue/24/outline";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const auth = useAuthStore();
const collapsed = ref(false);

const dropdown = reactive({
  products: false,
  stock: false,
  purchases: false,
  suppliers: false,
  sales: false,
  accounting: false,
  settings: false,
});

const toggleDropdown = (menu) => {
  dropdown[menu] = !dropdown[menu];
};

/* ─── MENU DATA ───────────────────────── */
const productsMenu = [
  { label: "Items List", to: "/products/list" },
  { label: "Units", to: "/products/units" },
  { label: "Categories", to: "/products/categories" },
  { label: "Brands", to: "/products/brands" },
];

const stockMenu = [
  { label: "Stock Take", to: "/stock/take" },
  { label: "Stock Transfer", to: "/stock/transfer" },
];

const purchasesMenu = [{ label: "Purchase Orders", to: "/purchases/list" }];

const salesMenu = [
  { label: "Pos Window", to: "/sales/pos" },
  { label: "Sales List", to: "/sales/list" },
  { label: "Invoices", to: "/sales/invoices" },
  { label: "Quotations", to: "/sales/quotations" },
  // { label: "Sales Return", to: "/sales/returns" },
];

const accountingMenu = [
  { label: "Accounts", to: "/accounting/accounts" },
  { label: "Journal Entries", to: "/accounting/journal" },
  { label: "Trial Balance", to: "/accounting/trial-balance" },
  { label: "P&L Statement", to: "/accounting/pl" },
  { label: "Balance Sheet", to: "/accounting/balance-sheet" },
];

const settingMenu = [
  { label: "Company Settings", to: "/settings/business" },
  { label: "Branches", to: "/branches/list" },
];

const simpleLinks = [
  { label: "Suppliers", to: "/suppliers/list", icon: UsersIcon },
  { label: "Customers", to: "/customers/list", icon: UserGroupIcon },
  { label: "Staff", to: "/staff/list", icon: UserIcon },
  { label: "Reports", to: "/reports", icon: ChartBarIcon },
  { label: "Transactions", to: "/finance", icon: BanknotesIcon },
];

const logout = () => {
  auth.clearToken();
  router.push("/login");
};
</script>

<style scoped>
/* ─── NAV ITEMS ───────────────────────── */
.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
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
  border-left: 2px solid #229de7;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* ─── SUB NAV ITEMS ───────────────────── */
.sub-nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
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

/* ─── SCROLLBAR ───────────────────────── */
.scrollbar-theme::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-theme::-webkit-scrollbar-track {
  background: #f0f6fb;
  border-radius: 4px;
}
.scrollbar-theme::-webkit-scrollbar-thumb {
  background-color: #b3defa;
  border-radius: 4px;
}
.scrollbar-theme::-webkit-scrollbar-thumb:hover {
  background-color: #229de7;
}
</style>
