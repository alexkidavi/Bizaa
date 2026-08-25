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
          {{ device.number }} - <span class="capitalize">{{ device.status }}</span>
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

    <!-- Quick Send -->
    <div
      v-if="activeTab === 'Quick Send'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Phone Numbers (comma separated)
        </label>
        <textarea
          v-model="quickSend.numbers"
          rows="2"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="+254701234567, +254790123456"
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
        <textarea
          v-model="quickSend.message"
          rows="7"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="Write your message here..."
        ></textarea>
      </div>

      <!-- Attachment Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Optional Attachment
        </label>
        <div
          class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center text-gray-500 cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition"
          @drop.prevent="handleDropQuick"
          @dragover.prevent
          @click="triggerQuickFile"
        >
          <p v-if="!quickSend.attachmentName">
            Drag & drop image/file here or click to select
          </p>
          <p v-else class="text-gray-700">
            Selected: {{ quickSend.attachmentName }}
          </p>
          <input
            type="file"
            accept="image/*,application/pdf,.doc,.docx"
            class="hidden"
            ref="quickInput"
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

    <!-- Phone Book -->
    <div
      v-if="activeTab === 'Phone Book'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Select Phone Book
        </label>

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
        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
        <textarea
          v-model="phoneBook.message"
          rows="7"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="Write your message here..."
        ></textarea>
      </div>

      <button
        class="bg-blue-500 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-600 transition w-full sm:w-auto"
        @click="sendPhoneBook"
      >
        Send Messages
      </button>
    </div>

    <!-- Excel Upload -->
    <div
      v-if="activeTab === 'Excel Upload'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Upload Excel File
        </label>
        <div
          class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center text-gray-500 cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition"
          @drop.prevent="handleDropExcel"
          @dragover.prevent
          @click="triggerExcelFile"
        >
          <p v-if="!excel.fileName">Drag & drop file here or click to select</p>
          <p v-else class="text-gray-700">Selected: {{ excel.fileName }}</p>
          <input
            type="file"
            accept=".xls,.xlsx,.csv"
            class="hidden"
            ref="excelInput"
            @change="handleExcelFile"
          />
        </div>
      </div>

      <button
        class="bg-blue-500 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-600 transition w-full sm:w-auto"
        @click="sendExcel"
      >
        Send Messages
      </button>
    </div>

    <!-- Custom Message -->
    <div
      v-if="activeTab === 'Custom Message'"
      class="bg-white rounded-2xl shadow p-6 space-y-5"
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Select Phone Book
        </label>
        <select
          v-model="customMessage.selectedBook"
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
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Message Template
        </label>

        <textarea
          v-model="customMessage.message"
          rows="7"
          class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-400 shadow-sm"
          placeholder="Write your message here... Use {{name}} for personalization."
        ></textarea>

        <p
          class="text-xs text-gray-500 mt-1"
          v-html="'You can personalize your message using <code>{{name}}</code>.'"
        ></p>

        <div
          class="bg-gray-50 border border-gray-200 rounded-lg p-3 mt-2 text-sm text-gray-700"
        >
          <strong>Sample:</strong> Hello &#123;&#123;name&#125;&#125;, thank you
          for being part of our WhatsApp community! 🎉
        </div>
      </div>

      <button
        class="bg-blue-500 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-600 transition w-full sm:w-auto"
        @click="sendCustomMessage"
      >
        Send Customized Messages
      </button>
    </div>
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
const activeTab = ref("Quick Send");
const router = useRouter();

const devices = ref([]);
const selectedDevice = ref("");
const loadingDevices = ref(true);

const loadDevices = async () => {
  loadingDevices.value = true;
  try {
    const data = await deviceService.getConnectedDevices();
    if (data.success) devices.value = data.devices;
  } catch {
    Swal.fire("Error", "Failed to load devices.", "error");
  } finally {
    loadingDevices.value = false;
  }
};

/* ---------------------- QUICK SEND ---------------------- */
const quickSend = ref({
  numbers: "",
  message: "",
  attachment: null,
  attachmentName: "",
});

const quickInput = ref(null);
const triggerQuickFile = () => quickInput.value?.click();
const handleQuickAttachment = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  quickSend.value.attachment = file;
  quickSend.value.attachmentName = file.name;
};
const handleDropQuick = (e) => {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  quickSend.value.attachment = file;
  quickSend.value.attachmentName = file.name;
};

const sendQuick = async () => {
  if (!selectedDevice.value)
    return Swal.fire("Select a device", "Please select a device first.", "warning");
  if (!quickSend.value.numbers.trim() || !quickSend.value.message.trim())
    return Swal.fire("Missing fields", "Please fill in numbers and message.", "warning");

  try {
    Swal.fire({
      title: "Sending...",
      text: "Your message is being sent.",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });

    const formData = new FormData();
    formData.append("device_id", selectedDevice.value);
    formData.append("numbers", quickSend.value.numbers);
    formData.append("message", quickSend.value.message);
    if (quickSend.value.attachment)
      formData.append("attachment", quickSend.value.attachment);

    const { data } = await WhatsappService.quickSend(formData);

    Swal.fire("Success", data.message || "Message sent successfully!", "success");
    quickSend.value = { numbers: "", message: "", attachment: null, attachmentName: "" };
    router.push("/communication/whatsapp/managemessages");
  } catch (err) {
    Swal.fire(
      "Error",
      err.response?.data?.message || "Failed to send message.",
      "error"
    );
  }
};

/* ---------------------- PHONE BOOK ---------------------- */
const phoneBook = ref({ list: [], selected: "", message: "" });
const loadingPhonebooks = ref(false);
const loadPhonebooks = async () => {
  loadingPhonebooks.value = true;
  try {
    const res = await phonebookService.getPhonebooks();
    if (res.success && Array.isArray(res.phonebooks))
      phoneBook.value.list = res.phonebooks;
  } catch {
    Swal.fire("Error", "Failed to load phonebooks.", "error");
  } finally {
    loadingPhonebooks.value = false;
  }
};

const sendPhoneBook = async () => {
  if (!selectedDevice.value)
    return Swal.fire("Select a device", "Please select a device first.", "warning");
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

    const data = await WhatsappService.phoneBookSend({
      device_id: selectedDevice.value,
      phonebook_id: phoneBook.value.selected,
      message: phoneBook.value.message,
    });
    Swal.fire("Success", data.message || "Messages sent successfully!", "success");
    router.push("/communication/whatsapp/managemessages");
  } catch (err) {
    Swal.fire("Error", err.response?.data?.message || "Failed to send.", "error");
  }
};

/* ---------------------- EXCEL UPLOAD ---------------------- */
const excel = ref({ file: null, fileName: "" });
const excelInput = ref(null);
const triggerExcelFile = () => excelInput.value?.click();
const handleExcelFile = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  excel.value.file = file;
  excel.value.fileName = file.name;
};
const handleDropExcel = (e) => {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  excel.value.file = file;
  excel.value.fileName = file.name;
};

const sendExcel = async () => {
  if (!selectedDevice.value)
    return Swal.fire("Select a device", "Please select a device first.", "warning");
  if (!excel.value.file)
    return Swal.fire("No file", "Please upload an Excel file.", "warning");

  try {
    Swal.fire({
      title: "Uploading...",
      text: "Processing your Excel file.",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });
    const formData = new FormData();
    formData.append("device_id", selectedDevice.value);
    formData.append("file", excel.value.file);

    const { data } = await WhatsappService.excelSend(formData);
    Swal.fire("Success", data.message || "Excel uploaded successfully!", "success");
    router.push("/communication/whatsapp/managemessages");
    excel.value = { file: null, fileName: "" };
  } catch (err) {
    Swal.fire("Error", err.response?.data?.message || "Failed to upload.", "error");
  }
};

/* ---------------------- CUSTOM MESSAGE ---------------------- */
const customMessage = ref({ selectedBook: "", message: "" });
const sendCustomMessage = async () => {
  if (!selectedDevice.value)
    return Swal.fire("Select a device", "Please select a device first.", "warning");
  if (!customMessage.value.selectedBook)
    return Swal.fire("Select a list", "Please select a phone book.", "warning");
  if (!customMessage.value.message.trim())
    return Swal.fire("Empty message", "Please write your message.", "warning");

  try {
    Swal.fire({
      title: "Processing...",
      text: "Sending customized messages.",
      icon: "info",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });

    const data = await WhatsappService.customPhonebookSend({
      device_id: selectedDevice.value,
      phonebook_id: customMessage.value.selectedBook,
      message: customMessage.value.message,
    });

    Swal.fire("Success", data.message || "Customized messages sent!", "success");
    router.push("/communication/whatsapp/managemessages");
    customMessage.value = { selectedBook: "", message: "" };
  } catch (err) {
    Swal.fire("Error", err.response?.data?.message || "Failed to send.", "error");
  }
};

onMounted(() => {
  loadDevices();
  loadPhonebooks();
});
</script>
