<template>
  <nav class="mkt-nav">
    <div class="mkt-container mkt-nav__top">
      <!-- Logo - pushed to far left -->
      <router-link to="/marketplace" class="mkt-nav__logo">
        <img src="/bizaa-logo.png" alt="Bizaa POS" class="mkt-nav__logo-img" />
        <!-- Bizaa<span class="mkt-nav__logo-dot">Market</span> -->
      </router-link>

      <!-- Search - centered -->
      <div class="mkt-nav__search">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search products, stores, brands…"
          @keydown.enter="doSearch"
        />
        <button @click="doSearch">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
          >
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          Search
        </button>
      </div>

      <!-- Actions (far right) -->
      <div class="mkt-nav__actions">
        <div class="mkt-nav__location">
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
          >
            <path
              d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"
            />
            <circle cx="12" cy="10" r="3" />
          </svg>
          Nairobi
        </div>
        <router-link to="/login" class="mkt-btn mkt-btn--ghost mkt-btn--sm"
          >Sign in</router-link
        >
        <router-link
          to="/register"
          class="mkt-btn mkt-btn--primary mkt-btn--sm"
          style="color: white"
          >Join As A Seller</router-link
        >
        <div class="mkt-nav__cart">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
          </svg>
          <span class="mkt-nav__cart-badge">3</span>
        </div>
        <button class="mkt-nav__burger" @click="mobileOpen = !mobileOpen">
          <svg
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
          >
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
          </svg>
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const searchQuery = ref("");
const mobileOpen = ref(false);

function doSearch() {
  if (searchQuery.value.trim()) {
    router.push({ path: "/marketplace", query: { q: searchQuery.value } });
  }
}
</script>

<style scoped>
.mkt-nav__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
}

.mkt-nav__logo {
  flex-shrink: 0;
  margin-right: auto;
}

.mkt-nav__search {
  flex: 1;
  max-width: 500px;
  margin: 0 auto;
}

.mkt-nav__actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Adjust for mobile */
@media (max-width: 768px) {
  .mkt-nav__search {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    padding: 1rem;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
}
</style>
