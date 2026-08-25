<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Ecommerce Stores</h1>
        <p class="text-slate-500 mt-1 text-sm">Create and manage your online stores. Each store is linked to a branch for real-time stock sync.</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Store
      </button>
    </div>

    <!-- LOADING SKELETON -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div v-for="n in 3" :key="n" class="h-52 bg-white rounded-2xl border border-[#D9EBF7] animate-pulse"></div>
    </div>

    <!-- EMPTY STATE -->
    <div v-else-if="stores.length === 0" class="flex flex-col items-center justify-center py-24 gap-4">
      <div class="w-20 h-20 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center">
        <svg class="w-10 h-10 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <h3 class="font-bold text-lg text-slate-900">No ecommerce stores yet</h3>
      <p class="text-slate-500 text-sm text-center max-w-sm">Create your first store and link it to a branch. Products from that branch will be available to list.</p>
      <button @click="openCreate" class="mt-2 px-6 py-2.5 bg-[#229DE7] text-white text-sm font-bold rounded-xl hover:bg-[#1682C4] transition">Create First Store</button>
    </div>

    <!-- STORES GRID -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div
        v-for="store in stores"
        :key="store.id"
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden hover:shadow-md hover:border-[#229DE7]/40 transition-all group"
      >
        <!-- Banner -->
        <div class="h-24 relative" :style="{ background: `linear-gradient(135deg, ${store.banner_color || '#1e3a5f'} 0%, ${adjustBannerColor(store.banner_color)} 100%)` }">
          <div class="absolute inset-0 bg-black/10"></div>
          <div class="absolute top-3 right-3 flex gap-1.5">
            <span v-if="store.is_featured" class="text-[10px] font-bold bg-amber-400 text-amber-900 px-2 py-0.5 rounded-full">Featured</span>
            <span :class="store.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="text-[10px] font-bold px-2 py-0.5 rounded-full">
              {{ store.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div class="absolute bottom-0 left-5 translate-y-1/2">
            <div class="w-14 h-14 rounded-2xl border-[3px] border-white shadow-lg flex items-center justify-center font-extrabold text-xl text-white" :style="{ background: store.banner_color || '#229DE7' }">
              {{ store.name?.charAt(0) ?? 'S' }}
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="pt-9 px-5 pb-5">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="font-extrabold text-base text-slate-900 truncate">{{ store.name }}</h3>
              <p v-if="store.tagline" class="text-xs text-slate-500 truncate mt-0.5">{{ store.tagline }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 mt-3 text-xs text-slate-500 flex-wrap">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5 text-[#229DE7] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
              {{ store.branch?.name ?? '—' }}
            </span>
            <span v-if="store.category" class="bg-[#EAF5FD] text-[#1682C4] px-2 py-0.5 rounded-full font-semibold capitalize">{{ store.category }}</span>
          </div>

          <div class="flex items-center gap-2 mt-4">
            <router-link
              :to="`/ecommerce/stores/${store.id}`"
              class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold bg-[#EAF5FD] text-[#229DE7] hover:bg-[#229DE7] hover:text-white transition"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              Manage
            </router-link>
            <button @click="openEdit(store)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#D9EBF7] text-[#229DE7] hover:bg-[#EAF5FD] transition">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            <button @click="confirmDelete(store)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-transparent text-red-400 hover:bg-red-50 hover:border-red-200 transition">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── CREATE / EDIT MODAL ────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 py-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              {{ editingStore ? 'Edit Store' : 'Create Ecommerce Store' }}
            </h2>
            <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <form @submit.prevent="submitStore" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">

            <!-- Branch (create only) -->
            <div v-if="!editingStore">
              <label class="field-label required">Link to Branch</label>
              <select v-model="form.branch_id" class="field-input" :class="{ 'border-red-400': errors.branch_id }">
                <option value="">Select a branch…</option>
                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
              </select>
              <p v-if="errors.branch_id" class="field-error">Branch is required</p>
              <p class="text-xs text-slate-400 mt-1">Stock will sync from the selected branch.</p>
            </div>
            <div v-else class="p-3 bg-[#EAF5FD] rounded-xl text-xs font-semibold text-[#1682C4] flex items-center gap-2">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
              Linked branch: <strong>{{ editingStore.branch?.name }}</strong>
            </div>

            <!-- Name -->
            <div>
              <label class="field-label required">Store Name</label>
              <input v-model="form.name" @input="autoSlug" type="text" class="field-input" :class="{ 'border-red-400': errors.name }" placeholder="e.g. TechHub Kenya"/>
              <p v-if="errors.name" class="field-error">Store name is required</p>
            </div>

            <!-- Slug -->
            <div>
              <label class="field-label">URL Slug</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-mono">/store/</span>
                <input v-model="form.slug" type="text" class="field-input pl-14" placeholder="techhub-kenya"/>
              </div>
              <p class="text-xs text-slate-400 mt-1">Auto-generated from name. Must be unique.</p>
            </div>

            <!-- Tagline -->
            <div>
              <label class="field-label">Tagline</label>
              <input v-model="form.tagline" type="text" class="field-input" placeholder="Your one-stop tech destination"/>
            </div>

            <!-- Category -->
            <div>
              <label class="field-label">Category</label>
              <select v-model="form.category" class="field-input">
                <option value="">None</option>
                <option v-for="c in storeCategories" :key="c.value" :value="c.value">{{ c.label }}</option>
              </select>
            </div>

            <!-- Banner Colour -->
            <div>
              <label class="field-label">Banner Color</label>
              <div class="flex items-center gap-3">
                <input v-model="form.banner_color" type="color" class="w-10 h-10 rounded-lg border border-[#D9EBF7] cursor-pointer p-0.5"/>
                <input v-model="form.banner_color" type="text" class="field-input flex-1" placeholder="#1e3a5f" maxlength="7"/>
              </div>
            </div>

            <!-- Phone + Email -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Phone</label>
                <input v-model="form.phone" type="text" class="field-input" placeholder="+254 700 123 456"/>
              </div>
              <div>
                <label class="field-label">Email</label>
                <input v-model="form.email" type="email" class="field-input" placeholder="store@example.co.ke"/>
              </div>
            </div>

            <!-- Hours -->
            <div>
              <label class="field-label">Business Hours</label>
              <input v-model="form.hours" type="text" class="field-input" placeholder="Mon–Sat: 8am–8pm, Sun: 10am–6pm"/>
            </div>

            <!-- Description -->
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="form.description" rows="3" class="field-input resize-none" placeholder="Describe your store…"></textarea>
            </div>

            <!-- Toggles -->
            <div class="flex gap-4">
              <label class="flex items-center gap-2 cursor-pointer">
                <div class="relative">
                  <input v-model="form.is_active" type="checkbox" class="sr-only peer"/>
                  <div class="w-10 h-5 bg-slate-200 peer-checked:bg-[#229DE7] rounded-full transition"></div>
                  <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                </div>
                <span class="text-xs font-semibold text-slate-700">Active</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <div class="relative">
                  <input v-model="form.is_featured" type="checkbox" class="sr-only peer"/>
                  <div class="w-10 h-5 bg-slate-200 peer-checked:bg-amber-400 rounded-full transition"></div>
                  <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                </div>
                <span class="text-xs font-semibold text-slate-700">Featured</span>
              </label>
            </div>

            <button type="submit" :disabled="saving" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60">
              <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ saving ? (editingStore ? 'Updating…' : 'Creating…') : (editingStore ? 'Update Store' : 'Create Store') }}
            </button>
          </form>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import EcommerceService from "@/services/EcommerceService";
import BranchesService from "@/services/Branchesservice";
import { slugify } from "@/utils/slugify";

const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const stores = ref([]);
const branches = ref([]);
const showModal = ref(false);
const editingStore = ref(null);

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

const defaultForm = () => ({
  branch_id: "",
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

const form = reactive(defaultForm());
const errors = reactive({ branch_id: false, name: false });

/* ── Data loading ──────────────────────────── */
const loadStores = async () => {
  loading.value = true;
  try {
    const res = await EcommerceService.getStores();
    stores.value = res.data ?? [];
  } catch {
    Swal.fire({ icon: "error", title: "Failed to load stores", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const loadBranches = async () => {
  try {
    const res = await BranchesService.getAllBranches();
    branches.value = res?.data?.data ?? res?.data ?? res ?? [];
  } catch { /* silent */ }
};

onMounted(() => Promise.all([loadStores(), loadBranches()]));

/* ── Slug auto-generate ──────────────────── */
const autoSlug = () => {
  if (!editingStore.value) {
    form.slug = slugify(form.name);
  }
};

/* ── Modal ───────────────────────────────── */
const openCreate = () => {
  editingStore.value = null;
  Object.assign(form, defaultForm());
  errors.branch_id = false;
  errors.name = false;
  showModal.value = true;
};

const openEdit = (store) => {
  editingStore.value = store;
  Object.assign(form, {
    branch_id: store.branch_id,
    name: store.name,
    slug: store.slug,
    tagline: store.tagline ?? "",
    category: store.category ?? "",
    banner_color: store.banner_color ?? "#1e3a5f",
    phone: store.phone ?? "",
    email: store.email ?? "",
    hours: store.hours ?? "",
    description: store.description ?? "",
    is_active: store.is_active,
    is_featured: store.is_featured,
  });
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingStore.value = null;
};

/* ── Submit ──────────────────────────────── */
const submitStore = async () => {
  errors.branch_id = !editingStore.value && !form.branch_id;
  errors.name = !form.name.trim();
  if (errors.branch_id || errors.name) return;

  saving.value = true;
  try {
    if (editingStore.value) {
      await EcommerceService.updateStore(editingStore.value.id, form);
    } else {
      await EcommerceService.createStore(form);
    }
    closeModal();
    await loadStores();
    Swal.fire({ icon: "success", title: editingStore.value ? "Store updated" : "Store created", toast: true, position: "top-end", showConfirmButton: false, timer: 2500, timerProgressBar: true });
  } catch (err) {
    const msg = err?.response?.data?.message || "Operation failed";
    Swal.fire({ icon: "error", title: "Error", text: msg, confirmButtonColor: "#229DE7" });
  } finally {
    saving.value = false;
  }
};

/* ── Delete ──────────────────────────────── */
const confirmDelete = async (store) => {
  const res = await Swal.fire({
    title: `Remove "${store.name}"?`,
    text: "Products will not be deleted, but this store will be removed.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, remove",
  });
  if (!res.isConfirmed) return;
  try {
    await EcommerceService.deleteStore(store.id);
    stores.value = stores.value.filter(s => s.id !== store.id);
    Swal.fire({ icon: "success", title: "Store removed", toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
  } catch {
    Swal.fire({ icon: "error", title: "Delete failed", confirmButtonColor: "#229DE7" });
  }
};

/* ── Banner colour helper ─────────────────── */
const adjustBannerColor = (hex) => {
  if (!hex || !hex.startsWith('#')) return '#0f3460';
  try {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    const darken = (v) => Math.max(0, Math.round(v * 0.65)).toString(16).padStart(2, '0');
    return `#${darken(r)}${darken(g)}${darken(b)}`;
  } catch { return '#0f3460'; }
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
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
