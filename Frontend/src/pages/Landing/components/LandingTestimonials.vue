<template>
  <section class="section" id="testimonials">
    <div class="container">
      <div class="section-header" v-reveal>
        <p class="label">Testimonials</p>
        <h2>What our customers say</h2>
      </div>
      <div class="testi-grid">
        <div
          class="testi"
          v-for="(t, i) in testimonials"
          :key="t.name"
          v-reveal="{ type: 'up', delay: i * 0.12 }"
        >
          <svg class="testi__quote" width="34" height="26" viewBox="0 0 34 26" fill="none">
            <path
              d="M14.9 0 9.6 12.2H15v13.6H0V13.4L6.5 0h8.4Zm19 0-5.3 12.2H34v13.6H19V13.4L25.5 0h8.4Z"
              fill="currentColor"
            />
          </svg>
          <div class="testi__stars">
            <svg v-for="i in 5" :key="i" width="13" height="13" viewBox="0 0 13 13" fill="var(--bizaa-orange)">
              <path d="M6.5.92l1.43 2.9 3.2.47-2.31 2.25.55 3.18L6.5 8.1 3.63 9.72l.55-3.18L1.87 4.3l3.2-.47L6.5.92z" />
            </svg>
          </div>
          <p>{{ t.body }}</p>
          <div class="testi__author">
            <div class="testi__av" :style="{ background: t.color }">{{ t.initials }}</div>
            <div>
              <strong>{{ t.name }}</strong>
              <span>{{ t.role }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { vReveal } from "../utils/reveal.js";

const testimonials = [
  {
    body: "Bizaa replaced three tools we were juggling. Our cashiers love it and I finally get clean daily reports without chasing anyone.",
    name: "Amina Wanjiku",
    role: "Owner, Wanjiku Supermarket, Nairobi",
    initials: "AW",
    color: "var(--bizaa-blue)",
  },
  {
    body: "Opening our second branch used to be a nightmare. With Bizaa, we had it set up in one afternoon — stock transfer and all.",
    name: "Kevin Ochieng",
    role: "Director, Ochieng Hardware, Kisumu",
    initials: "KO",
    color: "var(--bizaa-orange)",
  },
  {
    body: "The HRM module saves us hours every payroll. Attendance, overtime, leaves — it's all there, automatic.",
    name: "Mary Njeri",
    role: "HR Manager, Njeri Foods Ltd, Mombasa",
    initials: "MN",
    color: "#0f172a",
  },
];
</script>
