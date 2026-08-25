<template>
  <div class="p-8 bg-gray-50 min-h-screen font-sans">
    <!-- Header -->
    <div
      class="mb-12 bg-white border border-gray-100 rounded-2xl p-8 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6"
    >
      <div class="flex items-start md:items-center gap-5 flex-1">
        <div
          class="w-16 h-16 bg-gradient-to-tr from-pink-400 via-purple-500 to-yellow-400 flex items-center justify-center rounded-full shadow-md"
        >
          <span class="material-icons text-white text-4xl">photo_camera</span>
        </div>
        <div>
          <h2 class="text-2xl font-extrabold text-gray-800">
            Instagram Business Accounts
          </h2>
          <p class="text-gray-600 text-sm md:text-base mt-1">
            Connect your Instagram Business accounts to manage content, insights, and engagement all in one place.
          </p>
        </div>
      </div>
      <div class="flex-shrink-0 w-full md:w-auto">
        <a
          :href="instagramAuthUrl"
          class="w-full md:w-auto bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-400 text-white px-6 py-3 rounded-xl font-semibold flex items-center justify-center gap-2 shadow-md hover:scale-105 transition-transform"
        >
          <span class="material-icons-outlined text-sm">add_link</span>
          Connect Instagram Account
        </a>
      </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div
        v-for="(count, key) in counts"
        :key="key"
        class="rounded-2xl p-6 flex items-center gap-4 bg-white border border-gray-100 shadow-sm"
      >
        <div
          class="p-3 rounded-xl bg-gradient-to-tr from-pink-400 via-purple-500 to-yellow-400 text-white"
        >
          <span class="material-icons">{{ iconName(key) }}</span>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-500 capitalize">
            {{ labelName(key) }}
          </p>
          <p class="text-2xl font-extrabold text-gray-800">
            <span v-if="loading" class="w-12 h-6 inline-block rounded shimmer"></span>
            <span v-else>{{ counts[key] }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Accounts -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">
      <!-- Empty -->
      <div
        v-if="!loading && !accounts.length"
        class="col-span-full bg-white rounded-2xl p-10 border border-gray-100 shadow-sm text-center"
      >
        <span class="material-icons text-gray-300 text-6xl mb-4">
          account_circle
        </span>
        <h3 class="text-xl font-bold text-gray-800 mb-2">
          No Accounts Connected
        </h3>
        <p class="text-gray-600 mb-5">
          You haven’t connected any Instagram Business accounts yet. Start by connecting one below.
        </p>
        <a
          :href="instagramAuthUrl"
          class="px-6 py-3 bg-gradient-to-r from-pink-500 via-purple-500 to-yellow-400 text-white rounded-xl font-semibold hover:scale-105 transition-transform"
        >
          Connect Account
        </a>
      </div>

      <!-- Loading skeletons -->
      <div
        v-else-if="loading"
        v-for="n in 3"
        :key="n"
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 shimmer"
      >
        <div class="h-6 bg-gray-200 rounded w-1/3 mb-4"></div>
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-6"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
      </div>

      <!-- Account Cards -->
      <div
        v-for="account in accounts"
        :key="account.id"
        class="relative bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300"
      >
        <!-- Status -->
        <div
          class="absolute top-4 right-4 px-3 py-1 text-xs font-medium rounded-full"
          :class="{
            'bg-green-100 text-green-700': account.status === 'connected',
            'bg-red-100 text-red-600': account.status === 'expired',
          }"
        >
          {{ account.status === 'connected' ? 'Active' : 'Needs Reconnection' }}
        </div>

        <!-- Header -->
        <div class="flex items-center gap-4 mb-5 pt-5">
          <div
            class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-tr from-pink-400 via-purple-500 to-yellow-400 text-white"
          >
            <span class="material-icons text-2xl">account_circle</span>
          </div>
          <div class="flex-1">
            <p class="text-lg font-semibold text-gray-800">
              {{ account.username }}
            </p>
            <p class="text-sm text-gray-500">@{{ account.instagram_user_id }}</p>
          </div>
        </div>

        <!-- Info -->
        <div class="text-sm text-gray-600 bg-gray-50 rounded-xl p-4 mb-5">
          <div class="flex justify-between">
            <span class="font-medium">Account Name:</span>
            <span>{{ account.account_name }}</span>
          </div>
          <div class="flex justify-between mt-2">
            <span class="font-medium">Last Synced:</span>
            <span>{{ account.lastSync || "N/A" }}</span>
          </div>
          <div class="flex justify-between mt-2">
            <span class="font-medium">Access Status:</span>
            <span>
              {{ account.status === 'connected' ? 'Valid' : 'Expired' }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            v-if="account.status === 'expired'"
            @click="reconnectAccount(account)"
            class="flex-1 border border-pink-400 text-pink-600 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-pink-50 transition"
          >
            <span class="material-icons-outlined text-sm">refresh</span>
            Reconnect Account
          </button>
          <button
            v-else
            @click="refreshToken(account)"
            class="flex-1 border border-purple-400 text-purple-600 rounded-xl py-2 text-sm flex items-center justify-center gap-2 hover:bg-purple-50 transition"
          >
            <span class="material-icons-outlined text-sm">autorenew</span>
            Update Access
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import instagramService from "@/services/InstagramService";
import { useRoute } from "vue-router";

const route = useRoute();


// OAuth URL for Instagram Business Login
const instagramAuthUrl =
  "https://www.instagram.com/oauth/authorize?client_id=1737904123469092&redirect_uri=https://crm.ngumzo.com/api/instagram/callback&response_type=code&scope=instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments,instagram_business_content_publish,instagram_business_manage_insights";

const accounts = ref([]);
const counts = ref({ total: 0, connected: 0, expired: 0 });
const loading = ref(true);

const fetchAccounts = async () => {
  loading.value = true;
  try {
    const res = await instagramService.getAccounts();
    accounts.value = res.accounts || [];
    counts.value = res.counts || { total: 0, connected: 0, expired: 0 };
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Couldn’t load accounts",
      text: err.response?.data?.message || "Please try again later.",
    });
  } finally {
    loading.value = false;
  }
};

const reconnectAccount = (account) => {
  window.location.href = instagramAuthUrl;
};

const refreshToken = async (account) => {
  Swal.fire({
    title: "Updating Access...",
    text: `Refreshing connection for ${account.username}`,
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    await instagramService.refreshToken(account.id);
    await fetchAccounts();
    Swal.fire({
      icon: "success",
      title: "Connection Updated",
      text: `${account.username}'s account is now up to date.`,
    });
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Update Failed",
      text: err.response?.data?.message || "Could not refresh access.",
    });
  }
};
onMounted(() => {
  fetchAccounts();
  if (route.query.connected === "1") {
    Swal.fire({
      icon: "success",
      title: "Instagram Connected",
      text: "Your Instagram account has been successfully linked!",
    });
  } else if (route.query.error) {
    Swal.fire({
      icon: "error",
      title: "Connection Failed",
      text: "Something went wrong while connecting your Instagram account. Please try again.",
    });
  }
});
/* UI Helpers */
const iconName = (key) => {
  if (key === "total") return "group";
  if (key === "connected") return "check_circle";
  if (key === "expired") return "cancel";
};
const labelName = (key) => {
  if (key === "total") return "Total Accounts";
  if (key === "connected") return "Active Connections";
  if (key === "expired") return "Needs Reconnection";
};
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
