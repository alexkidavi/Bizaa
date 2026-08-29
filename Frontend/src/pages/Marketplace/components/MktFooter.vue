<template>
  <footer class="mkt-footer">
    <div class="mkt-container">
      <div class="mkt-footer__grid">
        <div>
          <div class="mkt-footer__logo">
            <img src="/bizaa-logo.png" alt="Bizaa POS" />
            Market
          </div>
          <p class="mkt-footer__brand p">East Africa's growing marketplace connecting local businesses with customers across the region.</p>
          <div class="mkt-footer__app">
            <a href="https://play.google.com/store/apps/details?id=ke.co.bizaa" target="_blank" rel="noopener" class="mkt-footer__app-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3.18 23.76a2 2 0 0 0 2.05-.22l11.59-6.69-2.66-2.65L3.18 23.76zM20.8 10.38 17.46 8.5l-3 2.97 3 2.97 3.37-1.92a1.83 1.83 0 0 0 0-3.14zM2.01 1.24A1.8 1.8 0 0 0 1.5 2.5v19a1.8 1.8 0 0 0 .51 1.26l.07.07 10.64-10.64v-.25L2.08 1.17l-.07.07zM5.23.46l11.59 6.69-2.66 2.65L3.18.24A2 2 0 0 1 5.23.46z"/></svg>
              Google Play
            </a>
          </div>
          <div class="mkt-footer__socials" style="margin-top:16px">
            <a href="#" aria-label="Twitter"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg></a>
            <a href="#" aria-label="Facebook"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
            <a href="#" aria-label="Instagram"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
          </div>
        </div>

        <div class="mkt-footer__col">
          <p class="mkt-footer__col-head">Marketplace</p>
          <ul>
            <li><a href="#">Browse Products</a></li>
            <li><a href="#">All Stores</a></li>
            <li><a href="#">Flash Deals</a></li>
            <li><a href="#">New Arrivals</a></li>
            <li><a href="#">Categories</a></li>
          </ul>
        </div>

        <div class="mkt-footer__col">
          <p class="mkt-footer__col-head">Sell on Bizaa</p>
          <ul>
            <li><router-link to="/register">Open a Store</router-link></li>
            <li><a href="#">Seller Dashboard</a></li>
            <li><a href="#">Pricing & Fees</a></li>
            <li><a href="#">Seller Guidelines</a></li>
            <li><a href="#">Seller Support</a></li>
          </ul>
        </div>

        <div class="mkt-footer__col">
          <p class="mkt-footer__col-head">Support</p>
          <ul>
            <li><a href="#">Help Centre</a></li>
            <li><a href="https://wa.me/254705030613" target="_blank">WhatsApp Us</a></li>
            <li><router-link to="/privacy">Privacy Policy</router-link></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Returns Policy</a></li>
          </ul>
        </div>
      </div>

      <div class="mkt-footer__bottom">
        <span>© {{ new Date().getFullYear() }} Bizaa Market by Brimsoft Solutions. All rights reserved.</span>
        <div class="mkt-footer__legal">
          <router-link to="/privacy">Privacy</router-link>
          <a href="#">Terms</a>
          <a href="#">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
</template>
