<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="mb-7">
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Settings</h1>
      <p class="text-slate-500 mt-1 text-sm">
        Manage your personal profile, business details, tax and M-Pesa settings.
      </p>
    </div>

    <!-- TABS -->
    <div class="flex items-center gap-1.5 mb-6 bg-white border border-[#D9EBF7] rounded-2xl p-1.5 shadow-sm w-fit overflow-x-auto">
      <button
        v-for="tab in visibleTabs"
        :key="tab.key"
        @click="setTab(tab.key)"
        class="px-4 py-2 text-sm font-bold rounded-xl transition whitespace-nowrap"
        :class="activeTab === tab.key
          ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
          : 'text-slate-500 hover:bg-[#EAF5FD] hover:text-[#229DE7]'"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-4 border-[#229DE7] border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else>
      <ProfileTab v-if="activeTab === 'profile'" />
      <BusinessTab
        v-else-if="activeTab === 'business'"
        :settings="settings"
        @saved="settings = $event"
      />
      <TaxTab
        v-else-if="activeTab === 'tax'"
        :settings="settings"
        @saved="settings = $event"
      />
      <MpesaTab
        v-else-if="activeTab === 'mpesa'"
        :settings="settings"
        @saved="settings = $event"
      />
      <DigitaxTab
        v-else-if="activeTab === 'digitax'"
        :settings="settings"
        @saved="settings = $event"
      />
      <BanksTab v-else-if="activeTab === 'banks'" />
      <LoyaltyTab v-else-if="activeTab === 'loyalty'" />
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import BusinessService from "@/services/BusinessService";
import ProfileTab from "./tabs/ProfileTab.vue";
import BusinessTab from "./tabs/BusinessTab.vue";
import TaxTab from "./tabs/TaxTab.vue";
import MpesaTab from "./tabs/MpesaTab.vue";
import DigitaxTab from "./tabs/DigitaxTab.vue";
import BanksTab from "./tabs/BanksTab.vue";
import LoyaltyTab from "./tabs/LoyaltyTab.vue";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const canManageBusiness = computed(() => auth.hasPermission("business.update"));
const canManageLoyalty = computed(() => auth.hasPermission("loyalty.manage"));

const allTabs = [
  { key: "profile", label: "Profile", requiresBusiness: false },
  { key: "business", label: "Business", requiresBusiness: true },
  { key: "tax", label: "Tax", requiresBusiness: true },
  { key: "mpesa", label: "M-Pesa Payments", requiresBusiness: true },
  { key: "digitax", label: "KRA eTIMS", requiresBusiness: true },
  { key: "banks", label: "Banks", requiresBusiness: true },
  { key: "loyalty", label: "Loyalty", requiresBusiness: false, requiresPermission: "loyalty.manage" },
];

const visibleTabs = computed(() =>
  allTabs.filter((t) => {
    if (t.requiresBusiness && !canManageBusiness.value) return false;
    if (t.requiresPermission === "loyalty.manage" && !canManageLoyalty.value) return false;
    return true;
  })
);

const activeTab = computed(() => {
  const requested = route.query.tab || "profile";
  return visibleTabs.value.some((t) => t.key === requested) ? requested : "profile";
});

const setTab = (key) => router.replace({ query: { ...route.query, tab: key } });

const settings = ref(null);
const loading = ref(true);

const fetchSettings = async () => {
  if (!canManageBusiness.value) {
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const res = await BusinessService.get();
    settings.value = res?.data?.data ?? res?.data ?? {};
  } finally {
    loading.value = false;
  }
};

onMounted(fetchSettings);
watch(canManageBusiness, (v) => {
  if (v && !settings.value) fetchSettings();
});
</script>
