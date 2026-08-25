<template>

  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight">Brands</h1>
        <p class="text-slate-500 text-sm mt-1">Configure your global brand identities.</p>
      </div>

      <!-- SEARCH -->
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
          placeholder="Search brands..."
          class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7]"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm sticky top-6">
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>

          <div class="px-6 pt-5 pb-4 flex justify-between border-b border-[#EEF5FB]">
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
              {{ editingId ? "Edit Brand" : "Create Brand" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="text-xs text-slate-400 hover:text-red-500"
            >
              Cancel
            </button>
          </div>

          <form @submit.prevent="saveBrand" class="p-6 space-y-4">
            <!-- NAME -->
            <div>
              <label class="field-label required">Brand Name</label>
              <input
                v-model="form.name"
                class="field-input"
                :class="{ 'border-red-400': vErrors.name }"
              />
              <p v-if="vErrors.name" class="field-error">Required</p>
            </div>

            <!-- DESC -->
            <div>
              <label class="field-label">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="field-input"
              ></textarea>
            </div>

            <!-- BRANCH -->
            <div>
              <label class="field-label required">Branches</label>
              <Multiselect
                v-model="form.branches"
                :options="branches"
                :multiple="true"
                track-by="id"
                label="name"
                :class="{ 'ms-invalid': vErrors.branches }"
              />
              <p v-if="vErrors.branches" class="field-error">Select at least one</p>
            </div>

            <!-- BUTTON -->
            <button
              :disabled="loading"
              class="w-full py-3 bg-[#229DE7] text-white rounded-xl font-bold"
            >
              {{ loading ? "Processing..." : editingId ? "Update Brand" : "Save Brand" }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <!-- SKELETON -->
          <div v-if="pageLoading" class="p-6 space-y-3">
            <div v-for="n in 5" :key="n" class="h-14 bg-slate-50 animate-pulse rounded-xl"></div>
          </div>

          <!-- EMPTY -->
          <div
            v-else-if="filteredBrands.length === 0"
            class="py-20 text-center text-slate-500"
          >
            No brands found
          </div>

          <!-- TABLE -->
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-[#EAF5FD] text-[#1682C4] text-xs uppercase">
                <tr>
                  <th class="px-5 py-3">#</th>
                  <th class="px-5 py-3">Brand</th>
                  <th class="px-5 py-3">Description</th>
                  <th class="px-5 py-3">Branches</th>
                  <th class="px-5 py-3 text-right">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(brand, index) in paginatedBrands"
                  :key="brand.id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="px-5 py-3 text-xs text-slate-400">
                    {{ index + 1 }}
                  </td>

                  <td class="px-5 py-3">
                    <div class="flex gap-3">
                      <div
                        class="w-9 h-9 bg-[#EAF5FD] text-[#229DE7] rounded-xl flex items-center justify-center font-bold"
                      >
                        {{ brand.name.substring(0, 2).toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-bold">{{ brand.name }}</div>
                        <div class="text-xs text-slate-400">By Admin</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-3 text-xs text-slate-500 italic">
                    {{ brand.description || "No description" }}
                  </td>

                  <td class="px-5 py-3">
                    <span
                      v-for="b in brand.branches"
                      :key="b.id"
                      class="px-2 py-0.5 bg-[#EAF5FD] text-[#1682C4] rounded-full text-xs mr-1"
                    >
                      {{ b.name }}
                    </span>
                  </td>

                  <td class="px-5 py-3 text-right">
                    <button @click="editBrand(brand)">Edit</button>
                    <button @click="deleteBrand(brand.id)" class="text-red-500 ml-2">
                      Delete
                    </button>
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
import brandService from "@/services/brandService";
import BranchesService from "@/services/Branchesservice";
import Multiselect from "vue-multiselect";
import Swal from "sweetalert2";

const pageLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);

const brands = ref([]);
const branches = ref([]);

const searchQuery = ref("");
const currentPage = ref(1);
const itemsPerPage = 8;

const form = reactive({ name: "", description: "", branches: [] });
const vErrors = reactive({ name: false, branches: false });

const filteredBrands = computed(() =>
  brands.value.filter((b) =>
    b.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
);

const paginatedBrands = computed(() => filteredBrands.value);

const initData = async () => {
  const [b, br] = await Promise.all([
    brandService.getAll(),
    BranchesService.getAllBranches(),
  ]);
  brands.value = b.data || b;
  branches.value = br.data || br;
  pageLoading.value = false;
};

onMounted(initData);

const validate = () => {
  vErrors.name = !form.name;
  vErrors.branches = !form.branches.length;
  return !vErrors.name && !vErrors.branches;
};

const saveBrand = async () => {
  if (!validate()) return;
  loading.value = true;

  const payload = {
    name: form.name,
    description: form.description,
    branch_ids: form.branches.map((b) => b.id),
  };

  if (editingId.value) {
    await brandService.update(editingId.value, payload);
  } else {
    await brandService.create(payload);
  }

  const res = await brandService.getAll();
  brands.value = res.data || res;
  resetForm();
  loading.value = false;
};

const editBrand = (b) => {
  editingId.value = b.id;
  form.name = b.name;
  form.description = b.description;
  form.branches = [...b.branches];
};

const deleteBrand = async (id) => {
  await brandService.delete(id);
  brands.value = brands.value.filter((b) => b.id !== id);
};

const resetForm = () => {
  form.name = "";
  form.description = "";
  form.branches = [];
  editingId.value = null;
};
</script>

<style scoped>
.field-label {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 6px;
}
.field-label.required::after {
  content: " *";
  color: red;
}
.field-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #d9ebf7;
  border-radius: 12px;
}
.field-error {
  font-size: 11px;
  color: red;
}
</style>
