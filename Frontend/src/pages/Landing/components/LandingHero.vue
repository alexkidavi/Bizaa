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
        <div class="dash-frame">
          <div class="dash-titlebar">
            <div class="dash-dots">
              <span class="dd dd--r"></span><span class="dd dd--y"></span
              ><span class="dd dd--g"></span>
            </div>
            <span class="dash-url">app.bizaa.co.ke/dashboard</span>
          </div>
          <svg
            class="dash-svg"
            viewBox="0 0 500 320"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <!-- sidebar -->
            <rect width="72" height="320" fill="#f9fafb" />
            <rect
              x="12"
              y="20"
              width="48"
              height="7"
              rx="3.5"
              fill="#229de7"
              opacity=".5"
            />
            <rect x="12" y="44" width="28" height="5" rx="2.5" fill="#d1d5db" />
            <rect x="12" y="57" width="34" height="5" rx="2.5" fill="#e5e7eb" />
            <rect
              x="12"
              y="70"
              width="30"
              height="5"
              rx="2.5"
              fill="#229de7"
              opacity=".25"
            />
            <rect x="12" y="83" width="36" height="5" rx="2.5" fill="#e5e7eb" />
            <rect x="12" y="96" width="28" height="5" rx="2.5" fill="#e5e7eb" />
            <rect x="12" y="109" width="32" height="5" rx="2.5" fill="#e5e7eb" />
            <rect x="12" y="122" width="26" height="5" rx="2.5" fill="#e5e7eb" />
            <!-- topbar -->
            <rect
              x="72"
              y="0"
              width="428"
              height="34"
              fill="white"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <rect x="84" y="11" width="90" height="12" rx="6" fill="#f3f4f6" />
            <circle cx="464" cy="17" r="8" fill="#e5e7eb" />
            <circle cx="444" cy="17" r="8" fill="#f3f4f6" />
            <!-- stats -->
            <rect
              x="84"
              y="46"
              width="92"
              height="58"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect x="94" y="56" width="40" height="5" rx="2.5" fill="#9ca3af" />
            <rect
              x="94"
              y="67"
              width="62"
              height="11"
              rx="3"
              fill="#0f6ca8"
              opacity=".06"
            />
            <text
              x="94"
              y="80"
              font-size="12"
              fill="#229de7"
              font-weight="700"
              font-family="sans-serif"
            >
              KSh 48,200
            </text>
            <rect
              x="94"
              y="87"
              width="28"
              height="4"
              rx="2"
              fill="#16a34a"
              opacity=".5"
            />
            <rect
              x="184"
              y="46"
              width="92"
              height="58"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect x="194" y="56" width="44" height="5" rx="2.5" fill="#9ca3af" />
            <rect x="194" y="67" width="50" height="11" rx="3" fill="#f0fdf4" />
            <text
              x="194"
              y="80"
              font-size="12"
              fill="#16a34a"
              font-weight="700"
              font-family="sans-serif"
            >
              142 Sales
            </text>
            <rect
              x="194"
              y="87"
              width="36"
              height="4"
              rx="2"
              fill="#16a34a"
              opacity=".4"
            />
            <rect
              x="284"
              y="46"
              width="92"
              height="58"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect x="294" y="56" width="48" height="5" rx="2.5" fill="#9ca3af" />
            <text
              x="294"
              y="80"
              font-size="12"
              fill="#d97706"
              font-weight="700"
              font-family="sans-serif"
            >
              37 Orders
            </text>
            <rect
              x="294"
              y="87"
              width="30"
              height="4"
              rx="2"
              fill="#d97706"
              opacity=".4"
            />
            <rect
              x="384"
              y="46"
              width="92"
              height="58"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect x="394" y="56" width="44" height="5" rx="2.5" fill="#9ca3af" />
            <text
              x="394"
              y="80"
              font-size="12"
              fill="#dc2626"
              font-weight="700"
              font-family="sans-serif"
            >
              8 Alerts
            </text>
            <rect
              x="394"
              y="87"
              width="24"
              height="4"
              rx="2"
              fill="#dc2626"
              opacity=".4"
            />
            <!-- chart -->
            <rect
              x="84"
              y="116"
              width="240"
              height="130"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect
              x="94"
              y="126"
              width="72"
              height="6"
              rx="3"
              fill="#374151"
              opacity=".4"
            />
            <rect
              x="94"
              y="136"
              width="48"
              height="4"
              rx="2"
              fill="#9ca3af"
              opacity=".5"
            />
            <rect x="100" y="206" width="14" height="34" rx="3" fill="#d0edf8" />
            <rect x="120" y="196" width="14" height="44" rx="3" fill="#c5e9f9" />
            <rect x="140" y="183" width="14" height="57" rx="3" fill="#7cc8f0" />
            <rect x="160" y="190" width="14" height="50" rx="3" fill="#c5e9f9" />
            <rect x="180" y="170" width="14" height="70" rx="3" fill="#229de7" />
            <rect x="200" y="178" width="14" height="62" rx="3" fill="#42b5f5" />
            <rect x="220" y="185" width="14" height="55" rx="3" fill="#7cc8f0" />
            <rect x="240" y="173" width="14" height="67" rx="3" fill="#229de7" />
            <rect x="260" y="180" width="14" height="60" rx="3" fill="#42b5f5" />
            <rect x="280" y="188" width="14" height="52" rx="3" fill="#c5e9f9" />
            <path
              d="M107 222 L127 208 L147 193 L167 200 L187 178 L207 184 L227 189 L247 176 L267 183 L287 194"
              stroke="#229de7"
              stroke-width="1.5"
              fill="none"
              opacity=".6"
            />
            <!-- recent list -->
            <rect
              x="332"
              y="116"
              width="144"
              height="130"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect
              x="342"
              y="126"
              width="60"
              height="6"
              rx="3"
              fill="#374151"
              opacity=".4"
            />
            <rect x="342" y="140" width="1" height="96" fill="#f3f4f6" />
            <rect x="342" y="148" width="66" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="148"
              width="46"
              height="5"
              rx="2.5"
              fill="#16a34a"
              opacity=".6"
            />
            <rect x="342" y="161" width="58" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="161"
              width="38"
              height="5"
              rx="2.5"
              fill="#16a34a"
              opacity=".6"
            />
            <rect x="342" y="174" width="64" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="174"
              width="42"
              height="5"
              rx="2.5"
              fill="#16a34a"
              opacity=".6"
            />
            <rect x="342" y="187" width="52" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="187"
              width="34"
              height="5"
              rx="2.5"
              fill="#d97706"
              opacity=".7"
            />
            <rect x="342" y="200" width="60" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="200"
              width="40"
              height="5"
              rx="2.5"
              fill="#dc2626"
              opacity=".6"
            />
            <rect x="342" y="213" width="56" height="5" rx="2.5" fill="#d1d5db" />
            <rect
              x="418"
              y="213"
              width="44"
              height="5"
              rx="2.5"
              fill="#16a34a"
              opacity=".6"
            />
            <!-- bottom band -->
            <rect
              x="84"
              y="256"
              width="392"
              height="52"
              rx="8"
              fill="white"
              stroke="#e5e7eb"
              stroke-width="1"
            />
            <rect
              x="94"
              y="266"
              width="64"
              height="5"
              rx="2.5"
              fill="#374151"
              opacity=".35"
            />
            <rect x="94" y="278" width="372" height="6" rx="3" fill="#f3f4f6" />
            <rect
              x="94"
              y="278"
              width="232"
              height="6"
              rx="3"
              fill="#229de7"
              opacity=".35"
            />
            <rect x="94" y="291" width="372" height="6" rx="3" fill="#f3f4f6" />
            <rect
              x="94"
              y="291"
              width="298"
              height="6"
              rx="3"
              fill="#16a34a"
              opacity=".35"
            />
          </svg>
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
