<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="flex items-center gap-2 text-3xl font-extrabold text-gray-800">
        <MessageSquare class="w-8 h-8 text-blue-500" />
        <span>Manage Whatsapp Messages</span>
      </h2>
      <button
        @click="refreshMessages"
        class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-xl shadow transition"
      >
        <RefreshCcw class="w-5 h-5" />
        Refresh
      </button>
    </div>

    <!-- Stats Cards -->
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
      <!-- Total -->
      <div
        class="bg-emerald-500 p-4 rounded-2xl shadow-md flex items-center gap-4 text-white"
      >
        <div class="p-3 bg-white/20 rounded-full">
          <Send class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs uppercase">Total</p>
          <p class="text-3xl font-extrabold">
            <span v-if="!statsLoading">{{ stats.total }}</span>
            <span
              v-else
              class="inline-block w-10 h-6 bg-white/40 rounded animate-pulse"
            ></span>
          </p>
        </div>
      </div>

      <!-- Sent -->
      <div
        class="bg-blue-900 p-4 rounded-2xl shadow-md flex items-center gap-4 text-white"
      >
        <div class="p-3 bg-white rounded-full">
          <CheckCircle class="w-6 h-6 text-blue-900" />
        </div>
        <div>
          <p class="text-xs uppercase">Sent</p>
          <p class="text-3xl font-extrabold">
            <span v-if="!statsLoading">{{
              stats.sent ? stats.sent.toLocaleString() : 0
            }}</span>
            <span
              v-else
              class="inline-block w-10 h-6 bg-white-50 rounded animate-pulse"
            ></span>
          </p>
        </div>
      </div>

      <!-- Failed -->
      <div
        class="bg-red-800 p-4 rounded-2xl shadow-md flex items-center gap-4 text-white"
      >
        <div class="p-3 bg-white rounded-full">
          <XCircle class="w-6 h-6 text-red-800" />
        </div>
        <div>
          <p class="text-xs uppercase">Failed</p>
          <p class="text-3xl font-extrabold">
            <span v-if="!statsLoading">{{ stats.failed }}</span>
            <span
              v-else
              class="inline-block w-10 h-6 bg-red-200 rounded animate-pulse"
            ></span>
          </p>
        </div>
      </div>

      <!-- Queued -->
      <div
        class="bg-yellow-500 p-4 rounded-2xl shadow-md flex items-center gap-4 text-yellow-900"
      >
        <div class="p-3 bg-white rounded-full">
          <Clock class="w-6 h-6 text-yellow-800" />
        </div>
        <div>
          <p class="text-xs uppercase">Queued</p>
          <p class="text-3xl font-extrabold">
            <span v-if="!statsLoading">{{ stats.queued }}</span>
            <span
              v-else
              class="inline-block w-10 h-6 bg-yellow-800 rounded animate-pulse"
            ></span>
          </p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6">
      <!-- Filter Card -->
      <div
        class="bg-white shadow-lg rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 flex-wrap"
      >
        <div class="flex gap-3 flex-1 flex-wrap">
          <!-- Search Input -->
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by phone or message..."
            class="flex-1 min-w-[200px] border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-emerald-400 shadow-sm placeholder-gray-400"
          />

          <!-- Status Dropdown -->
          <select
            v-model="filters.status"
            class="border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-emerald-400 shadow-sm min-w-[150px]"
          >
            <option value="">All Status</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
            <option value="queued">Pending</option>
          </select>

          <!-- From Date -->
          <input
            v-model="filters.fromDate"
            type="date"
            class="border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-emerald-400 shadow-sm min-w-[140px]"
          />

          <!-- To Date -->
          <input
            v-model="filters.toDate"
            type="date"
            class="border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-emerald-400 shadow-sm min-w-[140px]"
          />
        </div>
        <!-- 
    Optional: Reset / Apply Buttons
    <div class="flex gap-2 mt-3 md:mt-0">
      <button
        @click="resetFilters"
        class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm transition"
      >
        Reset
      </button>
      <button
        @click="applyFilters"
        class="px-4 py-2 rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 shadow-sm transition"
      >
        Apply
      </button>
    </div> -->
      </div>
    </div>

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-4">
      <div
        v-for="n in 5"
        :key="n"
        class="h-6 bg-gray-200 rounded-lg animate-pulse"
      ></div>
    </div>

    <!-- Messages Table -->
    <div v-else class="overflow-x-auto bg-white rounded-2xl shadow-2xl p-4">
      <table
        class="min-w-full divide-y divide-gray-200 rounded-2xl overflow-hidden"
      >
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="header in tableHeaders"
              :key="header"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              {{ header }}
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(msg, index) in filteredMessages"
            :key="msg.id"
            class="bg-white hover:shadow-md transition duration-150 ease-in-out rounded-lg"
          >
            <td class="px-6 py-4 text-sm text-gray-700">{{ index + 1 }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ msg.number }}</td>
            <td
              class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate"
              :title="msg.message"
            >
              {{ msg.message }}
            </td>
            <td
              class="px-6 py-4 text-sm font-semibold capitalize"
              :class="statusClass(msg.status)"
            >
              {{ msg.status }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ msg.sentAt }}</td>
            <!-- <td class="px-6 py-4 text-sm flex gap-2">
              <button
                class="border border-blue-500 text-blue-500 px-3 py-1 rounded-xl text-xs hover:bg-blue-50 transition duration-150"
                @click="resendMessage(msg.id)"
              >
                Resend
              </button>
              <button
                class="border border-red-500 text-red-500 px-3 py-1 rounded-xl text-xs hover:bg-red-50 transition duration-150"
                @click="deleteMessage(msg.id)"
              >
                Delete
              </button>
            </td> -->
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <p
        v-if="filteredMessages.length === 0"
        class="text-center text-gray-500 p-6"
      >
        No messages found.
      </p>
    </div>

    <!-- Pagination -->
    <div
      class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-3"
    >
      <button
        @click="prevPage"
        :disabled="page <= 1"
        class="px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 disabled:opacity-50 transition"
      >
        Previous
      </button>
      <span class="text-gray-600 text-sm">
        Page {{ page }} of {{ meta.last_page || 1 }}
      </span>
      <button
        @click="nextPage"
        :disabled="page >= meta.last_page"
        class="px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 disabled:opacity-50 transition"
      >
        Next
      </button>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import WhatsappService from "@/services/WhatsappService";
import {
  MessageSquare,
  Send,
  CheckCircle,
  XCircle,
  Clock,
  RefreshCcw,
} from "lucide-vue-next";

// State
const messages = ref([]);
const loading = ref(false);
const statsLoading = ref(false);
const stats = ref({ total: 0, sent: 0, failed: 0, queued: 0 });
const page = ref(1);
const perPage = ref(10);
const meta = ref({});
const filters = ref({ search: "", status: "", fromDate: "", toDate: "" });

const tableHeaders = [
  "#",
  "Phone",
  "Message",
  // "Attachment",
  "Status",
  "Sent At",
  // "Actions",
];

const fetchMessages = async () => {
  try {
    loading.value = true;

    const res = await WhatsappService.getMessages({
      page: page.value,
      per_page: perPage.value,
    });

    if (res.success) {
      // Use the correct keys from the API response
      messages.value = res.data; // res.data is already the array
      meta.value = res.meta || {}; // pagination info
    } else {
      Swal.fire("Error", "Failed to load messages.", "error");
    }
  } catch (error) {
    console.error(error);
    Swal.fire(
      "Error",
      "Something went wrong while fetching messages.",
      "error"
    );
  } finally {
    loading.value = false;
  }
};

// ✅ Fetch stats from backend
const fetchStats = async () => {
  statsLoading.value = true;
  try {
    const res = await WhatsappService.getStats();
    if (res.data?.success) {
      stats.value = res.data.data;
    }
  } catch (err) {
    console.error(err);
  } finally {
    statsLoading.value = false;
  }
};

// ✅ Computed filters for search + status + date
const filteredMessages = computed(() =>
  messages.value.filter((msg) => {
    const search = filters.value.search.toLowerCase();
    const matchesSearch =
      !search ||
      msg.phone.includes(search) ||
      msg.message.toLowerCase().includes(search);
    const matchesStatus =
      !filters.value.status || msg.status === filters.value.status;
    const matchesFrom =
      !filters.value.fromDate || msg.sentAt >= filters.value.fromDate;
    const matchesTo =
      !filters.value.toDate || msg.sentAt <= filters.value.toDate;
    return matchesSearch && matchesStatus && matchesFrom && matchesTo;
  })
);
const formatNumber = (num) => {
  return num != null ? num.toLocaleString() : "0";
};
const refreshMessages = () => {
  fetchMessages();
  fetchStats();
};

// ✅ Pagination
const nextPage = () => {
  if (page.value < meta.value.last_page) {
    page.value++;
    fetchMessages();
  }
};
const prevPage = () => {
  if (page.value > 1) {
    page.value--;
    fetchMessages();
  }
};

// ✅ Status colors
const statusClass = (status) => {
  switch (status) {
    case "sent":
      return "text-green-600";
    case "failed":
      return "text-red-600";
    case "queued":
    case "pending":
      return "text-yellow-600";
    default:
      return "text-gray-500";
  }
};

onMounted(() => {
  fetchMessages();
  fetchStats();
});
</script>
