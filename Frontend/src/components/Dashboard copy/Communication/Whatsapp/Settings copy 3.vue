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
        <div class="flex-shrink-0 w-full md:w-auto flex gap-2">
          <button
            @click="showModal = true"
            class="w-full md:w-auto border border-blue-500 text-blue-500 px-6 py-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition hover:scale-105 hover:bg-green-50"
          >
            <span class="material-icons-outlined text-sm">add_link</span>
            Add Device
          </button>

          <button
            @click="showGuideModal = true"
            class="w-full md:w-auto border border-gray-400 text-gray-600 px-6 py-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition hover:scale-105 hover:bg-gray-100"
          >
            <span class="material-icons-outlined text-sm">help_outline</span>
            Guide
          </button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
      <div
        v-for="(count, key) in counts"
        :key="key"
        class="rounded-2xl p-6 flex items-center gap-4 shadow-sm backdrop-blur-md border border-white/20 transition duration-300 hover:shadow-lg hover:-translate-y-1"
        :class="statsColor(key)"
      >
        <div
          class="p-3 rounded-full bg-white/20 flex items-center justify-center"
        >
          <span class="material-icons" :class="iconColor(key)">{{
            iconName(key)
          }}</span>
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
        <span class="material-icons text-gray-400 text-6xl mb-4"
          >devices_other</span
        >
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
      <div
        v-for="device in devices"
        :key="device.id"
        class="relative bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
      >
        <!-- Status Badge -->
        <div
          class="absolute top-4 right-4 px-3 py-1 text-xs font-medium rounded-full"
          :class="statusColor(device.status)"
        >
          {{ device.status }}
        </div>

        <!-- Header -->
        <div class="flex items-center gap-4 mb-5 pt-5">
          <div
            class="w-12 h-12 flex items-center justify-center rounded-xl"
            :class="statusColorBg(device.status)"
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
        <div
          v-else-if="device.qrError"
          class="flex justify-center items-center my-4 text-red-500 font-medium"
        >
          <span class="material-icons mr-2">error_outline</span>
          {{ device.qrError }}
        </div>

        <!-- Info Section -->
        <div class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4 mb-5">
          <div class="flex justify-between">
            <span class="font-medium">Webhook:</span>
            <span
              :class="device.webhook ? 'text-blue-500' : 'text-gray-400 italic'"
              >{{ device.webhook || "Not set" }}</span
            >
          </div>
          <div class="flex justify-between mt-2">
            <span class="font-medium">Last Sync:</span>
            <span>{{ device.lastSync || "N/A" }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3 mt-3">
          <div class="flex gap-3">
            <button
              v-if="device.status === 'disconnected'"
              @click="handleConnect(device)"
              class="flex-1 border border-blue-500 text-blue-500 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-green-50"
            >
              <span class="material-icons-outlined text-sm">link</span>
              Connect
            </button>

            <button
              @click="handleDisconnect(device)"
              class="flex-1 border border-red-500 text-red-500 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-red-50"
            >
              <span class="material-icons-outlined text-sm">link_off</span>
              Disconnect
            </button>
          </div>

          <!-- Dropdown for additional actions -->
          <div class="relative mt-3">
            <button
              @click="device.showActions = !device.showActions"
              class="w-full px-4 py-2 border rounded-xl bg-gray-100 hover:bg-gray-200 flex justify-between items-center transition"
            >
              Actions
              <ChevronDown class="w-4 h-4 text-emerald-600" />
            </button>

            <div
              v-if="device.showActions"
              class="bg-white shadow-lg rounded-xl border border-gray-200 z-50 overflow-hidden md:absolute md:right-0 md:mt-2 sm:fixed sm:left-4 sm:right-4 sm:top-20"
            >
              <button
                @click="handleToggleFeature(device, 'autosave_contacts')"
                class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
              >
                <UserPlus2Icon class="w-4 h-4 text-emerald-600" />
                {{ device.autosave_contacts ? "Disable" : "Enable" }} Contacts
              </button>

              <button
                @click="handleToggleFeature(device, 'autosave_leads')"
                class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
              >
                <File class="w-4 h-4 text-emerald-600" />
                {{ device.autosave_leads ? "Disable" : "Enable" }} Leads
              </button>

              <button
                @click="handleToggleFeature(device, 'bot')"
                class="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3 text-gray-700 transition"
              >
                <CpuIcon class="w-4 h-4 text-emerald-600" />
                {{ device.bot ? "Disable" : "Enable" }} Bot
              </button>

              <button
                @click="handleDeleteDevice(device)"
                class="w-full px-4 py-2 hover:bg-red-50 flex items-center gap-3 text-red-600 font-semibold transition"
              >
                <XCircle class="w-4 h-4" /> Delete Device
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ConnectDeviceModal
      v-model:show="showModal"
      :newDevice="newDevice"
      @add-device="handleAddDevice"
    />
    <GuideModal v-model:show="showGuideModal" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { io } from "socket.io-client";
import Swal from "sweetalert2";
import deviceService from "@/services/deviceService";
import {
  UserPlus2Icon,
  File,
  CpuIcon,
  ChevronDown,
  XCircle,
} from "lucide-vue-next";
import ConnectDeviceModal from "./components/ConnectDeviceModal.vue";
import GuideModal from "./components/GuideModal.vue";

const WA_URL_SERVER =
  import.meta.env.VITE_WA_URL_SERVER || "http://localhost:3000";
const socket = io(WA_URL_SERVER, { transports: ["websocket", "polling"] });

const devices = ref([]);
const counts = ref({ total: 0, connected: 0, disconnected: 0, qr: 0 });
const loading = ref(true);
const showModal = ref(false);
const showGuideModal = ref(false);
const newDevice = ref({ name: "", number: "", webhook: "" });

/* ----------------------- Fetch Devices ----------------------- */
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

/* ----------------------- Socket Listeners ----------------------- */
const setupSocketListeners = () => {
  console.log("[WS] Setting up WebSocket listeners...");

  socket.on("connect", () => {
    console.log("[WS] Connected to server");
    devices.value.forEach((device) => {
      console.log("[WS] Initializing device:", device.number);
      socket.emit("init-device", device.number);
    });
  });

  socket.on("device-status", (payload) => {
    console.log("[WS] Received device-status event:", payload);

    const { phone, status, qr, reason } = payload;
    const device = devices.value.find((d) => d.number === phone);

    if (!device) {
      console.warn("[WS] Device not found:", phone);
      return;
    }

    console.log("[WS] Updating device status:", {
      device: device.number,
      status,
      hasQr: !!qr,
      reason,
    });

    device.status = status;
    device.qr = qr || null;
    device.loadingQr = status === "qr";
    device.qrError = reason || null;
  });
};


/* ----------------------- Lifecycle ----------------------- */
onMounted(async () => {
  await fetchDevices();
  setupSocketListeners();
});

onUnmounted(() => socket.disconnect());

/* ----------------------- Device Actions ----------------------- */
const handleAddDevice = async () => {
  const { name, number, webhook } = newDevice.value;
  if (!/^(\+?254|0)?[17]\d{8}$/.test(number))
    return showAlert(
      "error",
      "Invalid Phone Number",
      "Enter a valid Kenyan number"
    );
  if (webhook && !/^(https?:\/\/)[\w.-]+\.[a-z]{2,}(\/\S*)?$/.test(webhook))
    return showAlert("error", "Invalid Webhook URL", "Enter a valid URL");

  Swal.fire({
    title: "Saving...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    const res = await deviceService.createDevice({
      name,
      phone_number: number,
      webhook,
    });
    await fetchDevices();
    showModal.value = false;
    newDevice.value = { name: "", number: "", webhook: "" };
    Swal.close();
    showAlert(
      "success",
      "Device Added",
      res.message || "Device saved successfully"
    );
  } catch (err) {
    Swal.close();
    showAlert(
      "error",
      "Failed",
      err.response?.data?.message || "Something went wrong"
    );
  }
};

const handleConnect = (device) => {
  console.log("[UI] Connect clicked for:", device.number);

  device.loadingQr = true;
  device.qr = null;
  device.qrError = null;
  device.status = "connecting";

  socket.emit("init-device", device.number);
  console.log("[WS] Sent init-device event for:", device.number);
};


const handleDisconnect = async (device) => {
  Swal.fire({
    title: "Disconnecting...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    socket.emit("LogoutDevice", device.number);
    device.status = "disconnected";
    device.qr = null;
    await deviceService.updateDeviceStatus(device.id, "disconnected");
    Swal.close();
    showAlert("success", "Disconnected", `${device.name} disconnected`);
  } catch (err) {
    Swal.close();
    showAlert(
      "error",
      "Disconnect Failed",
      err.response?.data?.message || "Failed to disconnect"
    );
  }
};

const handleToggleFeature = async (device, feature) => {
  const newStatus = device[feature] ? 0 : 1;
  Swal.fire({
    title: "Updating...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    const res = await deviceService.toggleFeature(
      device.id,
      feature,
      newStatus
    );
    device[feature] = newStatus;
    Swal.close();
    showAlert("success", "Updated", res.message);
  } catch (err) {
    Swal.close();
    showAlert(
      "error",
      "Update Failed",
      err.response?.data?.message || "Failed to update feature"
    );
  }
};

const handleDeleteDevice = async (device) => {
  const result = await Swal.fire({
    title: "Delete Device?",
    text: `Delete ${device.name}?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc2626",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  Swal.fire({
    title: "Deleting...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    await deviceService.deleteDevice(device.id);
    devices.value = devices.value.filter((d) => d.id !== device.id);
    await fetchDevices();
    Swal.close();
    showAlert("success", "Deleted", `${device.name} deleted`);
  } catch (err) {
    Swal.close();
    showAlert(
      "error",
      "Delete Failed",
      err.response?.data?.message || "Failed to delete device"
    );
  }
};

/* ----------------------- Helpers ----------------------- */
const showAlert = (icon, title, text) =>
  Swal.fire({ icon, title, text, confirmButtonColor: "#16a34a" });

const iconName = (key) =>
  ({
    total: "devices",
    connected: "check_circle",
    disconnected: "cancel",
    qr: "qr_code",
  }[key]);
const iconColor = (key) =>
  ({
    total: "text-gray-800",
    connected: "text-blue-800",
    disconnected: "text-red-800",
    qr: "text-yellow-800",
  }[key]);
const statsColor = (key) =>
  ({
    total: "bg-white text-green-900",
    connected: "bg-white text-blue-900",
    disconnected: "bg-white text-red-900",
    qr: "bg-white text-yellow-900",
  }[key] || "bg-white text-gray-800");

const statusColor = (status) =>
  ({
    connected: "bg-green-600 text-white",
    connecting: "bg-yellow-500 text-white",
    disconnected: "bg-red-500 text-white",
    qr: "bg-gray-400 text-white",
  }[status] || "bg-gray-400 text-white");
const statusColorBg = (status) =>
  ({
    connected: "bg-green-100 text-green-700",
    connecting: "bg-yellow-100 text-yellow-700",
    disconnected: "bg-red-100 text-red-700",
    qr: "bg-gray-100 text-gray-700",
  }[status] || "bg-gray-100 text-gray-700");
</script>

<style>
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
