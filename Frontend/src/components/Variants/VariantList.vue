<template>
  <div class="mt-4 border-t pt-4 space-y-4">
    <h2 class="text-lg font-semibold text-gray-800">Variants</h2>

    <VariantRow
      v-for="(variant, index) in variants"
      :key="index"
      :variant="variant"
      :index="index"
      @remove="removeVariant"
    />

    <button type="button" @click="addVariant" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Add Variant</button>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import VariantRow from "./VariantRow.vue";

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(["update:modelValue"]);

const variants = ref([...props.modelValue]);

const addVariant = () => {
  variants.value.push({ name: "", sku: "", price: 0 });
  emit("update:modelValue", variants.value);
};

const removeVariant = (index) => {
  variants.value.splice(index, 1);
  emit("update:modelValue", variants.value);
};

// Watch parent v-model changes
watch(
  () => props.modelValue,
  (newVal) => {
    variants.value = [...newVal];
  }
);
</script>
