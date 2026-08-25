<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Print Labels</h1>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
      <!-- Left: Select Products / Variants for Labels -->
      <div class="bg-white rounded-xl shadow p-6 flex-none w-full lg:w-1/4">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Select Items</h2>
        <form @submit.prevent="addLabel" class="space-y-4">
          <div>
            <label class="block text-gray-600 mb-1">Product</label>
            <select v-model="selectedProductId" class="form-control">
              <option value="">Select Product</option>
              <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Variant (optional)</label>
            <select v-model="selectedVariantId" class="form-control">
              <option value="">Select Variant</option>
              <option
                v-for="v in filteredVariants"
                :key="v.id"
                :value="v.id"
              >
                {{ v.name }} ({{ v.sku }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-gray-600 mb-1">Quantity</label>
            <input
              type="number"
              v-model.number="labelQuantity"
              min="1"
              class="form-control"
              placeholder="Enter quantity"
            />
          </div>

          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
          >
            Add to Print List
          </button>
        </form>
      </div>

      <!-- Right: Labels Preview -->
      <div class="bg-white rounded-xl shadow p-6 flex-1 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-20">
          <h2 class="text-xl font-semibold text-gray-700">Labels List</h2>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search labels..."
            class="form-control w-10"
          />
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="(label, index) in paginatedLabels" :key="label.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ index + 1 + (currentPage - 1) * pageSize }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ label.productName }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ label.variantName || "-" }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ label.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-2">
                  <button class="text-red-600 hover:text-red-800" @click="removeLabel(label.id)">Remove</button>
                </td>
              </tr>
              <tr v-if="filteredLabels.length === 0">
                <td colspan="5" class="text-center text-gray-500 py-4">No labels added.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-end gap-2 items-center mt-4">
          <button
            class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            Prev
          </button>
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
          <button
            class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            Next
          </button>
        </div>

        <div class="mt-4">
          <button
            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition"
            @click="printLabels"
            :disabled="labels.length === 0"
          >
            Print Labels
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Dummy products & variants
const products = ref([
  { id: 1, name: "Laptop" },
  { id: 2, name: "T-Shirt" },
  { id: 3, name: "Notebook" },
]);

const variants = ref([
  { id: 1, productId: 1, name: "Laptop - 16GB RAM", sku: "LP16" },
  { id: 2, productId: 1, name: "Laptop - 32GB RAM", sku: "LP32" },
  { id: 3, productId: 2, name: "T-Shirt - Small", sku: "TS-S" },
  { id: 4, productId: 2, name: "T-Shirt - Large", sku: "TS-L" },
  { id: 5, productId: 3, name: "Notebook - 100 pages", sku: "NB100" },
]);

// Form fields
const selectedProductId = ref("");
const selectedVariantId = ref("");
const labelQuantity = ref(1);

// Labels list
const labels = ref([]);

// Pagination & search
const searchQuery = ref("");
const currentPage = ref(1);
const pageSize = ref(5);

const filteredLabels = computed(() => {
  if (!searchQuery.value) return labels.value;
  return labels.value.filter(l =>
    l.productName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (l.variantName && l.variantName.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
});

const totalPages = computed(() => Math.ceil(filteredLabels.value.length / pageSize.value));

const paginatedLabels = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLabels.value.slice(start, end);
});

// Filter variants based on selected product
const filteredVariants = computed(() => {
  if (!selectedProductId.value) return [];
  return variants.value.filter(v => v.productId === selectedProductId.value);
});

// Methods
const addLabel = () => {
  if (!selectedProductId.value || labelQuantity.value < 1) return;

  const product = products.value.find(p => p.id === selectedProductId.value);
  const variant = variants.value.find(v => v.id === selectedVariantId.value);

  const newLabel = {
    id: labels.value.length ? labels.value[labels.value.length - 1].id + 1 : 1,
    productName: product.name,
    variantName: variant ? variant.name : "",
    quantity: labelQuantity.value,
  };
  labels.value.push(newLabel);

  // Reset form
  selectedProductId.value = "";
  selectedVariantId.value = "";
  labelQuantity.value = 1;
};

const removeLabel = (id) => {
  labels.value = labels.value.filter(l => l.id !== id);
};

const printLabels = () => {
  alert(`Printing ${labels.value.length} labels (mock)`); 
};
</script>

<style>
.form-control {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-clip: padding-box;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 0.313rem;
  color: #6c757d;
  display: block;
  font-size: 0.875rem;
  font-weight: 400;
  line-height: 1.5;
  padding: 0.688rem 0.938rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  width: 100%;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}
</style>
