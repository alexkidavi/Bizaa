<template>
  <section class="mkt-section mkt-section--white">
    <div class="mkt-container">
      <div style="text-align:center;margin-bottom:8px"><p class="mkt-label">Simple process</p></div>
      <h2 style="text-align:center;font-size:clamp(22px,3vw,30px);font-weight:800;color:#0f172a;letter-spacing:-0.4px;margin-bottom:32px">How Bizaa Market Works</h2>

      <div class="mkt-how__tabs">
        <button class="mkt-how__tab" :class="{ active: tab === 'buyer' }" @click="tab = 'buyer'">For Buyers</button>
        <button class="mkt-how__tab" :class="{ active: tab === 'seller' }" @click="tab = 'seller'">For Sellers</button>
      </div>

      <div class="mkt-how__steps">
        <div v-for="(step, i) in currentSteps" :key="i" class="mkt-how__step">
          <div class="mkt-how__step-num">Step {{ String(i + 1).padStart(2, "0") }}</div>
          <div class="mkt-how__step-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" v-html="step.icon"></svg>
          </div>
          <h3>{{ step.title }}</h3>
          <p>{{ step.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from "vue";

const tab = ref("buyer");

const buyerSteps = [
  {
    title: "Browse & Discover",
    desc: "Search thousands of products from local stores. Filter by category, price, location, or store rating to find exactly what you need.",
    icon: `<circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>`,
  },
  {
    title: "Add to Cart & Checkout",
    desc: "Add items from one or multiple stores to your cart. Pay securely with M-Pesa, card, or cash on delivery — it's fast and simple.",
    icon: `<circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="1.8"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="1.8"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>`,
  },
  {
    title: "Receive & Review",
    desc: "Get your order delivered or arrange pickup. Once received, rate the seller and help others shop with confidence.",
    icon: `<path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>`,
  },
];

const sellerSteps = [
  {
    title: "Register Your Store",
    desc: "Create a free Bizaa account and set up your store in minutes. Upload your logo, write your description, and add your location.",
    icon: `<path d="M12 20h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>`,
  },
  {
    title: "List Your Products",
    desc: "Add your products with photos, prices, and descriptions. Use the Bizaa POS system to keep your inventory synced automatically.",
    icon: `<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="1.8"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>`,
  },
  {
    title: "Sell & Get Paid",
    desc: "Receive orders, manage deliveries, and get payments settled directly to your M-Pesa or bank account within 24 hours.",
    icon: `<rect x="2" y="7" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>`,
  },
];

const currentSteps = computed(() => tab.value === "buyer" ? buyerSteps : sellerSteps);
</script>
