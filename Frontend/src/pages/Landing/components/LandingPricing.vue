<template>
  <section class="section section--tinted" id="pricing">
    <div class="container">
      <div class="section-header" v-reveal>
        <p class="label">Pricing</p>
        <h2>Transparent pricing</h2>
        <p class="sub">Start with a free 14-day trial. Pay only when you're ready. No hidden fees.</p>
      </div>

      <!-- Loading skeleton -->
      <div v-if="plansLoading" class="plans">
        <div v-for="i in 4" :key="i" class="plan plan--skeleton">
          <div class="skel skel--name"></div>
          <div class="skel skel--price"></div>
          <div class="skel skel--btn"></div>
          <div class="skel skel--line" v-for="j in 4" :key="j"></div>
        </div>
      </div>

      <div v-else class="plans">
        <div
          class="plan"
          v-for="(p, i) in plans"
          :key="p.id"
          :class="{ 'plan--featured': p.popular }"
          v-reveal="{ type: 'up', delay: i * 0.1 }"
        >
          <div v-if="p.popular" class="plan__badge">Most popular</div>
          <p class="plan__name">{{ p.name }}</p>
          <p class="plan__tagline">Billed per {{ p.periodLabel }}</p>
          <div class="plan__price">
            <span class="currency">KES</span>
            <span class="amount">{{ p.price.toLocaleString() }}</span>
            <span class="per">/{{ p.periodLabel }}</span>
          </div>
          <div v-if="p.savings" class="plan__savings">{{ p.savings }}</div>
          <router-link
            to="/register"
            class="btn btn--md"
            :class="p.popular ? 'btn--primary' : 'btn--outline'"
          >
            {{ p.cta }}
          </router-link>
          <ul>
            <li v-for="f in p.features" :key="f">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2.5 7.5l3 3 6-6" stroke="#16a34a" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ f }}
            </li>
          </ul>
        </div>
      </div>

      <p class="plans__trial-note" v-reveal>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
          <path d="M7 1.75a5.25 5.25 0 1 0 0 10.5A5.25 5.25 0 0 0 7 1.75zM7 4.5v3.25l2 1" stroke="#229de7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Every new account gets a <strong>14-day free trial</strong> — no card required.
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { vReveal } from "../utils/reveal.js";

const rawPlans = ref([]);
const plansLoading = ref(true);

const periodLabel = (p) => {
  if (p.billing_period === "monthly") return "month";
  if (p.billing_period === "quarterly") return "quarter";
  if (p.billing_period === "annually") return "year";
  return p.billing_period;
};

const savingsBadge = (p) => {
  if (p.slug === "quarterly") return "Save KES 100";
  if (p.slug === "annually") return "Save KES 1,000";
  return "";
};

const plans = computed(() =>
  rawPlans.value.map((p) => ({
    ...p,
    popular: p.slug === "quarterly",
    periodLabel: periodLabel(p),
    savings: savingsBadge(p),
    cta: "Get started",
  }))
);

onMounted(async () => {
  try {
    const res = await axios.get("/api/subscription-plans");
    rawPlans.value = res.data?.data ?? [];
  } catch {
  } finally {
    plansLoading.value = false;
  }
});
</script>
