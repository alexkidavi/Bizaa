<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Products</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your inventory, pricing, and stock levels in one place.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="exportCSV"
          class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-[#D9EBF7] rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4-4-4M12 14V4" />
          </svg>
          Export
        </button>

        <button
          @click="showImportModal = true"
          class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-[#D9EBF7] rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M8 8l4-4 4 4M12 4v12" />
          </svg>
          Import
        </button>

        <button
          @click="addProduct"
          class="cursor-pointer flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
          >
            <path d="M12 5v14M5 12h14" />
          </svg>
          Add Product
        </button>
      </div>
    </div>

    <!-- STATS ROW -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7">
      <div
        v-for="stat in [
          {
            label: 'Total Products',
            value: Number(stats.total_products).toLocaleString(),
            sub: 'Items in inventory',
            subClass: 'text-slate-400',
          },
          {
            label: 'Total Value',
            value: `KES ${formatCompactNumber(Number(stats.total_value))}`,
            sub: 'Inventory worth',
            subClass: 'text-emerald-600',
          },
          {
            label: 'Low Stock',
            value: stats.low_stock,
            sub: 'Needs restocking',
            subClass: 'text-amber-600',
          },
          {
            label: 'Categories',
            value: stats.categories,
            sub: 'Product groups',
            subClass: 'text-slate-400',
          },
        ]"
        :key="stat.label"
        class="bg-white border border-[#D9EBF7] rounded-2xl p-5 shadow-sm relative overflow-hidden"
      >
        <div
          class="absolute top-0 left-0 right-0 h-[3px] bg-[#229DE7] rounded-t-2xl opacity-60"
        ></div>
        <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400">
          {{ stat.label }}
        </div>
        <div class="text-2xl font-extrabold text-slate-900 mt-1 tracking-tight">
          {{ stat.value }}
        </div>
        <div class="text-[11px] font-semibold mt-1" :class="stat.subClass">
          {{ stat.sub }}
        </div>
      </div>
    </div>

    <!-- TOOLBAR -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
      <div class="relative flex-1">
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
          placeholder="Search by name, category, or barcode..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"
        />
      </div>
      <select
        v-model="branchFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm"
      >
        <option value="">All Branches</option>
        <option v-for="b in branches" :key="b.id" :value="b.id">
          {{ b.name }}
        </option>
      </select>

      <select
        v-model="categoryFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
          {{ cat.name }}
        </option>
      </select>
      <select
        v-model="stockFilter"
        class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 outline-none focus:border-[#229DE7] transition cursor-pointer"
      >
        <option value="">All Stock</option>
        <option value="low">Low Stock</option>
        <option value="ok">In Stock</option>
        <option value="out">Out of Stock</option>
      </select>
    </div>

    <!-- TABLE -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <!-- Loading skeleton -->
      <div v-if="loading" class="p-6 space-y-3">
        <div
          v-for="n in 5"
          :key="n"
          class="h-12 bg-slate-50 animate-pulse rounded-xl"
        ></div>
      </div>

      <!-- Empty state -->
      <div v-else-if="products.length === 0" class="py-20 text-center">
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
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
            />
          </svg>
        </div>
        <h3 class="font-bold text-base text-slate-900">No products found</h3>
        <p class="text-slate-500 text-sm mt-1">
          Adjust your filters or click "Add Product" to get started.
        </p>
      </div>

      <!-- Data table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
            >
              <th class="px-6 py-3.5">Product Info</th>
              <th class="px-6 py-3.5">Category</th>
              <th class="px-6 py-3.5">Brand</th>
              <th class="px-6 py-3.5">Pricing</th>
              <th class="px-6 py-3.5">Stock</th>
              <th class="px-6 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr
              v-for="p in products"
              :key="p.id"
              class="group hover:bg-[#F5FAFF] transition-colors"
            >
              <!-- Product Info -->
              <td class="px-6 py-3.5">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-bold text-base flex-shrink-0 group-hover:scale-105 transition-transform"
                  >
                    {{ p.name?.charAt(0).toUpperCase() ?? "?" }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ p.name }}</div>
                    <div class="text-[10.5px] font-mono text-slate-400 mt-0.5">
                      {{ p.product_code }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="px-6 py-3.5">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                >
                  {{ p.category }}
                </span>
              </td>

              <!-- Brand -->
              <td class="px-6 py-3.5 text-sm font-semibold text-slate-700">
                {{ p.brand }}
              </td>

              <!-- Pricing -->
              <td class="px-6 py-3.5">
                <div class="text-sm font-extrabold text-slate-900">
                  S.P KES {{ Number(p.selling_price).toLocaleString() }}
                </div>
                <div class="text-[10.5px] font-semibold text-slate-400 mt-0.5">
                  B.P KES {{ Number(p.buying_price).toLocaleString() }} ·
                  <span class="text-[#229DE7]">{{ margin(p) }}% margin</span>
                </div>
              </td>

              <!-- Stock -->
              <td class="px-6 py-3.5">
                <div class="flex items-center gap-2">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                    :class="stockBadge(p.current_stock).cls"
                  >
                    {{ stockBadge(p.current_stock).label }}
                  </span>
                  <div class="w-14 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :style="stockBadge(p.current_stock).bar"
                    ></div>
                  </div>
                  <span class="text-xs font-bold text-slate-400">{{
                    p.current_stock
                  }}</span>
                </div>
              </td>

              <!-- Actions -->
              <td class="px-6 py-3.5 text-right relative">
                <button
                  class="cursor-pointer w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 border border-transparent hover:bg-[#EAF5FD] hover:border-[#B3DEFA] hover:text-[#229DE7] transition"
                  @click.stop="toggleMenu(p.id)"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                    />
                  </svg>
                </button>

                <transition name="fade">
                  <div
                    v-if="activeMenu === p.id"
                    class="absolute right-8 top-12 w-44 bg-white border border-[#D9EBF7] rounded-2xl shadow-xl z-50 py-1.5 overflow-hidden"
                  >
                    <button
                      @click="viewProduct(p)"
                      class="cursor-pointer w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-[#EAF5FD] hover:text-[#1682C4] font-semibold transition"
                    >
                      View Details
                    </button>
                    <button
                      @click="editProduct(p)"
                      class="cursor-pointer w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-[#EAF5FD] hover:text-[#1682C4] font-semibold transition"
                    >
                      Edit Product
                    </button>
                    <div class="h-px bg-slate-100 my-1 mx-3"></div>
                    <button
                      @click="deleteProduct(p.id)"
                      class="cursor-pointer w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 font-bold transition"
                    >
                      Delete Product
                    </button>
                  </div>
                </transition>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="text-sm text-slate-500 font-semibold">
        Showing {{ products.length }} of {{ total }} products
      </div>
      <div class="flex items-center gap-2">
        <button
          class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 disabled:hover:bg-white disabled:hover:border-[#D9EBF7] disabled:hover:text-slate-600 transition shadow-sm active:scale-95"
          :disabled="page === 1"
          @click="page--"
        >
          ← Previous
        </button>
        <div
          class="flex items-center px-4 py-2 bg-[#229DE7] rounded-xl text-sm font-bold text-white shadow-md shadow-[#229DE7]/30"
        >
          {{ page }} / {{ totalPages }}
        </div>
        <button
          class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 disabled:hover:bg-white disabled:hover:border-[#D9EBF7] disabled:hover:text-slate-600 transition shadow-sm active:scale-95"
          :disabled="page === totalPages"
          @click="page++"
        >
          Next →
        </button>
      </div>
    </div>

    <ImportProductsModal
      v-if="showImportModal"
      :branches="branches"
      @close="showImportModal = false"
      @imported="handleImported"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import ProductService from "@/services/ProductService";
import BranchesService from "@/services/Branchesservice";
import CategoryService from "@/services/categoryService";
import ImportProductsModal from "./ImportProductsModal.vue";
const router = useRouter();

const showImportModal = ref(false);
const handleImported = () => {
  showImportModal.value = false;
  fetchProducts();
  fetchBranches();
};

/* ─── STATE ───────────────────────────── */
const products = ref([]);
const loading = ref(false);

const searchQuery = ref("");
const categoryFilter = ref("");
const stockFilter = ref("");

const page = ref(1);
const perPage = ref(10);
const totalPages = ref(1);
const total = ref(0);
const categories = ref([]);
const activeMenu = ref(null);
const backendStats = ref(null);
const branchFilter = ref("");
const branches = ref([]);
/* ─── FETCH DATA ──────────────────────── */
const fetchProducts = async () => {
  loading.value = true;

  try {
    const res = await ProductService.getAll({
      page: page.value,
      per_page: perPage.value,
      search: searchQuery.value,
      category: categoryFilter.value,
      stock: stockFilter.value,
      branch: branchFilter.value,
    });

    const data = res.data; // ✅ NO original anymore

    products.value = data.data || [];

    total.value = data.total || 0;
    totalPages.value = data.last_page || 1;

    stats.value = data.stats || {
      total_products: 0,
      total_value: 0,
      low_stock: 0,
      categories: 0,
    };
  } catch (err) {
    console.error("FAILED:", err);
    products.value = [];
  } finally {
    loading.value = false;
  }
};
function formatCompactNumber(num) {
  if (num >= 1_000_000_000) {
    return (num / 1_000_000_000).toFixed(1).replace(/\.0$/, "") + "B";
  }
  if (num >= 1_000_000) {
    return (num / 1_000_000).toFixed(1).replace(/\.0$/, "") + "M";
  }
  if (num >= 1_000) {
    return (num / 1_000).toFixed(1).replace(/\.0$/, "") + "K";
  }
  return num.toString();
}
const fetchBranches = async () => {
  try {
    const res = await BranchesService.getAllBranches();
    branches.value = res || [];
  } catch (e) {
    console.error("Branches load failed", e);
  }
};
const fetchCategories = async () => {
  try {
    const res = await CategoryService.getAll();
    categories.value = res.data || [];
  } catch (e) {
    console.error("Categories load failed", e);
  }
};
onMounted(() => {
  fetchProducts();
  fetchBranches();
  fetchCategories();
});
/* ─── WATCHERS (AUTO REFRESH) ─────────── */
watch(page, fetchProducts);

watch([searchQuery, categoryFilter, stockFilter, branchFilter], () => {
  page.value = 1;
  fetchProducts();
});

/* ─── STATS ───────────────────────────── */
const stats = ref({
  total_products: 0,
  total_value: 0,
  low_stock: 0,
  categories: 0,
});
/* ─── HELPERS ─────────────────────────── */
const margin = (p) =>
  Math.round(((p.selling_price - p.buying_price) / p.selling_price) * 100);

const stockBadge = (stock) => {
  stock = Number(stock); // decimal-cast backend field arrives as a string
  if (stock === 0)
    return {
      label: "Out of Stock",
      cls: "bg-red-50 text-red-600 border-red-200",
      bar: "width:0%;background:#E84545",
    };

  if (stock < 10)
    return {
      label: "Low Stock",
      cls: "bg-amber-50 text-amber-600 border-amber-200",
      bar: `width:${Math.round((stock / 10) * 40 + 10)}%;background:#CA8A04`,
    };

  return {
    label: "In Stock",
    cls: "bg-emerald-50 text-emerald-700 border-emerald-200",
    bar: `width:${Math.min(stock, 100)}%;background:#16A34A`,
  };
};

/* ─── MENU ─────────────────────────────── */
const toggleMenu = (id) => {
  activeMenu.value = activeMenu.value === id ? null : id;
};

const closeMenus = () => {
  activeMenu.value = null;
};

onMounted(() => document.addEventListener("click", closeMenus));
onBeforeUnmount(() => document.removeEventListener("click", closeMenus));

/* ─── NAVIGATION ───────────────────────── */
const addProduct = () => router.push("/products/add");
const editProduct = (p) => router.push(`/products/edit/${p.id}`);
const viewProduct = (p) => router.push(`/products/${p.id}`);

const deleteProduct = async (id) => {
  if (!confirm("Delete this product?")) return;

  try {
    await ProductService.delete(id);
    fetchProducts();
    fetchBranches();
  } catch {
    alert("Error deleting product.");
  }
};

/* ─── CSV EXPORT ───────────────────────── */
const exportCSV = () => {
  const headers = [
    "Name",
    "Code",
    "Category",
    "Brand",
    "Sell Price",
    "Buy Price",
    "Stock",
  ];

  const rows = products.value.map((p) => [
    p.name,
    p.product_code,
    p.category,
    p.brand,
    p.selling_price,
    p.buying_price,
    p.current_stock,
  ]);

  const csv =
    "data:text/csv;charset=utf-8," +
    [headers, ...rows].map((r) => r.join(",")).join("\n");

  const link = Object.assign(document.createElement("a"), {
    href: encodeURI(csv),
    download: `inventory_page_${page.value}.csv`,
  });

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

</script>
<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
/* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #eaf5fd;
  border-radius: 99px;
}

::-webkit-scrollbar-thumb {
  background: #229de7;
  border-radius: 99px;
  border: 2px solid #eaf5fd;
}

::-webkit-scrollbar-thumb:hover {
  background: #1682c4;
}

/* Firefox */
* {
  scrollbar-width: thin;
  scrollbar-color: #229de7 #eaf5fd;
}
</style>
