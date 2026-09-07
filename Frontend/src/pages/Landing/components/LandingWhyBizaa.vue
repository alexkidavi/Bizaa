<template>
  <section class="section section--tinted" id="why">
    <div class="container why__inner">
      <div class="why__copy">
        <p class="label" v-reveal>Why Bizaa</p>
        <h2 v-reveal="{ delay: 0.08 }">Built for how East African businesses actually work</h2>
        <div class="why-list">
          <div
            class="why-item"
            v-for="(p, i) in whyPoints"
            :key="p.title"
            v-reveal="{ type: 'right', delay: 0.16 + i * 0.1 }"
          >
            <div class="why-item__icon"><component :is="p.icon" /></div>
            <div>
              <h4>{{ p.title }}</h4>
              <p>{{ p.desc }}</p>
            </div>
          </div>
        </div>
        <router-link
          to="/register"
          class="btn btn--primary btn--md"
          style="margin-top: 32px; color: white"
          v-reveal="{ delay: 0.6 }"
        >
          Start free — no card needed
        </router-link>
      </div>

      <div class="why__visual" v-reveal="{ type: 'left', delay: 0.1 }">
        <svg viewBox="0 0 400 360" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect
            x="0"
            y="0"
            width="400"
            height="360"
            rx="16"
            fill="white"
            stroke="#e5e7eb"
            stroke-width="1.5"
          />
          <!-- header -->
          <rect x="20" y="20" width="100" height="8" rx="4" fill="#111827" opacity=".5" />
          <rect x="20" y="36" width="160" height="6" rx="3" fill="#9ca3af" opacity=".5" />
          <!-- metric boxes -->
          <rect
            x="20"
            y="60"
            width="170"
            height="80"
            rx="10"
            fill="#f9fafb"
            stroke="#e5e7eb"
            stroke-width="1"
          />
          <rect x="32" y="72" width="52" height="6" rx="3" fill="#9ca3af" />
          <text
            x="32"
            y="108"
            font-size="20"
            fill="#111827"
            font-weight="700"
            font-family="sans-serif"
          >
            KSh 124K
          </text>
          <rect
            x="32"
            y="116"
            width="70"
            height="5"
            rx="2.5"
            fill="#16a34a"
            opacity=".5"
          />
          <rect
            x="210"
            y="60"
            width="170"
            height="80"
            rx="10"
            fill="#f9fafb"
            stroke="#e5e7eb"
            stroke-width="1"
          />
          <rect x="222" y="72" width="44" height="6" rx="3" fill="#9ca3af" />
          <text
            x="222"
            y="108"
            font-size="20"
            fill="#111827"
            font-weight="700"
            font-family="sans-serif"
          >
            +18.4%
          </text>
          <rect
            x="222"
            y="116"
            width="60"
            height="5"
            rx="2.5"
            fill="#229de7"
            opacity=".4"
          />
          <!-- donut -->
          <circle
            cx="120"
            cy="238"
            r="58"
            stroke="#e5e7eb"
            stroke-width="16"
            fill="none"
          />
          <circle
            cx="120"
            cy="238"
            r="58"
            stroke="#229de7"
            stroke-width="16"
            fill="none"
            stroke-dasharray="182 182"
            stroke-linecap="round"
            transform="rotate(-90 120 238)"
          />
          <circle
            cx="120"
            cy="238"
            r="58"
            stroke="#16a34a"
            stroke-width="16"
            fill="none"
            stroke-dasharray="91 273"
            stroke-dashoffset="-182"
            stroke-linecap="round"
            transform="rotate(-90 120 238)"
          />
          <circle
            cx="120"
            cy="238"
            r="58"
            stroke="#f59e0b"
            stroke-width="16"
            fill="none"
            stroke-dasharray="55 309"
            stroke-dashoffset="-273"
            stroke-linecap="round"
            transform="rotate(-90 120 238)"
          />
          <text
            x="98"
            y="244"
            font-size="12"
            fill="#374151"
            font-weight="600"
            font-family="sans-serif"
          >
            Revenue
          </text>
          <!-- legend -->
          <rect x="210" y="175" width="8" height="8" rx="2" fill="#229de7" />
          <rect x="210" y="193" width="8" height="8" rx="2" fill="#16a34a" />
          <rect x="210" y="211" width="8" height="8" rx="2" fill="#f59e0b" />
          <rect x="225" y="176" width="80" height="6" rx="3" fill="#9ca3af" />
          <rect x="225" y="194" width="70" height="6" rx="3" fill="#9ca3af" />
          <rect x="225" y="212" width="64" height="6" rx="3" fill="#9ca3af" />
          <!-- progress bars -->
          <rect
            x="210"
            y="240"
            width="170"
            height="100"
            rx="10"
            fill="#f9fafb"
            stroke="#e5e7eb"
            stroke-width="1"
          />
          <rect
            x="222"
            y="252"
            width="60"
            height="6"
            rx="3"
            fill="#374151"
            opacity=".4"
          />
          <rect x="222" y="265" width="146" height="6" rx="3" fill="#e5e7eb" />
          <rect
            x="222"
            y="265"
            width="98"
            height="6"
            rx="3"
            fill="#229de7"
            opacity=".5"
          />
          <rect x="222" y="279" width="146" height="6" rx="3" fill="#e5e7eb" />
          <rect
            x="222"
            y="279"
            width="120"
            height="6"
            rx="3"
            fill="#16a34a"
            opacity=".5"
          />
          <rect x="222" y="293" width="146" height="6" rx="3" fill="#e5e7eb" />
          <rect
            x="222"
            y="293"
            width="72"
            height="6"
            rx="3"
            fill="#f59e0b"
            opacity=".6"
          />
          <rect x="222" y="307" width="146" height="6" rx="3" fill="#e5e7eb" />
          <rect
            x="222"
            y="307"
            width="110"
            height="6"
            rx="3"
            fill="#229de7"
            opacity=".3"
          />
        </svg>
      </div>
    </div>
  </section>
</template>

<script setup>
import { mk } from "../utils/mk.js";
import { vReveal } from "../utils/reveal.js";

const IconSpeed = mk([
  [
    "path",
    {
      d: "M13 2L3 14h9l-1 8 10-12h-9l1-8z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);

const IconOffline = mk([
  [
    "path",
    {
      d: "M1 6l5.5 5.5M17.5 11.5L23 6",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "path",
    {
      d: "M8.53 8.53A9.93 9.93 0 0 1 12 8c1.9 0 3.68.53 5.19 1.45",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  ["circle", { cx: 12, cy: 17, r: 1.5, fill: "currentColor" }],
  [
    "line",
    {
      x1: 1,
      y1: 1,
      x2: 23,
      y2: 23,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);

const IconSecure = mk([
  [
    "path",
    {
      d: "M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
  [
    "path",
    {
      d: "M9 12l2 2 4-4",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);

const IconSupport = mk([
  [
    "path",
    {
      d: "M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);

const whyPoints = [
  {
    title: "Works on slow connections",
    desc:
      "Optimised for 3G networks. Every screen loads fast — even in areas with patchy internet.",
    icon: IconSpeed,
  },
  {
    title: "Offline-capable POS",
    desc:
      "Keep serving customers when the internet drops. Transactions sync automatically when you reconnect.",
    icon: IconOffline,
  },
  {
    title: "Role-based security",
    desc:
      "Granular permissions mean staff only see what they need to. Full audit logs for accountability.",
    icon: IconSecure,
  },
  {
    title: "Local support team",
    desc:
      "Help via WhatsApp or phone, in your timezone. Real people — not ticket queues.",
    icon: IconSupport,
  },
];
</script>
