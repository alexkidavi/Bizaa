<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Add New Variant</h1>

    <form @submit.prevent="addVariant" class="bg-white rounded-xl shadow p-6 space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
        <select v-model="selectedProductId" class="form-control">
          <option value="">Select Product</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Variant Name</label>
        <input type="text" v-model="variantName" placeholder="e.g., Red / Large" class="form-control" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
        <input type="text" v-model="variantSKU" placeholder="SKU" class="form-control" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
        <input type="number" v-model.number="variantPrice" step="0.01" placeholder="0.00" class="form-control" />
      </div>

      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
        Add Variant
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";

// Dummy products
const products = ref([
  { id: 1, name: "Laptop" },
  { id: 2, name: "T-Shirt" },
  { id: 3, name: "Notebook" },
]);

// Form fields
const selectedProductId = ref("");
const variantName = ref("");
const variantSKU = ref("");
const variantPrice = ref(0);

// Dummy variants list (to be shared with List.vue)
const variants = ref([
  { id: 1, productId: 1, name: "Laptop - 16GB RAM", sku: "LP16", price: 1200 },
  { id: 2, productId: 1, name: "Laptop - 32GB RAM", sku: "LP32", price: 1500 },
  { id: 3, productId: 2, name: "T-Shirt - Small", sku: "TS-S", price: 20 },
  { id: 4, productId: 2, name: "T-Shirt - Large", sku: "TS-L", price: 22 },
  { id: 5, productId: 3, name: "Notebook - 100 pages", sku: "NB100", price: 5 },
]);

const addVariant = () => {
  if (!selectedProductId.value || !variantName.value) return;

  const newVariant = {
    id: variants.value.length ? variants.value[variants.value.length - 1].id + 1 : 1,
    productId: selectedProductId.value,
    name: variantName.value,
    sku: variantSKU.value,
    price: variantPrice.value,
  };

  variants.value.push(newVariant);

  // Reset form
  selectedProductId.value = "";
  variantName.value = "";
  variantSKU.value = "";
  variantPrice.value = 0;

  alert("Variant added (mock)");
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
