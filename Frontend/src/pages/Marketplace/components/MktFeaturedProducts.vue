<template>
  <section class="mkt-section mkt-section--white">
    <div class="mkt-container">
      <div class="mkt-section-head">
        <div>
          <p class="mkt-label">Handpicked for you</p>
          <h2>Featured Products</h2>
        </div>
        <router-link to="/marketplace">Browse all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></router-link>
      </div>

      <!-- Category filter tabs -->
      <div class="mkt-filter-tabs">
        <button
          v-for="tab in availableTabs"
          :key="tab.id"
          class="mkt-filter-tab"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Skeleton -->
      <div v-if="loading" class="mkt-products-grid">
        <div v-for="n in 6" :key="n" class="mkt-product-card" style="pointer-events:none">
          <div class="mkt-product-card__img" style="background:#e2e8f0;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          <div class="mkt-product-card__body">
            <div style="height:12px;background:#f1f5f9;border-radius:6px;margin-bottom:8px;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
            <div style="height:10px;background:#f1f5f9;border-radius:6px;width:60%;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="filtered.length === 0" style="text-align:center;padding:48px;color:#94a3b8">
        <p style="font-size:15px;font-weight:600">No products listed yet</p>
        <p style="font-size:13px;margin-top:6px">Products will appear here once sellers list them on their stores.</p>
      </div>

      <div v-else class="mkt-products-grid">
        <div
          v-for="p in filtered.slice(0, 8)"
          :key="p.id"
          class="mkt-product-card"
          style="cursor:pointer"
          @click="goToProduct(p)"
        >
          <div class="mkt-product-card__img" :style="cardBg(p)">
            <div class="mkt-product-card__img-inner">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" v-html="catIcon(p.category)"></svg>
            </div>
            <button class="mkt-product-card__wishlist" @click.stop>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
          </div>

          <div class="mkt-product-card__body">
            <p class="mkt-product-card__store">{{ p.store_name }}</p>
            <p class="mkt-product-card__name">{{ p.name }}</p>
            <div class="mkt-product-card__price">
              <span class="mkt-product-card__amount">KES {{ Number(p.selling_price).toLocaleString() }}</span>
            </div>
            <div class="mkt-product-card__footer">
              <button class="mkt-product-card__cart" :class="{ 'mkt-product-card__cart--out': p.stock <= 0 }" @click.stop>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                {{ p.stock > 0 ? "Add to cart" : "Out of stock" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { getCategoryConfig, getCategoryIcon, getCategoryColor } from "../utils/categoryConfig.js";

const props = defineProps({
  products: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const router = useRouter();
const activeTab = ref("all");

const availableTabs = computed(() => {
  const cats = [...new Set(props.products.map(p => p.category).filter(Boolean))];
  return [
    { id: "all", label: "All" },
    ...cats.slice(0, 5).map(c => ({ id: c, label: c })),
  ];
});

const filtered = computed(() =>
  activeTab.value === "all"
    ? props.products
    : props.products.filter(p => p.category === activeTab.value)
);

function catIcon(cat) { return getCategoryIcon(cat); }

function cardBg(p) {
  const color = p.color || getCategoryColor(p.category);
  return { background: `linear-gradient(135deg, ${color}cc 0%, ${color} 100%)` };
}

function goToProduct(p) {
  router.push(`/marketplace/product/${p.id}`);
}
</script>

<style scoped>
.mkt-filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.mkt-filter-tab {
  padding: 7px 18px; border-radius: 100px; font-size: 13px; font-weight: 600;
  border: 1.5px solid #e2e8f0; background: #fff; color: #64748b;
  cursor: pointer; transition: all 0.15s;
}
.mkt-filter-tab:hover { border-color: #229de7; color: #229de7; }
.mkt-filter-tab.active { background: #229de7; color: #fff; border-color: #229de7; }
@keyframes mkt-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
