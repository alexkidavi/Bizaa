<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Variant Name</label>
      <input v-model="variant.name" type="text" placeholder="e.g., Red / Large" class="form-control" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
      <input v-model="variant.sku" type="text" placeholder="SKU" class="form-control" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
      <input v-model.number="variant.price" type="number" step="0.01" placeholder="0.00" class="form-control" />
    </div>

    <div class="flex gap-2">
      <button type="button" @click="$emit('remove', index)" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">Remove</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  variant: Object,
  index: Number
});
</script>
