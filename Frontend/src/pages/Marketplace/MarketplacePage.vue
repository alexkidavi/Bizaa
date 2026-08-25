<template>
  <div class="mkt">
    <MktNavbar />
    <MktHero />
    <MktCategories />
    <MktFeaturedStores :stores="featuredStores" :loading="loading" />
    <MktDeals :products="listedProducts" :loading="loading" />
    <MktFeaturedProducts :products="listedProducts" :loading="loading" />
    <MktHowItWorks />
    <MktSellerCTA />
    <MktFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import MktNavbar from "./components/MktNavbar.vue";
import MktHero from "./components/MktHero.vue";
import MktCategories from "./components/MktCategories.vue";
import MktFeaturedStores from "./components/MktFeaturedStores.vue";
import MktDeals from "./components/MktDeals.vue";
import MktFeaturedProducts from "./components/MktFeaturedProducts.vue";
import MktHowItWorks from "./components/MktHowItWorks.vue";
import MktSellerCTA from "./components/MktSellerCTA.vue";
import MktFooter from "./components/MktFooter.vue";
import MarketplaceApiService from "@/services/MarketplaceApiService";

const loading = ref(true);
const stores = ref([]);

const featuredStores = computed(() => stores.value.filter((s) => s.is_featured));

// Flatten all listed products across all stores (fetched via storeBySlug in components)
// The landing page only needs stores; products are loaded lazily in each component.
const listedProducts = ref([]);

onMounted(async () => {
  try {
    stores.value = await MarketplaceApiService.getStores();

    // Load products from all stores for featured/deals sections
    const results = await Promise.all(
      stores.value.map((s) =>
        MarketplaceApiService.getStoreBySlug(s.slug).catch(() => null)
      )
    );
    listedProducts.value = results
      .filter(Boolean)
      .flatMap((s) =>
        (s.products ?? []).map((p) => ({ ...p, store_name: s.name, store_slug: s.slug }))
      );
  } catch {
    // fail silently — empty state handled in child components
  } finally {
    loading.value = false;
  }
});
</script>

<style src="./marketplace.css"></style>
