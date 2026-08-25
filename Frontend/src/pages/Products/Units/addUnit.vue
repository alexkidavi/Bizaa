<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Add New Product</h1>

    <form @submit.prevent="submitProduct" class="bg-white rounded-xl shadow p-6 space-y-6">
      <!-- Basic Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter product name"
           class="form-control"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
          <input
            v-model="form.sku"
            type="text"
            placeholder="Enter SKU"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
          <input
            v-model="form.barcode"
            type="text"
            placeholder="Enter barcode"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="form.category_id"
            class="form-control"
          >
            <option value="">Select Category</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
          <select
            v-model="form.brand_id"
            class="form-control"
          >
            <option value="">Select Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
          <select
            v-model="form.unit_id"
            class="form-control"
          >
            <option value="">Select Unit</option>
            <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
          </select>
        </div>
      </div>

      <!-- Pricing & Stock -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buying Price</label>
          <input
            v-model.number="form.buying_price"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
          <input
            v-model.number="form.selling_price"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Wholesale Price</label>
          <input
            v-model.number="form.wholesale_price"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
          <input
            v-model.number="form.reorder_level"
            type="number"
            placeholder="0"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Opening Stock</label>
          <input
            v-model.number="form.opening_stock"
            type="number"
            placeholder="0"
            class="form-control"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
          <input
            v-model="form.expiration_date"
            type="date"
            class="form-control"
          />
        </div>
      </div>

      <!-- Description & Image -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea
          v-model="form.description"
          rows="4"
          class="form-control"
          placeholder="Enter product description"
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
        <input type="file" @change="handleImageUpload" accept="image/*" />
        <div v-if="form.imagePreview" class="mt-2">
          <img :src="form.imagePreview" alt="Preview" class="h-32 rounded-lg border" />
        </div>
      </div>

      <!-- Active Switch -->
      <div class="flex items-center gap-2">
        <input type="checkbox" v-model="form.is_active" id="active" class="h-4 w-4" />
        <label for="active" class="text-gray-700 font-medium">Active</label>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition"
      >
        Save Product
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";

// Sample data for selects, replace with API calls
const categories = ref([
  { id: 1, name: "Electronics" },
  { id: 2, name: "Beverages" },
  { id: 3, name: "Stationery" },
]);

const brands = ref([
  { id: 1, name: "Brand A" },
  { id: 2, name: "Brand B" },
]);

const units = ref([
  { id: 1, name: "Piece" },
  { id: 2, name: "Box" },
]);

const form = ref({
  name: "",
  sku: "",
  barcode: "",
  category_id: "",
  brand_id: "",
  unit_id: "",
  description: "",
  buying_price: 0,
  selling_price: 0,
  wholesale_price: 0,
  reorder_level: 0,
  opening_stock: 0,
  expiration_date: "",
  image: null,
  imagePreview: null,
  is_active: true,
});

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  form.value.image = file;
  form.value.imagePreview = URL.createObjectURL(file);
};

const submitProduct = () => {
  console.log("Submitting product:", form.value);
  alert("Product saved! (implement backend logic)");
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
  border-radius: 0.313rem; /* 5px approx */
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
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25); /* subtle focus glow */
}
</style>
