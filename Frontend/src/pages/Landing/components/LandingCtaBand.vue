<template>
  <section class="cta-band" id="contact">
    <div class="container cta-band__inner">
      <div v-reveal>
        <h2>Ready to take control of your business?</h2>
        <p>Join 500+ businesses already running on Bizaa. Free to start — upgrade when you're ready.</p>
      </div>
      <div class="cta-band__btns" v-reveal="{ type: 'up', delay: 0.1 }">
        <router-link to="/register" class="btn btn--white btn--lg">Get started free</router-link>
        <router-link to="/login" class="btn btn--outline-white btn--lg">Sign in</router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { vReveal } from "../utils/reveal.js";
</script>
