<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-8">
      Send WhatsApp Messages
    </h2>

    <!-- Device Selection -->
    <div class="bg-white rounded-2xl shadow p-6 mb-8">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Select Device
      </label>

      <div
        v-if="loadingDevices"
        class="flex items-center space-x-2 text-gray-500 text-sm"
      >
        <svg
          class="animate-spin h-5 w-5 text-blue-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4l3.536-3.536A8.001 8.001 0 0120 12h-4a4 4 0 00-4-4v4H4z"
          ></path>
        </svg>
        <span>Loading devices...</span>
      </div>

      <select
        v-else
        v-model="selectedDevice"
        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
      >
        <option disabled value="">Select a connected device</option>
        <option
          v-for="device in devices"
          :key="device.id"
          :value="device.id"
          :disabled="device.status !== 'connected'"
        >
          {{ device.number }} - {{ device.status }}
        </option>
      </select>

      <p
        v-if="!loadingDevices && devices.length === 0"
        class="text-sm text-gray-500 mt-2"
      >
        No devices available. Please connect a device first.
      </p>
    </div>

    <!-- Tabs -->
    <div class="flex space-x-2 mb-8">
      <button
        v-for="(tab, i) in tabs"
        :key="i"
        @click="activeTab = tab"
        :class="[
          'px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300',
          activeTab === tab
            ? 'bg-blue-500 text-white shadow'
            : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-100',
        ]"
      >
        {{ tab }}
      </button>
    </div>

    <!-- QUICK SEND -->
    <div
      v-if="activeTab === 'Quick Send'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Phone Numbers (comma separated)</label
        >
        <textarea
          v-model="quickSend.numbers"
          rows="2"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="+254701234567, +254790123456"
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Message</label
        >
        <textarea
          v-model="quickSend.message"
          rows="6"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="Write your message here..."
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Attachment (optional)</label
        >
        <div
          class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center text-gray-500 cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition"
          @drop.prevent="handleDropQuick"
          @dragover.prevent
          @click="triggerQuickFile"
        >
          <p v-if="!quickSend.attachmentName">
            Drag & drop file here or click to select
          </p>
          <p v-else class="text-gray-700">
            Selected: {{ quickSend.attachmentName }}
          </p>
          <input
            type="file"
            ref="quickInput"
            class="hidden"
            @change="handleQuickAttachment"
          />
        </div>
      </div>

      <button
        class="bg-blue-500 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-600 transition w-full sm:w-auto"
        @click="sendQuick"
      >
        Send Messages
      </button>
    </div>

    <!-- PHONE BOOK -->
    <div
      v-if="activeTab === 'Phone Book'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Select Phone Book</label
        >

        <div
          v-if="loadingPhonebooks"
          class="flex items-center space-x-2 text-gray-500 text-sm"
        >
          <svg
            class="animate-spin h-5 w-5 text-blue-500"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4l3.536-3.536A8.001 8.001 0 0120 12h-4a4 4 0 00-4-4v4H4z"
            ></path>
          </svg>
          <span>Loading phonebooks...</span>
        </div>

        <select
          v-else
          v-model="phoneBook.selected"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
        >
          <option disabled value="">Select a phonebook</option>
          <option
            v-for="book in phoneBook.list"
            :key="book.id"
            :value="book.id"
          >
            {{ book.name }} ({{ book.contacts_count || 0 }} contacts)
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Message</label
        >
        <textarea
          v-model="phoneBook.message"
          rows="7"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="Write your message here..."
        ></textarea>
      </div>

      <!-- Attachment -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2"
          >Attachment (optional)</label
        >
        <div
          class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center text-gray-500 cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition"
          @drop.prevent="handleDropPhonebook"
          @dragover.prevent
          @click="triggerPhonebookFile"
        >
          <p v-if="!phoneBook.attachmentName">
            Drag & drop file here or click to select
          </p>
          <p v-else class="text-gray-700">
            Selected: {{ phoneBook.attachmentName }}
          </p>
          <input
            type="file"
            ref="phonebookInput"
            class="hidden"
            @change="handlePhonebookAttachment"
          />
        </div>
      </div>

      <!-- TEST NUMBER INPUT -->
      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Test Number (send a preview message)
        </label>
        <div class="flex gap-2">
          <input
            type="text"
            v-model="testNumber"
            placeholder="+2547XXXXXXXX"
            class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          />
          <button
            class="bg-green-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-600 transition"
            @click="sendTestMessage"
          >
            Send Test
          </button>
        </div>
      </div>

      <button
        class="bg-blue-500 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-600 transition w-full sm:w-auto"
        @click="sendPhoneBook"
      >
        Send Messages
      </button>
    </div>

    <!-- other tabs unchanged -->
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import deviceService from "@/services/deviceService";
import WhatsappService from "@/services/WhatsappService";
import phonebookService from "@/services/phonebookService";
import { useRouter } from "vue-router";

const tabs = ["Quick Send", "Phone Book", "Excel Upload", "Custom Message"];
const activeTab = ref("Phone Book");
const router = useRouter();

// Devices
const devices = ref([]);
const selectedDevice = ref("");
const loadingDevices = ref(true);
const loadDevices = async () => {
  try {
    const data = await deviceService.getConnectedDevices();
    if (data.success) devices.value = data.devices;
  } catch {
    Swal.fire("Error", "Failed to load devices.", "error");
  } finally {
    loadingDevices.value = false;
  }
};

// Quick send reuse
const quickSend = ref({
  numbers: "",
  message: "",
  attachment: null,
  attachmentName: "",
});
const sendQuick = async (overrideFormData = null) => {
  const formData = overrideFormData || new FormData();
  if (!overrideFormData) {
    formData.append("device_id", selectedDevice.value);
    formData.append("numbers", quickSend.value.numbers);
    formData.append("message", quickSend.value.message);
    if (quickSend.value.attachment)
      formData.append("attachment", quickSend.value.attachment);
  }

  const data = await WhatsappService.quickSend(formData);
  return data;
};

// Phonebook section
const phoneBook = ref({
  list: [],
  selected: "",
  message: "",
  attachment: null,
  attachmentName: "",
});
const loadingPhonebooks = ref(false);
const loadPhonebooks = async () => {
  loadingPhonebooks.value = true;
  try {
    const res = await phonebookService.getPhonebooks();
    if (res.success) phoneBook.value.list = res.phonebooks;
  } catch {
    Swal.fire("Error", "Failed to load phonebooks.", "error");
  } finally {
    loadingPhonebooks.value = false;
  }
};

// Attachments
const phonebookInput = ref(null);
const triggerPhonebookFile = () => phonebookInput.value?.click();
const handlePhonebookAttachment = (e) => {
  const file = e.target.files[0];
  if (file) {
    phoneBook.value.attachment = file;
    phoneBook.value.attachmentName = file.name;
  }
};
const handleDropPhonebook = (e) => {
  const file = e.dataTransfer.files[0];
  if (file) {
    phoneBook.value.attachment = file;
    phoneBook.value.attachmentName = file.name;
  }
};

// Test message logic
const testNumber = ref("");
const sendTestMessage = async () => {
  if (!selectedDevice.value)
    return Swal.fire("Device Required", "Select a device first.", "warning");
  if (!testNumber.value.trim())
    return Swal.fire(
      "Enter Test Number",
      "Please enter a phone number.",
      "warning"
    );
  if (!phoneBook.value.message.trim())
    return Swal.fire("Message Required", "Write a message first.", "warning");

  try {
    Swal.fire({
      title: "Sending Test Message...",
      text: "Please wait a moment.",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });

    const formData = new FormData();
    formData.append("device_id", selectedDevice.value);
    formData.append("numbers", testNumber.value);
    formData.append("message", phoneBook.value.message);
    if (phoneBook.value.attachment)
      formData.append("attachment", phoneBook.value.attachment);

    await sendQuick(formData);
    Swal.fire("Success", "Test message sent successfully!", "success");
  } catch (err) {
    Swal.fire(
      "Error",
      err.response?.data?.message || "Failed to send test message.",
      "error"
    );
  }
};

// Main send phonebook
const sendPhoneBook = async () => {
  if (!selectedDevice.value)
    return Swal.fire(
      "Select a device",
      "Please select a device first.",
      "warning"
    );
  if (!phoneBook.value.selected)
    return Swal.fire("Select a list", "Please select a phone book.", "warning");

  try {
    Swal.fire({
      title: "Processing...",
      text: "Sending messages to phone book.",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });

    const formData = new FormData();
    formData.append("device_id", selectedDevice.value);
    formData.append("phonebook_id", phoneBook.value.selected);
    formData.append("message", phoneBook.value.message);
    if (phoneBook.value.attachment)
      formData.append("attachment", phoneBook.value.attachment);

    const data = await WhatsappService.phoneBookSend(formData);
    Swal.fire(
      "Success",
      data.message || "Messages sent successfully!",
      "success"
    );
    router.push("/communication/whatsapp/managemessages");
  } catch (err) {
    Swal.fire(
      "Error",
      err.response?.data?.message || "Failed to send messages.",
      "error"
    );
  }
};

onMounted(() => {
  loadDevices();
  loadPhonebooks();
});
</script>
