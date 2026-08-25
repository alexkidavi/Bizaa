<template>
  <div class="mkt">
    <MktNavbar />

    <!-- Loading -->
    <div v-if="loading" style="padding:80px 24px;text-align:center;color:#94a3b8">
      <svg class="mkt-spinner" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#229de7" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
      <p style="margin-top:12px;font-size:14px;font-weight:600">Loading store…</p>
    </div>

    <!-- 404 -->
    <div v-else-if="!store" style="text-align:center;padding:100px 24px;color:#64748b">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 20px;display:block;color:#cbd5e1"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
      <h2 style="font-size:24px;font-weight:700;color:#0f172a;margin-bottom:10px">Store not found</h2>
      <p style="margin-bottom:24px">This store doesn't exist or may have been removed.</p>
      <router-link to="/marketplace" class="mkt-btn mkt-btn--primary">Browse Marketplace</router-link>
    </div>

    <template v-else>
      <!-- Breadcrumb -->
      <div style="background:#fff;border-bottom:1px solid #e2e8f0">
        <div class="mkt-container" style="padding-top:10px;padding-bottom:10px;display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b">
          <router-link to="/marketplace" style="color:#229de7">Marketplace</router-link>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
          <span style="color:#94a3b8">{{ categoryLabel }}</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
          <span style="color:#0f172a;font-weight:500">{{ store.name }}</span>
        </div>
      </div>

      <StoreHeader :store="store" />

      <div style="background:#f1f5f9;padding-bottom:48px">
        <StoreProducts :store="store" :products="storeProducts" />
      </div>
    </template>

    <MktFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import MktNavbar from "./components/MktNavbar.vue";
import MktFooter from "./components/MktFooter.vue";
import StoreHeader from "./components/StoreHeader.vue";
import StoreProducts from "./components/StoreProducts.vue";
import MarketplaceApiService from "@/services/MarketplaceApiService";
import { getCategoryConfig } from "./utils/categoryConfig.js";

const route = useRoute();
const loading = ref(true);
const store = ref(null);
const storeProducts = ref([]);

const categoryLabel = computed(() =>
  getCategoryConfig(store.value?.category)?.label ?? store.value?.category ?? "Store"
);

const loadStore = async (slug) => {
  loading.value = true;
  store.value = null;
  storeProducts.value = [];
  try {
    const data = await MarketplaceApiService.getStoreBySlug(slug);
    if (data) {
      storeProducts.value = data.products ?? [];
      store.value = data;
    }
  } catch {
    store.value = null;
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadStore(route.params.slug));
watch(() => route.params.slug, (slug) => slug && loadStore(slug));
</script>

<style src="./marketplace.css"></style>
<style src="./store.css"></style>
<style scoped>
@keyframes spin { to { transform: rotate(360deg); } }
.mkt-spinner { animation: spin 0.8s linear infinite; display: block; margin: 0 auto; }
</style>
