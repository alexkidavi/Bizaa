<template>
  <header class="navbar" :class="{ 'navbar--scrolled': scrolled }">
    <div class="container navbar__inner">
      <a class="navbar__logo" @click.prevent="scrollTo('top')">
        <img
          src="/bizaa-logo.png"
          width="132"
          height="53"
          alt="Bizaa POS"
          class="navbar__logo-img"
        />
      </a>


      <nav class="navbar__links">
        <a @click.prevent="scrollTo('features')">Features</a>
        <a @click.prevent="scrollTo('app')">Mobile App</a>
        <a @click.prevent="scrollTo('why')">Why Bizaa</a>
        <a @click.prevent="scrollTo('pricing')">Pricing</a>
        <router-link to="/marketplace">Marketplace</router-link>
      </nav>

      <div class="navbar__actions">
        <router-link to="/login" class="btn btn--ghost">Sign in</router-link>
        <router-link to="/register" class="btn btn--primary btn--lg" style="color: white"
          >Get started</router-link
        >
      </div>

      <button class="navbar__burger" @click="mobileOpen = !mobileOpen" aria-label="Menu">
        <span :class="{ open: mobileOpen }"></span>
        <span :class="{ open: mobileOpen }"></span>
        <span :class="{ open: mobileOpen }"></span>
      </button>
    </div>

    <div class="navbar__mobile" :class="{ active: mobileOpen }">
      <a
        @click.prevent="
          scrollTo('features');
          mobileOpen = false;
        "
        >Features</a
      >
      <a
        @click.prevent="
          scrollTo('why');
          mobileOpen = false;
        "
        >Why Bizaa</a
      >
      <a
        @click.prevent="
          scrollTo('how');
          mobileOpen = false;
        "
        >How it works</a
      >
      <a
        @click.prevent="
          scrollTo('pricing');
          mobileOpen = false;
        "
        >Pricing</a
      >
      <div class="mobile-btns">
        <router-link to="/login" class="btn btn--ghost">Sign in</router-link>
        <router-link to="/register" class="btn btn--primary">Get started</router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const scrolled = ref(false);
const mobileOpen = ref(false);

function scrollTo(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: "smooth" });
  mobileOpen.value = false;
}

function onScroll() {
  scrolled.value = window.scrollY > 48;
}

onMounted(() => window.addEventListener("scroll", onScroll));
onUnmounted(() => window.removeEventListener("scroll", onScroll));
</script>
