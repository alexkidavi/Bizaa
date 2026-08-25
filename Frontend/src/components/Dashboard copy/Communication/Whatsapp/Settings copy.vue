<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- Banner -->
    <div
      class="mb-12 bg-white border border-gray-200 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-6"
    >
      <div class="flex items-start md:items-center gap-5 flex-1">
        <div
          class="w-16 h-16 bg-blue-200 flex items-center justify-center rounded-full shadow-sm"
        >
          <span class="material-icons text-blue-700 text-4xl">chat</span>
        </div>
        <div>
          <h2 class="text-2xl font-extrabold text-gray-800">
            Manage WhatsApp Devices
          </h2>
          <p class="text-gray-600 text-sm md:text-base mt-1">
            Connect all your WhatsApp sessions and track their live status.
          </p>
        </div>
      </div>
      <div class="flex-shrink-0 w-full md:w-auto">
        <button
          @click="showModal = true"
          class="w-full md:w-auto border border-blue-500 text-blue-500 px-6 py-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition hover:scale-105 hover:bg-green-50"
        >
          <span class="material-icons-outlined text-sm">add_link</span>
          Connect Device
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
      <div
        v-for="(count, key) in counts"
        :key="key"
        class="rounded-2xl p-6 flex items-center gap-4 shadow-sm backdrop-blur-md border border-white/20 transition duration-300 hover:shadow-lg hover:-translate-y-1"
        :class="{
          'bg-white text-green-900': key === 'total',
          'bg-white text-blue-900': key === 'connected',
          'bg-white text-red-900': key === 'disconnected',
          'bg-white text-yellow-900': key === 'qr',
        }"
      >
        <div
          class="p-3 rounded-full bg-white/20 flex items-center justify-center"
        >
          <span class="material-icons" :class="iconColor(key)">
            {{ iconName(key) }}
          </span>
        </div>
        <div>
          <p class="text-xs font-medium uppercase mb-1 capitalize">
            {{ key.replace("_", " ") }}
          </p>
          <p class="text-2xl font-extrabold">
            <span
              v-if="loading"
              class="w-12 h-6 inline-block rounded shimmer"
            ></span>
            <span v-else>{{ counts[key] }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Devices Section -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">
      <!-- Empty state -->
      <div
        v-if="!loading && !devices.length"
        class="col-span-full bg-white rounded-2xl p-6 border border-gray-200 shadow text-center"
      >
        <span class="material-icons text-gray-400 text-6xl mb-4">
          devices_other
        </span>
        <h3 class="text-xl font-bold text-gray-800 mb-2">
          No Devices Registered
        </h3>
        <p class="text-gray-600 mb-4">
          You have not connected any WhatsApp devices yet. Click below to add
          one.
        </p>
        <button
          @click="showModal = true"
          class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition"
        >
          Connect Device
        </button>
      </div>

      <!-- Loading skeletons -->
      <div
        v-else-if="loading"
        v-for="n in 3"
        :key="n"
        class="bg-white p-6 rounded-2xl shadow border border-gray-200 shimmer"
      >
        <div class="h-6 bg-gray-200 rounded w-1/3 mb-4"></div>
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-6"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
      </div>

      <!-- Device Cards -->
      <!-- Device Cards -->
      <div
        v-for="device in devices"
        :key="device.id"
        class="relative bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
      >
        <!-- Status Badge -->
        <div
          class="absolute top-4 right-4 px-3 py-1 text-xs font-medium rounded-full"
          :class="{
            'bg-green-600 text-white': device.status === 'connected',
            'bg-yellow-500 text-white': device.status === 'connecting',
            'bg-red-500 text-white': device.status === 'disconnected',
            'bg-gray-400 text-white': device.status === 'qr',
          }"
        >
          {{ device.status }}
        </div>

        <!-- Header -->
        <div class="flex items-center gap-4 mb-5 pt-5">
          <div
            class="w-12 h-12 flex items-center justify-center rounded-xl"
            :class="{
              'bg-green-100 text-green-700': device.status === 'connected',
              'bg-yellow-100 text-yellow-700': device.status === 'connecting',
              'bg-red-100 text-red-700': device.status === 'disconnected',
              'bg-gray-100 text-gray-700': device.status === 'qr',
            }"
          >
            <span class="material-icons text-2xl">smartphone</span>
          </div>

          <div class="flex-1">
            <p class="text-lg font-semibold text-gray-800">{{ device.name }}</p>
            <p class="text-sm text-gray-500">{{ device.number }}</p>
          </div>
        </div>

        <!-- QR Section -->
        <div v-if="device.qr" class="flex justify-center my-4">
          <img
            :src="device.qr"
            alt="QR Code"
            class="rounded-xl border border-gray-200 shadow-sm p-3 w-48"
          />
        </div>
        <div
          v-else-if="device.loadingQr"
          class="flex justify-center items-center my-4 text-gray-400 italic"
        >
          <span class="material-icons animate-spin mr-2">autorenew</span>
          Generating QR Code...
        </div>

        <!-- Info Section -->
        <div class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4 mb-5">
          <div class="flex justify-between">
            <span class="font-medium">Webhook:</span>
            <span
              :class="device.webhook ? 'text-blue-500' : 'text-gray-400 italic'"
            >
              {{ device.webhook || "Not set" }}
            </span>
          </div>
          <div class="flex justify-between mt-2">
            <span class="font-medium">Last Sync:</span>
            <span>{{ device.lastSync || "N/A" }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3 mt-3">
          <div class="flex gap-3">
            <!-- Connect / Disconnect buttons -->
            <button
              v-if="device.status === 'disconnected'"
              @click="connectDevice(device)"
              class="flex-1 border border-blue-500 text-blue-500 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-green-50"
            >
              <span class="material-icons-outlined text-sm">link</span>
              Connect
            </button>

            <button
              @click="disconnectDevice(device)"
              class="flex-1 border border-red-500 text-red-500 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-red-50"
            >
              <span class="material-icons-outlined text-sm">link_off</span>
              Disconnect
            </button>
          </div>

          <!-- Dropdown for additional actions -->
          <div class="flex flex-col gap-3 mt-3">
            <div class="relative">
              <button
                @click="device.showActions = !device.showActions"
                class="w-full px-4 py-2 border rounded-xl bg-gray-100 hover:bg-gray-200 flex justify-between items-center transition"
              >
                Actions
                <ChevronDown class="w-4 h-4 text-emerald-600" />
              </button>

              <!-- Dropdown -->
              <div
                v-if="device.showActions"
                :class="[
                  'bg-white shadow-lg rounded-xl border border-gray-200 z-50 overflow-hidden transition-all',
                  'md:absolute md:right-0 md:mt-2',
                  'sm:fixed sm:left-4 sm:right-4 sm:top-20',
                ]"
              >
                <button
                  @click="toggleAutosaveContacts(device)"
                  class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
                >
                  <UserPlus2Icon class="w-4 h-4 text-emerald-600" />
                  Autosave Contacts
                </button>

                <button
                  @click="toggleAutosaveLeads(device)"
                  class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
                >
                  <File class="w-4 h-4 text-emerald-600" />
                  Autosave Leads
                </button>

                <button
                  @click="toggleBot(device)"
                  class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
                >
                  <CpuIcon class="w-4 h-4 text-emerald-600" />
                  Enable Bot
                </button>

                <button
                  @click="toggleInbox(device)"
                  class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
                >
                  <InboxIcon class="w-4 h-4 text-emerald-600" />
                  Enable Inbox
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showModal"
      class="fixed inset-0 flex items-center justify-center z-50 bg-black/30"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-96 p-6 relative">
        <button
          @click="showModal = false"
          class="absolute top-3 right-3 text-gray-500 hover:text-gray-800"
        >
          <span class="material-icons">close</span>
        </button>
        <h3 class="text-xl font-bold mb-4 text-gray-800">Connect New Device</h3>
        <form @submit.prevent="submitNewDevice" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Device Name</label
            >
            <input
              v-model="newDevice.name"
              type="text"
              required
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
              placeholder="e.g. Sales Phone"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Phone Number</label
            >
            <input
              v-model="newDevice.number"
              type="text"
              required
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
              placeholder="e.g. 254712345678"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Webhook (optional)</label
            >
            <input
              v-model="newDevice.webhook"
              type="text"
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
              placeholder="https://example.com/webhook"
            />
          </div>
          <div class="flex justify-end pt-3">
            <button
              type="button"
              @click="showModal = false"
              class="mr-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
              Add Device
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { io } from "socket.io-client";
import Swal from "sweetalert2";
import deviceService from "@/services/deviceService";
import {
  MessageSquare,
  Send,
  CheckCircle,
  XCircle,
  Clock,
  RefreshCcw,
  UserPlus2Icon,
  File,
  CpuIcon,
  InboxIcon,
  ChevronDown,
} from "lucide-vue-next";

const WA_URL_SERVER =
  import.meta.env.VITE_WA_URL_SERVER || "http://localhost:3000";
const socket = io(WA_URL_SERVER, { transports: ["websocket", "polling"] });

const devices = ref([]);
const counts = ref({ total: 0, connected: 0, disconnected: 0, qr: 0 });
const loading = ref(true);
const showModal = ref(false);
const newDevice = ref({ name: "", number: "", webhook: "" });

const fetchDevices = async () => {
  loading.value = true;
  try {
    const res = await deviceService.getDevices();
    devices.value = res.devices || [];
    counts.value = res.counts || {
      total: 0,
      connected: 0,
      disconnected: 0,
      qr: 0,
    };
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Failed to load devices",
      text: err.response?.data?.message || "Please try again later.",
    });
  } finally {
    loading.value = false;
  }
};

const setupSocketListeners = () => {
  socket.on("qrcode", ({ token, data }) => {
    const device = devices.value.find((d) => d.number === token);
    if (device) {
      device.loadingQr = false;
      device.qr = data;
      device.status = "qr";
    }
  });

  socket.on("connection-open", async ({ token }) => {
    const device = devices.value.find((d) => d.number === token);
    if (device) {
      device.status = "Connected";
      device.qr = null;
      device.lastSync = "Connected now";
      try {
        await deviceService.updateDeviceStatus(device.id, "Connected");
      } catch (err) {
        console.error("Failed to update device status:", err);
      }
    }
  });
};

onMounted(async () => {
  await fetchDevices();
  setupSocketListeners();
});
onUnmounted(() => socket.disconnect());

/* Status Helpers */
const statusBadge = (status) => {
  if (status === "connected")
    return "absolute top-3 right-3 text-xs font-semibold px-3 py-1 rounded-full shadow-sm bg-green-100 text-green-700";
  if (status === "disconnected")
    return "absolute top-3 right-3 text-xs font-semibold px-3 py-1 rounded-full shadow-sm bg-red-100 text-red-700";
  if (status === "qr")
    return "absolute top-3 right-3 text-xs font-semibold px-3 py-1 rounded-full shadow-sm bg-yellow-100 text-yellow-700";
  return "absolute top-3 right-3 text-xs font-semibold px-3 py-1 rounded-full shadow-sm bg-gray-100 text-gray-700";
};
const statusCircle = (status) => {
  if (status === "connected")
    return "w-14 h-14 rounded-full flex items-center justify-center shadow-inner bg-green-50";
  if (status === "disconnected")
    return "w-14 h-14 rounded-full flex items-center justify-center shadow-inner bg-red-50";
  return "w-14 h-14 rounded-full flex items-center justify-center shadow-inner bg-yellow-50";
};
const statusIconColor = (status) => {
  if (status === "connected") return "text-green-600";
  if (status === "disconnected") return "text-red-600";
  return "text-yellow-600";
};

/* Stats Helpers */
const iconName = (key) => {
  if (key === "total") return "devices";
  if (key === "connected") return "check_circle";
  if (key === "disconnected") return "cancel";
  if (key === "qr") return "qr_code";
};
const iconColor = (key) => {
  if (key === "total") return "text-gray-800";
  if (key === "connected") return "text-blue-800";
  if (key === "disconnected") return "text-red-800";
  if (key === "qr") return "text-yellow-800";
};
const bgColor = (key) => {
  if (key === "total") return "bg-gray-100";
  if (key === "connected") return "bg-green-100";
  if (key === "disconnected") return "bg-red-100";
  if (key === "qr") return "bg-yellow-100";
};

/* Validation Helpers */
const isValidPhone = (phone) => /^(\+?254|0)?[17]\d{8}$/.test(phone);
const isValidUrl = (url) =>
  !url || /^(https?:\/\/)[\w.-]+\.[a-z]{2,}(\/\S*)?$/.test(url);

const showAlert = (icon, title, text) =>
  Swal.fire({ icon, title, text, confirmButtonColor: "#16a34a" });

const submitNewDevice = async () => {
  const { name, number, webhook } = newDevice.value;
  if (!isValidPhone(number))
    return showAlert(
      "error",
      "Invalid Phone Number",
      "Please enter a valid Kenyan number (e.g. 2547XXXXXXXX)."
    );
  if (!isValidUrl(webhook))
    return showAlert(
      "error",
      "Invalid Webhook URL",
      "Please enter a valid URL starting with http:// or https://."
    );

  Swal.fire({
    title: "Saving Device...",
    text: "Please wait while we connect your WhatsApp session.",
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const res = await deviceService.createDevice({
      phone_number: number,
      webhook,
    });
    await fetchDevices();
    // socket.emit("StartConnection", number);
    showModal.value = false;
    newDevice.value = { name: "", number: "", webhook: "" };
    Swal.close();
    showAlert(
      "success",
      "Device Added",
      res.message || "Your device has been saved successfully."
    );
  } catch (err) {
    Swal.close();
    if (err.response && err.response.status === 422) {
      const errors = err.response.data.errors;
      showAlert(
        "error",
        "Validation Error",
        Object.values(errors).flat().join("\n")
      );
    } else
      showAlert(
        "error",
        "Unexpected Error",
        err.response?.data?.message ||
          "Something went wrong. Please try again later."
      );
  }
};
const connectDevice = async (device) => {
  device.loadingQr = true;
  device.qr = null;
  device.status = "connecting";
  socket.emit("StartConnection", device.number);
};

const disconnectDevice = (device) => {
  socket.emit("LogoutDevice", device.number);
  device.status = "disconnected";
  device.qr = null;
  deviceService
    .updateDeviceStatus(device.id, "Disconnected")
    .catch(console.error);
};

const refreshDevice = (id) =>
  showAlert("info", "Refreshing", `Refreshing device ${id}`);
</script>

<style>
/* Shimmer Effect */
@keyframes shimmer {
  0% {
    background-position: -200px 0;
  }
  100% {
    background-position: 200px 0;
  }
}
.shimmer {
  background: linear-gradient(90deg, #f3f3f3 25%, #ecebeb 50%, #f3f3f3 75%);
  background-size: 400% 100%;
  animation: shimmer 1.2s infinite;
}
</style>

