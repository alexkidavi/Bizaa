<template>
  <section class="mkt-section mkt-section--white">
    <div class="mkt-container">
      <div class="mkt-section-head">
        <div>
          <p class="mkt-label">Browse by category</p>
          <h2>Shop by Department</h2>
        </div>
        <router-link to="/marketplace">View all <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></router-link>
      </div>

      <div class="mkt-cats-grid">
        <router-link
          v-for="c in categories"
          :key="c.id"
          :to="`/marketplace?cat=${c.id}`"
          class="mkt-cat-card"
          :style="{ '--cat-color': c.color, '--cat-bg': c.bg }"
        >
          <div class="mkt-cat-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" v-html="c.icon"></svg>
          </div>
          <span class="mkt-cat-card__label">{{ c.label }}</span>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { categoryConfig as categories } from "../utils/categoryConfig.js";
</script>
