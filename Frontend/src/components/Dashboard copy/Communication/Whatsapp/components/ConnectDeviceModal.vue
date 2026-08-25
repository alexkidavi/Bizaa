<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
    >
      <div
        class="bg-white rounded-2xl w-full max-w-md p-6 relative shadow-lg"
      >
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800">Add WhatsApp Device</h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition"
          >
            <span class="material-icons-outlined">close</span>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Device Name</label
            >
            <input
              v-model="device.name"
              type="text"
              placeholder="My Device"
              class="w-full border border-gray-300 rounded-xl p-3 text-gray-800 focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Phone Number</label
            >
            <input
              v-model="device.number"
              type="text"
              placeholder="+254700000000"
              class="w-full border border-gray-300 rounded-xl p-3 text-gray-800 focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Webhook (Optional)</label
            >
            <input
              v-model="device.webhook"
              type="url"
              placeholder="https://example.com/webhook"
              class="w-full border border-gray-300 rounded-xl p-3 text-gray-800 focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
          </div>

          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition flex justify-center items-center gap-2"
          >
            <span class="material-icons-outlined">add</span>
            Add Device
          </button>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from "vue";

const props = defineProps({ show: Boolean, newDevice: Object });
const emit = defineEmits(["update:show", "add-device"]);

const device = ref({ name: "", number: "", webhook: "" });

watch(
  () => props.newDevice,
  (val) => {
    if (val) device.value = { ...val };
  },
  { immediate: true }
);

const closeModal = () => emit("update:show", false);

const submitForm = () => {
  emit("add-device", { ...device.value });
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
