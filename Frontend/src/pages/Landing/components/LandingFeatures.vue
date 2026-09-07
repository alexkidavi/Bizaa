<template>
  <section class="section" id="features">
    <div class="container">
      <div class="section-header" v-reveal>
        <p class="label">Features</p>
        <h2>Everything in one place</h2>
        <p class="sub">No integrations to manage, no tab-switching. Bizaa handles the full operation.</p>
      </div>
      <div class="features-grid">
        <div
          class="feat-card"
          v-for="(f, i) in features"
          :key="f.title"
          v-reveal="{ type: 'zoom', delay: (i % 3) * 0.12 }"
        >
          <div class="feat-icon"><component :is="f.icon" /></div>
          <h3>{{ f.title }}</h3>
          <p>{{ f.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { mk } from "../utils/mk.js";
import { vReveal } from "../utils/reveal.js";

const IconPOS = mk([
  ["rect", { x: 2, y: 3, width: 20, height: 14, rx: 3, stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M8 21h8M12 17v4", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["path", { d: "M7 8h4M7 11h2", stroke: "currentColor", "stroke-width": 1.5, "stroke-linecap": "round" }],
]);

const IconInventory = mk([
  ["path", { d: "M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z", stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const IconHRM = mk([
  ["path", { d: "M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["circle", { cx: 9, cy: 7, r: 4, stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const IconReports = mk([
  ["line", { x1: 18, y1: 20, x2: 18, y2: 10, stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["line", { x1: 12, y1: 20, x2: 12, y2: 4, stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["line", { x1: 6, y1: 20, x2: 6, y2: 14, stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["line", { x1: 2, y1: 20, x2: 22, y2: 20, stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const IconBranches = mk([
  ["path", { d: "M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z", stroke: "currentColor", "stroke-width": 1.8 }],
  ["polyline", { points: "9 22 9 12 15 12 15 22", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const IconPurchases = mk([
  ["path", { d: "M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z", stroke: "currentColor", "stroke-width": 1.8 }],
  ["line", { x1: 3, y1: 6, x2: 21, y2: 6, stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M16 10a4 4 0 0 1-8 0", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const features = [
  { title: "Point of Sale", desc: "Fast checkout for cash, M-Pesa, and card. Built for high-volume retail environments.", icon: IconPOS },
  { title: "Inventory", desc: "Real-time stock across all locations. Reorder alerts so you never run out of top sellers.", icon: IconInventory },
  { title: "Human Resources", desc: "Staff management, shifts, attendance, payroll, and leave — all in one HR module.", icon: IconHRM },
  { title: "Reports & Analytics", desc: "Daily, weekly, and custom reports. Know your best products, busiest hours, and margins.", icon: IconReports },
  { title: "Multi-Branch", desc: "Manage multiple locations from one login. Transfer stock, compare branches, set permissions.", icon: IconBranches },
  { title: "Purchases", desc: "Create purchase orders, receive deliveries, and manage supplier relationships easily.", icon: IconPurchases },
];
</script>
