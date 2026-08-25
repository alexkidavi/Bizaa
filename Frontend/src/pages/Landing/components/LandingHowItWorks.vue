<template>
  <section class="section" id="how">
    <div class="container">
      <div class="section-header">
        <p class="label">How it works</p>
        <h2>Up and running in minutes</h2>
        <p class="sub">No IT team. No complex setup. Just sign up and start selling.</p>
      </div>
      <div class="steps">
        <div class="step" v-for="(s, i) in steps" :key="i">
          <div class="step__num">{{ String(i + 1).padStart(2, "0") }}</div>
          <div class="step__icon"><component :is="s.icon" /></div>
          <h3>{{ s.title }}</h3>
          <p>{{ s.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { mk } from "../utils/mk.js";

const IconSetup = mk([
  ["path", { d: "M12 20h9", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["path", { d: "M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round", "stroke-linejoin": "round" }],
]);

const IconImport = mk([
  ["path", { d: "M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
  ["polyline", { points: "7 10 12 15 17 10", stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round", "stroke-linejoin": "round" }],
  ["line", { x1: 12, y1: 15, x2: 12, y2: 3, stroke: "currentColor", "stroke-width": 1.8, "stroke-linecap": "round" }],
]);

const IconGo = mk([
  ["circle", { cx: 12, cy: 12, r: 10, stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M10 8l6 4-6 4V8z", fill: "currentColor", opacity: 0.6 }],
]);

const steps = [
  { title: "Create your account", desc: "Sign up in under 2 minutes. No credit card, no contracts. Just an email.", icon: IconSetup },
  { title: "Import your data", desc: "Bring in products, customers, and staff via CSV — or start fresh from scratch.", icon: IconImport },
  { title: "Start selling", desc: "Open the POS, process your first sale, and watch your dashboard come alive.", icon: IconGo },
];
</script>
