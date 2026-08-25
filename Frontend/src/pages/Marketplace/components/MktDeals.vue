<template>
  <section v-if="loading || visibleProducts.length > 0" class="mkt-section mkt-section--dark">
    <div class="mkt-container">
      <div class="mkt-deals-header">
        <h2>⚡ New Arrivals</h2>
        <router-link to="/marketplace" style="color:#38bdf8;font-size:13px;font-weight:600;margin-left:auto">
          Browse all →
        </router-link>
      </div>

      <!-- Skeleton -->
      <div v-if="loading" class="mkt-deals-grid">
        <div v-for="n in 4" :key="n" class="mkt-product-card mkt-product-card--deal" style="pointer-events:none">
          <div class="mkt-product-card__img" style="background:#334155;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          <div class="mkt-product-card__body">
            <div style="height:12px;background:#334155;border-radius:6px;margin-bottom:8px;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
            <div style="height:10px;background:#334155;border-radius:6px;width:60%;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          </div>
        </div>
      </div>

      <div v-else class="mkt-deals-grid">
        <div
          v-for="p in visibleProducts"
          :key="p.id"
          class="mkt-product-card mkt-product-card--deal"
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
import { computed } from "vue";
import { useRouter } from "vue-router";
import { getCategoryIcon, getCategoryColor } from "../utils/categoryConfig.js";

const props = defineProps({
  products: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const router = useRouter();

// Show the last 4 added products as "new arrivals"
const visibleProducts = computed(() => props.products.slice(-4).reverse());

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
@keyframes mkt-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
