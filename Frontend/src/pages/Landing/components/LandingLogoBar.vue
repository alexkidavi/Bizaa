<template>
  <section class="statbar">
    <div class="container statbar__inner">
      <div class="statbar__stats">
        <div class="statbar__stat" v-reveal="{ type: 'up' }">
          <span class="statbar__num">500+</span>
          <span class="statbar__label">Businesses trust Bizaa</span>
        </div>
        <div class="statbar__divider" v-reveal="{ type: 'line-y', delay: 0.05 }"></div>
        <div class="statbar__stat" v-reveal="{ type: 'up', delay: 0.1 }">
          <span class="statbar__num">6</span>
          <span class="statbar__label">Cities across East Africa</span>
        </div>
        <div class="statbar__divider" v-reveal="{ type: 'line-y', delay: 0.15 }"></div>
        <div class="statbar__stat" v-reveal="{ type: 'up', delay: 0.2 }">
          <span class="statbar__num">14-Day</span>
          <span class="statbar__label">Free trial, no card required</span>
        </div>
      </div>

      <div class="statbar__cities" v-reveal>
        <span>Nairobi</span><span>Mombasa</span><span>Kisumu</span>
        <span>Nakuru</span><span>Eldoret</span><span>Kampala</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { vReveal } from "../utils/reveal.js";
</script>
