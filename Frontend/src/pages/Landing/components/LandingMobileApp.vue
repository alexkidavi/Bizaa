<template>
  <section class="section app-section" id="app">
    <div class="container app__inner">
      <div class="app__visual" v-reveal="{ type: 'left' }">
        <div class="phone-mockup">
          <div class="phone-screen">
            <div class="phone-header">
              <div class="phone-status"></div>
            </div>
            <svg viewBox="0 0 220 360" fill="none" class="phone-svg" style="color: white">
              <!-- Top bar -->
              <rect width="220" height="44" fill="#229de7" />
              <rect
                x="12"
                y="14"
                width="80"
                height="8"
                rx="4"
                fill="white"
                opacity=".85"
              />
              <circle cx="200" cy="18" r="8" fill="white" opacity=".3" />
              <circle cx="180" cy="18" r="8" fill="white" opacity=".2" />
              <!-- stat cards -->
              <rect
                x="10"
                y="54"
                width="92"
                height="60"
                rx="10"
                fill="white"
                stroke="#e5e7eb"
              />
              <rect x="20" y="64" width="40" height="5" rx="2.5" fill="#94a3b8" />
              <text
                x="20"
                y="94"
                font-size="14"
                fill="#229de7"
                font-weight="800"
                font-family="sans-serif"
              >
                48,200
              </text>
              <rect
                x="20"
                y="100"
                width="30"
                height="4"
                rx="2"
                fill="#16a34a"
                opacity=".5"
              />
              <rect
                x="118"
                y="54"
                width="92"
                height="60"
                rx="10"
                fill="white"
                stroke="#e5e7eb"
              />
              <rect x="128" y="64" width="40" height="5" rx="2.5" fill="#94a3b8" />
              <text
                x="128"
                y="94"
                font-size="14"
                fill="#16a34a"
                font-weight="800"
                font-family="sans-serif"
              >
                142
              </text>
              <rect
                x="128"
                y="100"
                width="34"
                height="4"
                rx="2"
                fill="#229de7"
                opacity=".4"
              />
              <!-- chart -->
              <rect
                x="10"
                y="124"
                width="200"
                height="110"
                rx="10"
                fill="white"
                stroke="#e5e7eb"
              />
              <rect
                x="20"
                y="134"
                width="60"
                height="5"
                rx="2.5"
                fill="#374151"
                opacity=".4"
              />
              <rect x="24" y="200" width="18" height="28" rx="4" fill="#c5e9f9" />
              <rect x="50" y="188" width="18" height="40" rx="4" fill="#7cc8f0" />
              <rect x="76" y="175" width="18" height="53" rx="4" fill="#229de7" />
              <rect x="102" y="182" width="18" height="46" rx="4" fill="#5bb8f5" />
              <rect x="128" y="170" width="18" height="58" rx="4" fill="#229de7" />
              <rect x="154" y="178" width="18" height="50" rx="4" fill="#5bb8f5" />
              <rect x="180" y="184" width="18" height="44" rx="4" fill="#c5e9f9" />
              <!-- Bottom nav -->
              <rect x="0" y="320" width="220" height="40" fill="white" stroke="#e5e7eb" />
              <rect
                x="30"
                y="333"
                width="20"
                height="14"
                rx="4"
                fill="#229de7"
                opacity=".15"
              />
              <rect x="100" y="333" width="20" height="14" rx="4" fill="#e5e7eb" />
              <rect x="170" y="333" width="20" height="14" rx="4" fill="#e5e7eb" />
            </svg>
          </div>
        </div>
      </div>

      <div class="app__copy" v-reveal="{ type: 'right' }">
        <p class="label">Mobile App</p>
        <h2>Manage your business<br /><em>from your pocket</em></h2>
        <p>
          The Bizaa mobile app puts your POS, sales history, inventory alerts, and daily
          summaries right on your phone. Sell, check stock, and stay on top — even when
          you're away from the counter.
        </p>
        <ul class="app-features">
          <li v-for="item in appFeatures" :key="item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path
                d="M3 8.5l3.5 3.5 6.5-7"
                stroke="#229de7"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            {{ item }}
          </li>
        </ul>
        <div class="app-btns">
          <a
            href="https://play.google.com/store/apps/details?id=ke.co.bizaa"
            style="color: white"
            target="_blank"
            rel="noopener"
            class="app-store-btn app-store-btn--android"
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M3.18 23.76a2 2 0 0 0 2.05-.22l11.59-6.69-2.66-2.65L3.18 23.76zM20.8 10.38 17.46 8.5l-3 2.97 3 2.97 3.37-1.92a1.83 1.83 0 0 0 0-3.14zM2.01 1.24A1.8 1.8 0 0 0 1.5 2.5v19a1.8 1.8 0 0 0 .51 1.26l.07.07 10.64-10.64v-.25L2.08 1.17l-.07.07zM5.23.46l11.59 6.69-2.66 2.65L3.18.24A2 2 0 0 1 5.23.46z"
              />
            </svg>
            <div>
              <span class="store-sub">Get it on</span>
              <span class="store-name">Google Play</span>
            </div>
          </a>
          <router-link to="/register" class="btn btn--outline btn--md">
            Try web version
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
              <path
                d="M2 6.5h9M7 2.5l4 4-4 4"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { vReveal } from "../utils/reveal.js";

const appFeatures = [
  "Full POS — sell, refund, view history",
  "Real-time stock levels & low-stock alerts",
  "M-Pesa payment integration built in",
  "Works on Android — free to download",
];
</script>
