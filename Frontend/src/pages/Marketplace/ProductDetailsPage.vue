<template>
  <div class="mkt">
    <MktNavbar />

    <!-- Loading -->
    <div v-if="loading" style="padding:80px 24px;text-align:center;color:#94a3b8">
      <svg class="mkt-spinner" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#229de7" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
      <p style="margin-top:12px;font-size:14px;font-weight:600">Loading product…</p>
    </div>

    <!-- 404 -->
    <div v-else-if="!product" style="text-align:center;padding:100px 24px;color:#64748b">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 20px;display:block;color:#cbd5e1"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
      <h2 style="font-size:24px;font-weight:700;color:#0f172a;margin-bottom:10px">Product not found</h2>
      <p style="margin-bottom:24px">This product doesn't exist or may have been removed.</p>
      <router-link to="/marketplace" class="mkt-btn mkt-btn--primary">Browse Marketplace</router-link>
    </div>

    <template v-else>
      <!-- Breadcrumb -->
      <div style="background:#fff;border-bottom:1px solid #e2e8f0">
        <div class="mkt-container" style="padding-top:10px;padding-bottom:10px;display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b">
          <router-link to="/marketplace" style="color:#229de7">Marketplace</router-link>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
          <router-link v-if="product.store_slug" :to="`/marketplace/store/${product.store_slug}`" style="color:#229de7">{{ product.store_name }}</router-link>
          <span v-else style="color:#94a3b8">{{ product.store_name }}</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
          <span style="color:#0f172a;font-weight:500">{{ product.name }}</span>
        </div>
      </div>

      <!-- Main content -->
      <div style="background:#f1f5f9;padding:32px 0 48px">
        <div class="mkt-container">

          <!-- Product card -->
          <div class="pd-card">
            <!-- Image panel -->
            <div class="pd-image" :style="cardBg">
              <div class="pd-image__badges">
                <span v-if="product.stock <= 0" class="mkt-badge mkt-badge--out">Out of stock</span>
                <span v-if="product.stock > 0 && product.stock <= 5" class="mkt-badge mkt-badge--deal">Only {{ product.stock }} left</span>
              </div>
              <svg width="80" height="80" viewBox="0 0 24 24" fill="none" v-html="catIcon" style="opacity:0.9"></svg>
            </div>

            <!-- Info panel -->
            <div class="pd-info">
              <p class="pd-store-link" v-if="product.store_slug">
                <router-link :to="`/marketplace/store/${product.store_slug}`">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  {{ product.store_name }}
                </router-link>
              </p>

              <h1 class="pd-title">{{ product.name }}</h1>

              <p v-if="product.short_description" style="font-size:14px;color:#64748b;line-height:1.6">{{ product.short_description }}</p>

              <div class="pd-price">
                <span class="pd-price__main">KES {{ Number(product.selling_price).toLocaleString() }}</span>
              </div>

              <div class="pd-availability" :class="product.stock > 0 ? 'pd-availability--in' : 'pd-availability--out'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                  <path v-if="product.stock > 0" d="M20 6L9 17l-5-5"/>
                  <path v-else d="M18 6L6 18M6 6l12 12"/>
                </svg>
                {{ product.stock > 0 ? `In stock — ${product.stock} available` : 'Currently out of stock' }}
              </div>

              <!-- Tags -->
              <div v-if="product.tags?.length" style="display:flex;gap:6px;flex-wrap:wrap">
                <span v-for="tag in product.tags" :key="tag" style="font-size:11px;font-weight:600;background:#f1f5f9;color:#64748b;padding:3px 10px;border-radius:20px">{{ tag }}</span>
              </div>

              <div class="pd-actions">
                <button class="mkt-btn mkt-btn--primary mkt-btn--lg pd-action-btn" :disabled="product.stock <= 0">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                  {{ product.stock > 0 ? 'Add to Cart' : 'Out of Stock' }}
                </button>
                <router-link v-if="product.store_slug" :to="`/marketplace/store/${product.store_slug}`" class="mkt-btn mkt-btn--outline mkt-btn--lg pd-action-btn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  Visit Store
                </router-link>
              </div>

              <!-- Store info mini -->
              <div class="pd-store-card" v-if="product.store">
                <div class="pd-store-card__logo" :style="{ background: product.store.banner_color || '#229de7' }">
                  {{ product.store.name?.charAt(0) ?? 'S' }}
                </div>
                <div class="pd-store-card__body">
                  <p class="pd-store-card__name">{{ product.store.name }}</p>
                  <p class="pd-store-card__meta">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ product.store.location ?? '—' }}
                  </p>
                </div>
              </div>

              <!-- Description -->
              <div v-if="product.description" style="padding:16px;background:#f8fafc;border-radius:12px;font-size:13px;color:#475569;line-height:1.7;border:1px solid #e2e8f0">
                <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Description</p>
                {{ product.description }}
              </div>
            </div>
          </div>

          <!-- Related products -->
          <div v-if="related.length" class="pd-related">
            <h2 class="pd-related__title">More in {{ product.category }}</h2>
            <div class="mkt-products-grid">
              <div
                v-for="p in related"
                :key="p.id"
                class="mkt-product-card"
                style="cursor:pointer"
                @click="goToProduct(p)"
              >
                <div class="mkt-product-card__img" :style="relCardBg(p)">
                  <div class="mkt-product-card__img-inner">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" v-html="getCatIcon(p.category)"></svg>
                  </div>
                </div>
                <div class="mkt-product-card__body">
                  <p class="mkt-product-card__store">{{ p.store_name }}</p>
                  <p class="mkt-product-card__name">{{ p.name }}</p>
                  <div class="mkt-product-card__price">
                    <span class="mkt-product-card__amount">KES {{ Number(p.selling_price).toLocaleString() }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </template>

    <MktFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import MktNavbar from "./components/MktNavbar.vue";
import MktFooter from "./components/MktFooter.vue";
import MarketplaceApiService from "@/services/MarketplaceApiService";
import { getCategoryIcon, getCategoryColor } from "./utils/categoryConfig.js";

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const product = ref(null);
const related = ref([]);

const catIcon = computed(() => getCategoryIcon(product.value?.category));

const cardBg = computed(() => {
  const color = product.value?.color || getCategoryColor(product.value?.category);
  return { background: `linear-gradient(135deg, ${color}cc 0%, ${color} 100%)` };
});

function relCardBg(p) {
  const color = p.color || getCategoryColor(p.category);
  return { background: `linear-gradient(135deg, ${color}cc 0%, ${color} 100%)` };
}

function getCatIcon(cat) { return getCategoryIcon(cat); }

function goToProduct(p) {
  router.push(`/marketplace/product/${p.id}`);
}

const loadProduct = async (id) => {
  loading.value = true;
  product.value = null;
  related.value = [];
  try {
    const data = await MarketplaceApiService.getProduct(id);
    if (data) {
      related.value = data.related ?? [];
      product.value = data;
    }
  } catch {
    product.value = null;
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadProduct(route.params.id));
watch(() => route.params.id, (id) => id && loadProduct(id));
</script>

<style src="./marketplace.css"></style>
<style>
.pd-card {
  display: grid;
  grid-template-columns: 400px 1fr;
  gap: 28px;
  background: #fff;
  border-radius: 18px;
  border: 1.5px solid #e2e8f0;
  overflow: hidden;
  margin-bottom: 36px;
}
.pd-image {
  min-height: 380px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.pd-image__badges {
  position: absolute;
  top: 16px;
  left: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.pd-info {
  padding: 32px 32px 32px 4px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.pd-store-link a {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  font-weight: 600;
  color: #229de7;
  text-decoration: none;
}
.pd-store-link a:hover { color: #1682c4; }
.pd-title {
  font-size: clamp(20px, 2.5vw, 28px);
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.5px;
  line-height: 1.3;
}
.pd-price { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
.pd-price__main { font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -1px; }
.pd-availability {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 8px;
  width: fit-content;
}
.pd-availability--in { background: #dcfce7; color: #15803d; }
.pd-availability--out { background: #f1f5f9; color: #94a3b8; }
.pd-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.pd-action-btn { flex: 1; min-width: 140px; justify-content: center; }
.pd-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pd-store-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 16px;
}
.pd-store-card__logo {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 20px;
  font-weight: 900;
  flex-shrink: 0;
}
.pd-store-card__body { flex: 1; min-width: 0; }
.pd-store-card__name { font-size: 14px; font-weight: 700; color: #0f172a; }
.pd-store-card__meta { font-size: 12px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.pd-related { margin-top: 8px; }
.pd-related__title { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.4px; margin-bottom: 20px; }
@keyframes spin { to { transform: rotate(360deg); } }
.mkt-spinner { animation: spin 0.8s linear infinite; display: block; margin: 0 auto; }
@media (max-width: 768px) {
  .pd-card { grid-template-columns: 1fr; }
  .pd-image { min-height: 240px; }
  .pd-info { padding: 20px; }
  .pd-price__main { font-size: 24px; }
}
</style>
