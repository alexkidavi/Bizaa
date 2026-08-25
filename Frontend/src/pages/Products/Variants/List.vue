<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Variants List</h1>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 bg-white rounded-xl shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(variant, index) in paginatedVariants" :key="variant.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 + (currentPage - 1) * pageSize }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ getProductName(variant.productId) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ variant.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ variant.sku }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ variant.price.toFixed(2) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-2">
              <button class="text-blue-600 hover:text-blue-800" @click="editVariant(variant)">Edit</button>
              <button class="text-red-600 hover:text-red-800" @click="deleteVariant(variant.id)">Delete</button>
            </td>
          </tr>
          <tr v-if="variants.length === 0">
            <td colspan="6" class="text-center text-gray-500 py-4">No variants found.</td>
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
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Dummy products
const products = [
  { id: 1, name: "Laptop" },
  { id: 2, name: "T-Shirt" },
  { id: 3, name: "Notebook" },
];

// Dummy variants
const variants = ref([
  { id: 1, productId: 1, name: "Laptop - 16GB RAM", sku: "LP16", price: 1200 },
  { id: 2, productId: 1, name: "Laptop - 32GB RAM", sku: "LP32", price: 1500 },
  { id: 3, productId: 2, name: "T-Shirt - Small", sku: "TS-S", price: 20 },
  { id: 4, productId: 2, name: "T-Shirt - Large", sku: "TS-L", price: 22 },
  { id: 5, productId: 3, name: "Notebook - 100 pages", sku: "NB100", price: 5 },
]);

// Pagination
const currentPage = ref(1);
const pageSize = ref(5);

const totalPages = computed(() => Math.ceil(variants.value.length / pageSize.value));

const paginatedVariants = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return variants.value.slice(start, end);
});

// Methods
const getProductName = (id) => products.find(p => p.id === id)?.name || "-";

const editVariant = (variant) => alert(`Edit ${variant.name}`);
const deleteVariant = (id) => {
  if (confirm("Are you sure you want to delete this variant?")) {
    variants.value = variants.value.filter(v => v.id !== id);
  }
};
</script>
