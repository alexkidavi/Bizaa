<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">

    <!-- HEADER -->
    <div class="flex items-center gap-3 mb-6">
      <router-link to="/ecommerce/stores" class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#D9EBF7] bg-white text-slate-500 hover:text-[#229DE7] hover:border-[#229DE7] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </router-link>
      <div class="flex-1 min-w-0">
        <h1 v-if="store" class="text-2xl font-extrabold tracking-tight text-slate-900 truncate">{{ store.name }}</h1>
        <div v-else class="h-7 w-48 bg-slate-200 rounded-lg animate-pulse"></div>
        <p class="text-slate-500 text-sm mt-0.5">Ecommerce Store — {{ store?.branch?.name ?? '…' }}</p>
      </div>
      <span v-if="store" :class="store.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="text-xs font-bold px-3 py-1 rounded-full">
        {{ store.is_active ? 'Active' : 'Inactive' }}
      </span>
    </div>

    <!-- TABS -->
    <div class="flex gap-1 bg-white border border-[#D9EBF7] rounded-xl p-1 w-fit mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="activeTab === tab.id ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30' : 'text-slate-600 hover:text-[#229DE7]'"
        class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- ── TAB: Products ────────────────────────────────── -->
    <div v-if="activeTab === 'products'">
      <!-- Toolbar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input v-model="search" type="text" placeholder="Search products…" class="pl-9 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] w-60"/>
          </div>
          <select v-model="filterListed" class="py-2.5 px-3 bg-white border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7]">
            <option value="all">All products</option>
            <option value="listed">Listed only</option>
            <option value="unlisted">Unlisted only</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <span v-if="selected.length" class="text-xs text-slate-500 font-semibold">{{ selected.length }} selected</span>
          <button v-if="selected.length" @click="bulkList(true)" class="px-3 py-2 bg-emerald-500 text-white text-xs font-bold rounded-lg hover:bg-emerald-600 transition">List Selected</button>
          <button v-if="selected.length" @click="bulkList(false)" class="px-3 py-2 bg-slate-500 text-white text-xs font-bold rounded-lg hover:bg-slate-600 transition">Unlist Selected</button>
        </div>
      </div>

      <!-- Products table -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <!-- Loading skeleton -->
        <div v-if="productsLoading" class="p-6 space-y-3">
          <div v-for="n in 6" :key="n" class="h-14 bg-slate-50 rounded-xl animate-pulse"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredProducts.length === 0" class="py-20 text-center">
          <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          <p class="text-slate-500 font-semibold">No products found</p>
          <p class="text-slate-400 text-sm mt-1">Products are loaded from the linked branch.</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                <th class="px-4 py-3.5 w-10">
                  <input type="checkbox" :checked="allSelected" @change="toggleAll" class="accent-[#229DE7] w-4 h-4"/>
                </th>
                <th class="px-4 py-3.5">Product</th>
                <th class="px-4 py-3.5">Category</th>
                <th class="px-4 py-3.5 text-right">Selling Price</th>
                <th class="px-4 py-3.5 text-center">Stock</th>
                <th class="px-4 py-3.5 text-center">Listed</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="p in filteredProducts" :key="p.product_branch_id" class="group hover:bg-[#F5FAFF] transition-colors">
                <td class="px-4 py-3.5">
                  <input type="checkbox" :value="p.product_branch_id" v-model="selected" class="accent-[#229DE7] w-4 h-4"/>
                </td>
                <td class="px-4 py-3.5">
                  <div class="flex items-center gap-3">
                    <div v-if="p.image" class="w-9 h-9 rounded-lg overflow-hidden border border-[#D9EBF7] flex-shrink-0">
                      <img :src="p.image" class="w-full h-full object-cover" alt=""/>
                    </div>
                    <div v-else class="w-9 h-9 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                      <p class="font-bold text-sm text-slate-900">{{ p.name }}</p>
                      <p class="text-xs text-slate-400 font-mono">{{ p.product_code }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3.5">
                  <span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full font-semibold">{{ p.category || '—' }}</span>
                </td>
                <td class="px-4 py-3.5 text-right">
                  <span class="text-sm font-bold text-slate-900">KES {{ Number(p.selling_price).toLocaleString() }}</span>
                </td>
                <td class="px-4 py-3.5 text-center">
                  <span :class="p.stock <= 0 ? 'bg-red-50 text-red-600' : p.stock <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'" class="text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ p.stock }}
                  </span>
                </td>
                <td class="px-4 py-3.5 text-center">
                  <button
                    @click="toggleListing(p)"
                    :disabled="toggling[p.product_branch_id]"
                    class="relative inline-flex cursor-pointer"
                    :title="p.is_listed ? 'Unlist from store' : 'List on store'"
                  >
                    <div :class="p.is_listed ? 'bg-[#229DE7]' : 'bg-slate-200'" class="w-10 h-5 rounded-full transition-colors"></div>
                    <div :class="p.is_listed ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></div>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Stats bar -->
        <div v-if="!productsLoading && products.length" class="px-5 py-3 border-t border-[#EEF5FB] flex items-center justify-between text-xs text-slate-500 font-semibold">
          <span>{{ listedCount }} of {{ products.length }} products listed</span>
          <div class="flex gap-3">
            <button @click="bulkListAll(true)" class="text-emerald-600 hover:underline">List all</button>
            <button @click="bulkListAll(false)" class="text-slate-500 hover:underline">Unlist all</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TAB: Settings ────────────────────────────────── -->
    <div v-if="activeTab === 'settings' && store">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Form panel -->
        <div class="lg:col-span-7">
          <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
            <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
            <div class="px-6 py-4 border-b border-[#EEF5FB]">
              <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Store Settings</h2>
            </div>
            <form @submit.prevent="saveSettings" class="p-6 space-y-4">

              <div>
                <label class="field-label required">Store Name</label>
                <input v-model="settings.name" type="text" class="field-input"/>
              </div>

              <div>
                <label class="field-label">URL Slug</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-mono">/store/</span>
                  <input v-model="settings.slug" type="text" class="field-input pl-14"/>
                </div>
              </div>

              <div>
                <label class="field-label">Tagline</label>
                <input v-model="settings.tagline" type="text" class="field-input" placeholder="Short compelling tagline"/>
              </div>

              <div>
                <label class="field-label">Category</label>
                <select v-model="settings.category" class="field-input">
                  <option value="">None</option>
                  <option v-for="c in storeCategories" :key="c.value" :value="c.value">{{ c.label }}</option>
                </select>
              </div>

              <div>
                <label class="field-label">Banner Color</label>
                <div class="flex items-center gap-3">
                  <input v-model="settings.banner_color" type="color" class="w-10 h-10 rounded-lg border border-[#D9EBF7] cursor-pointer p-0.5"/>
                  <input v-model="settings.banner_color" type="text" class="field-input flex-1" maxlength="7"/>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="field-label">Phone</label>
                  <input v-model="settings.phone" type="text" class="field-input"/>
                </div>
                <div>
                  <label class="field-label">Email</label>
                  <input v-model="settings.email" type="email" class="field-input"/>
                </div>
              </div>

              <div>
                <label class="field-label">Business Hours</label>
                <input v-model="settings.hours" type="text" class="field-input" placeholder="Mon–Sat: 8am–8pm"/>
              </div>

              <div>
                <label class="field-label">Description</label>
                <textarea v-model="settings.description" rows="4" class="field-input resize-none"></textarea>
              </div>

              <div class="flex gap-5">
                <label class="flex items-center gap-2 cursor-pointer">
                  <div class="relative">
                    <input v-model="settings.is_active" type="checkbox" class="sr-only peer"/>
                    <div class="w-10 h-5 bg-slate-200 peer-checked:bg-[#229DE7] rounded-full transition"></div>
                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                  </div>
                  <span class="text-xs font-semibold text-slate-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <div class="relative">
                    <input v-model="settings.is_featured" type="checkbox" class="sr-only peer"/>
                    <div class="w-10 h-5 bg-slate-200 peer-checked:bg-amber-400 rounded-full transition"></div>
                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                  </div>
                  <span class="text-xs font-semibold text-slate-700">Featured</span>
                </label>
              </div>

              <button type="submit" :disabled="settingsSaving" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60">
                <svg v-if="settingsSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                {{ settingsSaving ? 'Saving…' : 'Save Settings' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Preview card -->
        <div class="lg:col-span-5">
          <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
            <div class="px-5 py-3 border-b border-[#EEF5FB]">
              <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Preview</p>
            </div>
            <div>
              <!-- Mini banner -->
              <div class="h-20 relative" :style="{ background: `linear-gradient(135deg, ${settings.banner_color || '#1e3a5f'} 0%, #0f3460 100%)` }">
                <div class="absolute inset-0 bg-black/10"></div>
              </div>
              <div class="px-5 pb-5 pt-8 relative">
                <div class="absolute top-0 left-5 -translate-y-1/2 w-14 h-14 rounded-2xl border-[3px] border-white shadow-lg flex items-center justify-center font-extrabold text-xl text-white" :style="{ background: settings.banner_color || '#229DE7' }">
                  {{ settings.name?.charAt(0) ?? 'S' }}
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-1 truncate">{{ settings.name || 'Store Name' }}</h3>
                <p class="text-xs text-slate-500 truncate mt-0.5">{{ settings.tagline || 'Your tagline here' }}</p>
                <div class="flex items-center gap-2 mt-3 text-xs text-slate-500">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  {{ store?.branch?.location || store?.branch?.name || '—' }}
                </div>
                <div v-if="settings.description" class="mt-3 text-xs text-slate-600 line-clamp-3">{{ settings.description }}</div>
                <div class="flex gap-2 mt-4 text-xs text-slate-500 flex-wrap">
                  <span v-if="settings.phone" class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.69 12"/></svg>{{ settings.phone }}</span>
                  <span v-if="settings.hours" class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ settings.hours }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import EcommerceService from "@/services/EcommerceService";

const route = useRoute();
const storeId = computed(() => route.params.id);

const tabs = [
  { id: "products", label: "Products" },
  { id: "settings", label: "Settings" },
];
const activeTab = ref("products");

const store = ref(null);
const products = ref([]);
const productsLoading = ref(true);
const settingsSaving = ref(false);

const search = ref("");
const filterListed = ref("all");
const selected = ref([]);
const toggling = reactive({});

const storeCategories = [
  { value: "electronics", label: "Electronics" },
  { value: "fashion", label: "Fashion" },
  { value: "groceries", label: "Groceries" },
  { value: "hardware", label: "Hardware & Tools" },
  { value: "beauty", label: "Beauty & Health" },
  { value: "food", label: "Food & Drinks" },
  { value: "home", label: "Home & Living" },
  { value: "sports", label: "Sports & Fitness" },
  { value: "auto", label: "Auto Parts" },
  { value: "books", label: "Books" },
  { value: "other", label: "Other" },
];

const settings = reactive({
  name: "",
  slug: "",
  tagline: "",
  category: "",
  banner_color: "#1e3a5f",
  phone: "",
  email: "",
  hours: "",
  description: "",
  is_active: true,
  is_featured: false,
});

/* ── Load ────────────────────────────────── */
const loadStore = async () => {
  try {
    const res = await EcommerceService.getStore(storeId.value);
    store.value = res.data;
    Object.assign(settings, {
      name:         store.value.name ?? "",
      slug:         store.value.slug ?? "",
      tagline:      store.value.tagline ?? "",
      category:     store.value.category ?? "",
      banner_color: store.value.banner_color ?? "#1e3a5f",
      phone:        store.value.phone ?? "",
      email:        store.value.email ?? "",
      hours:        store.value.hours ?? "",
      description:  store.value.description ?? "",
      is_active:    store.value.is_active,
      is_featured:  store.value.is_featured,
    });
  } catch {
    Swal.fire({ icon: "error", title: "Store not found", confirmButtonColor: "#229DE7" });
  }
};

const loadProducts = async () => {
  productsLoading.value = true;
  try {
    const res = await EcommerceService.getStoreProducts(storeId.value);
    products.value = res.data ?? [];
  } catch {
    Swal.fire({ icon: "error", title: "Failed to load products", confirmButtonColor: "#229DE7" });
  } finally {
    productsLoading.value = false;
  }
};

onMounted(() => {
  loadStore();
  loadProducts();
});

/* ── Computed ────────────────────────────── */
const filteredProducts = computed(() => {
  let list = [...products.value];
  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(p => p.name?.toLowerCase().includes(q) || p.product_code?.toLowerCase().includes(q));
  }
  if (filterListed.value === "listed")   list = list.filter(p => p.is_listed);
  if (filterListed.value === "unlisted") list = list.filter(p => !p.is_listed);
  return list;
});

const listedCount = computed(() => products.value.filter(p => p.is_listed).length);

const allSelected = computed(() =>
  filteredProducts.value.length > 0 && filteredProducts.value.every(p => selected.value.includes(p.product_branch_id))
);

const toggleAll = (e) => {
  selected.value = e.target.checked ? filteredProducts.value.map(p => p.product_branch_id) : [];
};

/* ── Listing toggle ──────────────────────── */
const toggleListing = async (p) => {
  toggling[p.product_branch_id] = true;
  const newVal = !p.is_listed;
  p.is_listed = newVal;   // optimistic
  try {
    await EcommerceService.toggleProductListing(storeId.value, p.product_branch_id, newVal);
  } catch {
    p.is_listed = !newVal;  // revert
    Swal.fire({ icon: "error", title: "Toggle failed", toast: true, position: "top-end", showConfirmButton: false, timer: 2000 });
  } finally {
    toggling[p.product_branch_id] = false;
  }
};

const bulkList = async (isListed) => {
  if (!selected.value.length) return;
  try {
    await EcommerceService.bulkToggleListing(storeId.value, selected.value, isListed);
    products.value.forEach(p => {
      if (selected.value.includes(p.product_branch_id)) p.is_listed = isListed;
    });
    selected.value = [];
    Swal.fire({ icon: "success", title: isListed ? "Products listed" : "Products unlisted", toast: true, position: "top-end", showConfirmButton: false, timer: 1800, timerProgressBar: true });
  } catch {
    Swal.fire({ icon: "error", title: "Bulk operation failed", confirmButtonColor: "#229DE7" });
  }
};

const bulkListAll = async (isListed) => {
  const ids = products.value.map(p => p.product_branch_id);
  try {
    await EcommerceService.bulkToggleListing(storeId.value, ids, isListed);
    products.value.forEach(p => (p.is_listed = isListed));
    Swal.fire({ icon: "success", title: isListed ? "All products listed" : "All products unlisted", toast: true, position: "top-end", showConfirmButton: false, timer: 1800, timerProgressBar: true });
  } catch {
    Swal.fire({ icon: "error", title: "Operation failed", confirmButtonColor: "#229DE7" });
  }
};

/* ── Settings save ───────────────────────── */
const saveSettings = async () => {
  if (!settings.name.trim()) return;
  settingsSaving.value = true;
  try {
    const res = await EcommerceService.updateStore(storeId.value, settings);
    store.value = res.data;
    Swal.fire({ icon: "success", title: "Settings saved", toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
  } catch (err) {
    const msg = err?.response?.data?.message || "Save failed";
    Swal.fire({ icon: "error", title: "Error", text: msg, confirmButtonColor: "#229DE7" });
  } finally {
    settingsSaving.value = false;
  }
};
</script>

<style scoped>
.field-label {
  display: block; font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;
}
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input {
  width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7;
  border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none;
  transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit;
}
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,0.12); background: #fff; }
.line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>
