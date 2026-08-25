<template>
  <section class="mkt-section mkt-section--gray">
    <div class="mkt-container">
      <div class="mkt-section-head">
        <div>
          <p class="mkt-label">Trusted sellers</p>
          <h2>Featured Stores</h2>
        </div>
        <router-link to="/marketplace">All stores <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></router-link>
      </div>

      <!-- Skeleton -->
      <div v-if="loading" class="mkt-stores-grid">
        <div v-for="n in 4" :key="n" class="mkt-store-card" style="pointer-events:none">
          <div class="mkt-store-card__banner" style="background:#e2e8f0;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          <div class="mkt-store-card__body" style="gap:8px">
            <div style="height:14px;background:#e2e8f0;border-radius:6px;width:70%;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
            <div style="height:11px;background:#f1f5f9;border-radius:6px;width:45%;animation:mkt-pulse 1.5s ease-in-out infinite"></div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="stores.length === 0" style="text-align:center;padding:48px 24px;color:#94a3b8">
        <p style="font-size:15px;font-weight:600">No featured stores yet</p>
        <p style="font-size:13px;margin-top:6px">Check back soon — sellers are setting up their shops.</p>
      </div>

      <!-- Grid -->
      <div v-else class="mkt-stores-grid">
        <router-link
          v-for="store in stores"
          :key="store.id"
          :to="`/marketplace/store/${store.slug}`"
          class="mkt-store-card"
        >
          <div class="mkt-store-card__banner" :style="{ background: `linear-gradient(135deg, ${store.banner_color || '#1e3a5f'} 0%, ${darken(store.banner_color)} 100%)` }">
            <div class="mkt-store-card__banner-pattern"></div>
            <div class="mkt-store-card__initial">{{ store.name?.[0] ?? 'S' }}</div>
          </div>

          <div class="mkt-store-card__body">
            <p class="mkt-store-card__name">{{ store.name }}</p>
            <p class="mkt-store-card__cat">{{ categoryLabel(store.category) }}</p>

            <div class="mkt-store-card__meta">
              <div v-if="store.location" class="mkt-store-card__location">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                {{ store.location }}
              </div>
              <span class="mkt-store-card__products">{{ store.listed_products_count ?? 0 }} products</span>
            </div>

            <div class="mkt-store-card__footer">
              <span class="mkt-store-card__btn">Visit Store →</span>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { getCategoryConfig } from "../utils/categoryConfig.js";

const props = defineProps({
  stores: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

function categoryLabel(cat) {
  return getCategoryConfig(cat)?.label ?? (cat ?? "General");
}

function darken(hex) {
  if (!hex || !hex.startsWith('#')) return '#0f3460';
  try {
    const d = (v) => Math.max(0, Math.round(parseInt(v, 16) * 0.6)).toString(16).padStart(2, '0');
    return `#${d(hex.slice(1, 3))}${d(hex.slice(3, 5))}${d(hex.slice(5, 7))}`;
  } catch { return '#0f3460'; }
}
</script>
