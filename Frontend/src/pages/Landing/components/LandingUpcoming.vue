<template>
  <section class="section section--gradient" id="upcoming">
    <div class="container">
      <div class="section-header">
        <div class="upcoming-badge">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg>
          Product Roadmap
        </div>
        <h2>The future of <span class="gradient-text">business management</span></h2>
        <p class="sub">
          From features that are live today to what's shipping next — here's exactly where
          we're headed.
        </p>
      </div>

      <div class="roadmap">
        <div class="roadmap-timeline">
          <div class="timeline-line"></div>
          <div class="timeline-dot" style="--delay: 1"></div>
          <div class="timeline-dot" style="--delay: 2"></div>
          <div class="timeline-dot" style="--delay: 3"></div>
          <div class="timeline-dot" style="--delay: 4"></div>
        </div>

        <div class="roadmap-grid">
          <div
            class="roadmap-card"
            :class="`roadmap-card--${item.phase}`"
            v-for="(item, idx) in upcoming"
            :key="item.title"
            :style="{ '--delay': idx + 1 }"
          >
            <div class="roadmap-card__top">
              <div class="roadmap-card__icon"><component :is="item.icon" /></div>
              <span class="roadmap-card__phase-badge">
                <svg
                  v-if="item.phase === 'launched'"
                  width="10"
                  height="10"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                >
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <svg
                  v-else-if="item.phase === 'beta'"
                  width="10"
                  height="10"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                >
                  <path
                    d="M12 2a7 7 0 0 1 7 7c0 4-3 6-3 9H8c0-3-3-5-3-9a7 7 0 0 1 7-7z"
                  />
                  <line x1="8" y1="22" x2="16" y2="22" />
                  <line x1="12" y1="2" x2="12" y2="7" />
                </svg>
                <svg
                  v-else-if="item.phase === 'progress'"
                  width="10"
                  height="10"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                >
                  <path d="M21 12a9 9 0 1 1-6.22-8.56" />
                </svg>
                <svg
                  v-else
                  width="10"
                  height="10"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                >
                  <circle cx="12" cy="12" r="3" />
                  <path
                    d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"
                  />
                </svg>
                {{ item.phaseLabel }}
              </span>
            </div>

            <div class="roadmap-card__status">{{ item.status }}</div>
            <h3>{{ item.title }}</h3>
            <p>{{ item.desc }}</p>

            <div class="roadmap-card__footer">
              <template v-if="item.phase === 'launched'">
                <div class="roadmap-card__live">
                  <span class="live-dot"></span>
                  Available now
                </div>
              </template>
              <template v-else>
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: item.progress }"></div>
                </div>
                <div class="roadmap-card__progress-row">
                  <span class="progress-text">{{ item.progress }} complete</span>
                  <span class="progress-text">{{ item.eta }}</span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <div class="early-access">
        <div class="early-access__content">
          <div>
            <h3>Want early access?</h3>
            <p>Join the waitlist and be first when these features ship.</p>
          </div>
          <button class="btn btn--primary btn--md early-access__btn">
            Join waitlist
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path
                d="M3 8h10M8 3l5 5-5 5"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { mk } from "../utils/mk.js";

const BusinessManagerIcon = mk([
  [
    "rect",
    {
      x: 2,
      y: 3,
      width: 20,
      height: 14,
      rx: 2,
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "line",
    {
      x1: 8,
      y1: 21,
      x2: 16,
      y2: 21,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "line",
    { x1: 12, y1: 17, x2: 12, y2: 21, stroke: "currentColor", "stroke-width": 1.8 },
  ],
  [
    "path",
    {
      d: "M7 8h4M7 11h6",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  ["circle", { cx: 16, cy: 9, r: 2, stroke: "currentColor", "stroke-width": 1.8 }],
]);

const TruckIcon = mk([
  [
    "rect",
    {
      x: 1,
      y: 3,
      width: 15,
      height: 13,
      rx: 1,
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "path",
    {
      d: "M16 8h4l3 5v4h-7V8z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linejoin": "round",
    },
  ],
  ["circle", { cx: 5.5, cy: 18.5, r: 2.5, stroke: "currentColor", "stroke-width": 1.8 }],
  ["circle", { cx: 18.5, cy: 18.5, r: 2.5, stroke: "currentColor", "stroke-width": 1.8 }],
]);

const ShoppingBagIcon = mk([
  [
    "path",
    {
      d: "M6 3L3 6v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-3z",
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  ["line", { x1: 3, y1: 8, x2: 21, y2: 8, stroke: "currentColor", "stroke-width": 1.8 }],
  [
    "path",
    {
      d: "M16 12a4 4 0 0 1-8 0",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);

const CreditCardIcon = mk([
  [
    "rect",
    {
      x: 2,
      y: 6,
      width: 20,
      height: 12,
      rx: 2,
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "line",
    { x1: 2, y1: 12, x2: 22, y2: 12, stroke: "currentColor", "stroke-width": 1.8 },
  ],
  [
    "line",
    {
      x1: 7,
      y1: 15,
      x2: 10,
      y2: 15,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);

const upcoming = [
  {
    title: "Bizaa Business Manager",
    desc:
      "The all-in-one business hub — inventory management, sales tracking, point of sale, staff management, and real-time reports. Live and powering hundreds of shops today.",
    icon: BusinessManagerIcon,
    status: "Launched · 2024",
    phase: "launched",
    phaseLabel: "Launched",
    progress: "100%",
    eta: "",
  },
  {
    title: "Bizaa B2B",
    desc:
      "Connect with verified distributors and wholesalers across Kenya. Order stock in bulk and get it delivered directly to your shop.",
    icon: TruckIcon,
    status: "Q2 2026",
    phase: "beta",
    phaseLabel: "Beta",
    progress: "70%",
    eta: "Jun 2026",
  },
  {
    title: "Bizaa Marketplace",
    desc:
      "Expand your reach beyond your physical location. List products, receive orders, and grow your customer base across East Africa.",
    icon: ShoppingBagIcon,
    status: "Q3 2026",
    phase: "progress",
    phaseLabel: "In Progress",
    progress: "40%",
    eta: "Sep 2026",
  },
  {
    title: "Bizaa Credit",
    desc:
      "No stock, no cash? Not a problem. Get inventory on credit from your supplier and pay as you sell — zero upfront cost.",
    icon: CreditCardIcon,
    status: "Q4 2026",
    phase: "planned",
    phaseLabel: "Planned",
    progress: "15%",
    eta: "Dec 2026",
  },
];
</script>
