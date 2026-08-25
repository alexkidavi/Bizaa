<template>
  <section class="mkt-seller-cta">
    <div class="mkt-container">
      <div class="mkt-seller-cta__inner">
        <div class="mkt-seller-cta__text">
          <p class="mkt-label">Grow your business</p>
          <h2>Open your store on<br />Bizaa Market today</h2>
          <p>Join hundreds of sellers already reaching thousands of customers across East Africa. Free to start — no monthly fees, no setup costs.</p>
        </div>

        <div class="mkt-seller-cta__stats">
          <div class="mkt-seller-stat">
            <div class="mkt-seller-stat__value">8+</div>
            <div class="mkt-seller-stat__label">Active Stores</div>
          </div>
          <div class="mkt-seller-stat">
            <div class="mkt-seller-stat__value">2,400+</div>
            <div class="mkt-seller-stat__label">Products Listed</div>
          </div>
          <div class="mkt-seller-stat">
            <div class="mkt-seller-stat__value">6</div>
            <div class="mkt-seller-stat__label">Cities Covered</div>
          </div>
        </div>

        <div class="mkt-seller-cta__actions">
          <router-link to="/register" class="mkt-btn mkt-btn--primary mkt-btn--lg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Open your store free
          </router-link>
          <router-link to="/marketplace" class="mkt-btn mkt-btn--lg" style="border-color:rgba(255,255,255,0.3);color:#fff;background:rgba(255,255,255,0.08)">
            Learn more →
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>
