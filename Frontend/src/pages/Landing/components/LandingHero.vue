<template>
  <section class="hero" id="hero">
    <div class="container hero__inner">
      <div class="hero__text">
        <h1>
          One platform.<br />
          Every part of your<br />
          <em>business, covered.</em>
        </h1>

        <p>
          POS, inventory, HRM, purchases, and analytics in one fast, simple platform.
          Designed for the way businesses actually operate in Kenya and beyond.
        </p>

        <div class="hero__ctas">
          <router-link
            to="/register"
            class="btn btn--primary btn--lg"
            style="color: white"
            >Start for free</router-link
          >
          <a @click.prevent="scrollTo('how')" class="btn btn--text btn--lg">
            See how it works
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path
                d="M2 7h10M7 2l5 5-5 5"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </a>
        </div>

        <div class="hero__meta">
          <div class="hero__avatars">
            <div class="ha" style="--c: #229de7">KM</div>
            <div class="ha" style="--c: #16a34a">AW</div>
            <div class="ha" style="--c: #dc2626">JO</div>
            <div class="ha" style="--c: #d97706">MN</div>
          </div>
          <span>Trusted by <strong>500+</strong> businesses across East Africa</span>
        </div>
      </div>

      <div class="hero__visual">
        <div class="hero-photo">
          <picture>
            <source srcset="/hero.avif" type="image/avif" />
            <source srcset="/hero.webp" type="image/webp" />
            <img
              src="/hero.jpg"
              alt="A shop attendant ringing up a sale on Bizaa POS"
              width="1000"
              height="667"
              fetchpriority="high"
              class="hero-photo__img"
            />
          </picture>
          <div class="hero-badge hero-badge--sale">
            <span class="hero-badge__icon">✓</span>
            <div>
              <strong>Sale completed</strong>
              <span>KES 411.80</span>
            </div>
          </div>
          <div class="hero-badge hero-badge--sync">
            <span class="hero-badge__dot"></span>
            Synced across branches
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
function scrollTo(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: "smooth" });
}
</script>
