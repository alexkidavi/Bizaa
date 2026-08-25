<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
    >
      <div class="bg-white rounded-2xl w-full max-w-lg p-6 relative shadow-lg">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800">WhatsApp Devices Guide</h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition"
          >
            <span class="material-icons-outlined">close</span>
          </button>
        </div>

        <!-- Content -->
        <div class="space-y-4 text-gray-700 text-sm">
          <p>
            Here’s how to use the WhatsApp device manager:
          </p>
          <ul class="list-disc list-inside space-y-2">
            <li>
              <strong>Add a Device:</strong> Click “Add Device”, provide a name,
              phone number, and optional webhook.
            </li>
            <li>
              <strong>Connect:</strong> Generate a QR code and scan it with WhatsApp.
            </li>
            <li>
              <strong>Disconnect:</strong> Logs out the device safely.
            </li>
            <li>
              <strong>Features:</strong> Enable autosave contacts, leads, or bot.
            </li>
            <li>
              <strong>Delete:</strong> Remove device permanently.
            </li>
          </ul>

          <p class="italic text-gray-500">
            All changes are synced in real-time via WebSocket. Make sure the device
            is online before performing actions.
          </p>
        </div>

        <div class="mt-6 text-right">
          <button
            @click="closeModal"
            class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition"
          >
            Got it
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({ show: Boolean });
const emit = defineEmits(["update:show"]);

const closeModal = () => emit("update:show", false);
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
