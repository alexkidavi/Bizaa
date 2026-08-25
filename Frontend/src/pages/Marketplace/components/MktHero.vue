<template>
  <section class="mkt-hero">
    <div class="mkt-container mkt-hero__inner">
      <div class="mkt-hero__text">
        <h1>Everything You Need,<br /><em>From Kenya’s Best Sellers.</em></h1>
        <p>
          Shop electronics, fashion, home essentials, and more from verified Kenyan
          stores. Secure payments and fast delivery straight to your doorstep.
        </p>
      </div>

      <div class="mkt-hero__search">
        <select v-model="county">
          <option value="">All Counties</option>
          <option v-for="c in counties" :key="c.id" :value="c.id">{{ c.label }}</option>
        </select>
        <input
          v-model="q"
          type="text"
          placeholder="What are you looking for today?"
          @keydown.enter="search"
        />
        <button @click="search">
          <svg
            width="18"
            height="18"
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

      <div class="mkt-hero__cats">
        <router-link
          v-for="c in categories.slice(0, 8)"
          :key="c.id"
          :to="`/marketplace?cat=${c.id}`"
        >
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            v-html="c.icon"
          ></svg>
          {{ c.label }}
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { categoryConfig as categories } from "../utils/categoryConfig.js";
import { counties } from "../utils/mockData.js";

const router = useRouter();
const q = ref("");
const county = ref("");

function search() {
  router.push({
    path: "/marketplace",
    query: { q: q.value || undefined, county: county.value || undefined },
  });
}
</script>
