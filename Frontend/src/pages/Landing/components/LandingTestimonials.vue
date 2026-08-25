<template>
  <section class="section" id="testimonials">
    <div class="container">
      <div class="section-header">
        <p class="label">Testimonials</p>
        <h2>What our customers say</h2>
      </div>
      <div class="testi-grid">
        <div class="testi" v-for="t in testimonials" :key="t.name">
          <div class="testi__stars">
            <svg v-for="i in 5" :key="i" width="13" height="13" viewBox="0 0 13 13" fill="#f59e0b">
              <path d="M6.5.92l1.43 2.9 3.2.47-2.31 2.25.55 3.18L6.5 8.1 3.63 9.72l.55-3.18L1.87 4.3l3.2-.47L6.5.92z" />
            </svg>
          </div>
          <p>"{{ t.body }}"</p>
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
const testimonials = [
  {
    body: "Bizaa replaced three tools we were juggling. Our cashiers love it and I finally get clean daily reports without chasing anyone.",
    name: "Amina Wanjiku",
    role: "Owner, Wanjiku Supermarket, Nairobi",
    initials: "AW",
    color: "#229de7",
  },
  {
    body: "Opening our second branch used to be a nightmare. With Bizaa, we had it set up in one afternoon — stock transfer and all.",
    name: "Kevin Ochieng",
    role: "Director, Ochieng Hardware, Kisumu",
    initials: "KO",
    color: "#16a34a",
  },
  {
    body: "The HRM module saves us hours every payroll. Attendance, overtime, leaves — it's all there, automatic.",
    name: "Mary Njeri",
    role: "HR Manager, Njeri Foods Ltd, Mombasa",
    initials: "MN",
    color: "#dc2626",
  },
];
</script>
