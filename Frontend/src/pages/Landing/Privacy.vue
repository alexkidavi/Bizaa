<template>
  <div class="landing" id="top">
    <!-- ─── NAVBAR ─────────────────────────────────────────────── -->
    <header class="navbar" :class="{ 'navbar--scrolled': scrolled }">
      <div class="container navbar__inner">
        <a class="navbar__logo" @click.prevent="scrollTo('top')">
          <img
            src="/logo.webp"
            width="100"
            height="90"
            alt="Bizaa"
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
          <a href="/login" class="btn btn--ghost">Sign in</a>
          <a href="/register" class="btn btn--primary btn--lg">Get started</a>
        </div>

        <button
          class="navbar__burger"
          @click="mobileOpen = !mobileOpen"
          aria-label="Menu"
        >
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
          <a href="/login" class="btn btn--ghost">Sign in</a>
          <a href="/register" class="btn btn--primary">Get started</a>
        </div>
      </div>
    </header>

    <!-- ─── PRIVACY POLICY SECTION ───────────────────────────── -->
    <section id="privacy" class="section section--tinted">
      <div class="container">
        <div class="section-header">
          <span class="label">Legal</span>
          <h2>Privacy Policy</h2>
          <p class="sub">How Bizaa collects, uses, and protects your information.</p>
        </div>

        <div class="policy">
          <h3>1. Information we collect</h3>
          <p>
            We collect account details such as name, email, and business data you enter
            into Bizaa (products, sales, inventory, and staff information).
          </p>

          <h3>2. How we use your data</h3>
          <p>
            Your data is used only to provide POS, inventory, HR, and reporting services.
            We do not sell your personal or business data.
          </p>

          <h3>3. Data security</h3>
          <p>
            We use secure servers, encryption, and access control to protect your
            information against unauthorized access.
          </p>

          <h3>4. Data sharing</h3>
          <p>
            We only share data when required by law or when you explicitly authorize
            integrations.
          </p>

          <h3>5. Your rights</h3>
          <p>
            You can request access, correction, or deletion of your data at any time by
            contacting support.
          </p>

          <h3>6. Contact</h3>
          <p>For privacy concerns, email us at brimsoftsolutions@gmail.com</p>
        </div>
      </div>
    </section>
    <!-- ─── FOOTER ─────────────────────────────────────────────── -->
    <footer class="footer">
      <div class="container footer__top">
        <div class="footer__brand">
          <a class="navbar__logo" @click.prevent="scrollTo('top')">
            <img
              src="/logo.webp"
              width="100"
              height="90"
              alt="Bizaa"
              class="navbar__logo-img"
            />
          </a>
          <p>Smart business management for modern East African enterprises.</p>
          <div class="footer__contact">
            <a
              href="https://wa.me/254705030613"
              target="_blank"
              rel="noopener"
              class="footer__contact-item"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"
                />
              </svg>
              WhatsApp: +254 705 030 613
            </a>
            <a href="tel:+254705030613" class="footer__contact-item">
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.17h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6 6l.93-.94a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                />
              </svg>
              +254 705 030 613
            </a>
            <a href="mailto:brimsoftsolutions@gmail.com" class="footer__contact-item">
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path
                  d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
                />
                <polyline points="22,6 12,13 2,6" />
              </svg>
              brimsoftsolutions@gmail.com
            </a>
          </div>
          <div class="footer__socials">
            <a href="#" aria-label="Twitter"
              ><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"
                /></svg
            ></a>
            <a href="#" aria-label="LinkedIn"
              ><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"
                />
                <circle cx="4" cy="4" r="2" /></svg
            ></a>
            <a href="#" aria-label="Facebook"
              ><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"
                /></svg
            ></a>
          </div>
        </div>
        <div class="footer__col" v-for="col in footerLinks" :key="col.heading">
          <p class="footer__col-head">{{ col.heading }}</p>
          <ul>
            <li v-for="l in col.links" :key="l.label">
              <a :href="l.href">{{ l.label }}</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="container footer__bottom">
        <span
          >© {{ new Date().getFullYear() }} Bizaa by Brimsoft Solutions. All rights
          reserved.</span
        >
        <div class="footer__legal">
          <a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, h } from "vue";
import axios from "axios";

const scrolled = ref(false);
const mobileOpen = ref(false);

function scrollTo(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: "smooth" });
  mobileOpen.value = false;
}
onMounted(async () => {
  window.addEventListener("scroll", () => {
    scrolled.value = window.scrollY > 48;
  });
  try {
    const res = await axios.get("/api/subscription-plans");
    rawPlans.value = res.data?.data ?? [];
  } catch {
  } finally {
    plansLoading.value = false;
  }
});
onUnmounted(() => window.removeEventListener("scroll", () => {}));

// ── Icons ────────────────────────────────────────────────────────
const mk = (paths) => ({
  render: () =>
    h(
      "svg",
      { viewBox: "0 0 24 24", fill: "none", width: 20, height: 20 },
      paths.map((p) => h(p[0], p[1]))
    ),
});

const IconPOS = mk([
  [
    "rect",
    {
      x: 2,
      y: 3,
      width: 20,
      height: 14,
      rx: 3,
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "path",
    {
      d: "M8 21h8M12 17v4",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "path",
    {
      d: "M7 8h4M7 11h2",
      stroke: "currentColor",
      "stroke-width": 1.5,
      "stroke-linecap": "round",
    },
  ],
]);
const IconInventory = mk([
  [
    "path",
    {
      d:
        "M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z",
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "path",
    {
      d: "M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconHRM = mk([
  [
    "path",
    {
      d: "M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  ["circle", { cx: 9, cy: 7, r: 4, stroke: "currentColor", "stroke-width": 1.8 }],
  [
    "path",
    {
      d: "M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconReports = mk([
  [
    "line",
    {
      x1: 18,
      y1: 20,
      x2: 18,
      y2: 10,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "line",
    {
      x1: 12,
      y1: 20,
      x2: 12,
      y2: 4,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "line",
    {
      x1: 6,
      y1: 20,
      x2: 6,
      y2: 14,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "line",
    {
      x1: 2,
      y1: 20,
      x2: 22,
      y2: 20,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconBranches = mk([
  [
    "path",
    {
      d: "M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z",
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  [
    "polyline",
    {
      points: "9 22 9 12 15 12 15 22",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconPurchases = mk([
  [
    "path",
    {
      d: "M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z",
      stroke: "currentColor",
      "stroke-width": 1.8,
    },
  ],
  ["line", { x1: 3, y1: 6, x2: 21, y2: 6, stroke: "currentColor", "stroke-width": 1.8 }],
  [
    "path",
    {
      d: "M16 10a4 4 0 0 1-8 0",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconSpeed = mk([
  [
    "path",
    {
      d: "M13 2L3 14h9l-1 8 10-12h-9l1-8z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);
const IconOffline = mk([
  [
    "path",
    {
      d: "M1 6l5.5 5.5M17.5 11.5L23 6",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "path",
    {
      d: "M8.53 8.53A9.93 9.93 0 0 1 12 8c1.9 0 3.68.53 5.19 1.45",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  ["circle", { cx: 12, cy: 17, r: 1.5, fill: "currentColor" }],
  [
    "line",
    {
      x1: 1,
      y1: 1,
      x2: 23,
      y2: 23,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconSecure = mk([
  [
    "path",
    {
      d: "M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
  [
    "path",
    {
      d: "M9 12l2 2 4-4",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);
const IconSupport = mk([
  [
    "path",
    {
      d: "M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);
const IconSetup = mk([
  [
    "path",
    {
      d: "M12 20h9",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "path",
    {
      d: "M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
]);
const IconImport = mk([
  [
    "path",
    {
      d: "M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
  [
    "polyline",
    {
      points: "7 10 12 15 17 10",
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
    },
  ],
  [
    "line",
    {
      x1: 12,
      y1: 15,
      x2: 12,
      y2: 3,
      stroke: "currentColor",
      "stroke-width": 1.8,
      "stroke-linecap": "round",
    },
  ],
]);
const IconGo = mk([
  ["circle", { cx: 12, cy: 12, r: 10, stroke: "currentColor", "stroke-width": 1.8 }],
  ["path", { d: "M10 8l6 4-6 4V8z", fill: "currentColor", opacity: 0.6 }],
]);

// ── Data ─────────────────────────────────────────────────────────
const features = [
  {
    title: "Point of Sale",
    desc:
      "Fast checkout for cash, M-Pesa, and card. Built for high-volume retail environments.",
    icon: IconPOS,
  },
  {
    title: "Inventory",
    desc:
      "Real-time stock across all locations. Reorder alerts so you never run out of top sellers.",
    icon: IconInventory,
  },
  {
    title: "Human Resources",
    desc:
      "Staff management, shifts, attendance, payroll, and leave — all in one HR module.",
    icon: IconHRM,
  },
  {
    title: "Reports & Analytics",
    desc:
      "Daily, weekly, and custom reports. Know your best products, busiest hours, and margins.",
    icon: IconReports,
  },
  {
    title: "Multi-Branch",
    desc:
      "Manage multiple locations from one login. Transfer stock, compare branches, set permissions.",
    icon: IconBranches,
  },
  {
    title: "Purchases",
    desc:
      "Create purchase orders, receive deliveries, and manage supplier relationships easily.",
    icon: IconPurchases,
  },
];

const whyPoints = [
  {
    title: "Works on slow connections",
    desc:
      "Optimised for 3G networks. Every screen loads fast — even in areas with patchy internet.",
    icon: IconSpeed,
  },
  {
    title: "Offline-capable POS",
    desc:
      "Keep serving customers when the internet drops. Transactions sync automatically when you reconnect.",
    icon: IconOffline,
  },
  {
    title: "Role-based security",
    desc:
      "Granular permissions mean staff only see what they need to. Full audit logs for accountability.",
    icon: IconSecure,
  },
  {
    title: "Local support team",
    desc:
      "Help via WhatsApp or phone, in your timezone. Real people — not ticket queues.",
    icon: IconSupport,
  },
];

const steps = [
  {
    title: "Create your account",
    desc: "Sign up in under 2 minutes. No credit card, no contracts. Just an email.",
    icon: IconSetup,
  },
  {
    title: "Import your data",
    desc:
      "Bring in products, customers, and staff via CSV — or start fresh from scratch.",
    icon: IconImport,
  },
  {
    title: "Start selling",
    desc: "Open the POS, process your first sale, and watch your dashboard come alive.",
    icon: IconGo,
  },
];

// ── Plans (fetched from API) ──────────────────────────────
const rawPlans = ref([]);
const plansLoading = ref(true);

const periodLabel = (p) => {
  if (p.billing_period === "monthly") return "month";
  if (p.billing_period === "quarterly") return "quarter";
  if (p.billing_period === "annually") return "year";
  return p.billing_period;
};

const savingsBadge = (p) => {
  if (p.slug === "quarterly") return "Save KES 100";
  if (p.slug === "annually") return "Save KES 1,000";
  return "";
};

const plans = computed(() =>
  rawPlans.value.map((p) => ({
    ...p,
    popular: p.slug === "quarterly",
    periodLabel: periodLabel(p),
    savings: savingsBadge(p),
    cta: "Get started",
  }))
);

const testimonials = [
  {
    body:
      "Bizaa replaced three tools we were juggling. Our cashiers love it and I finally get clean daily reports without chasing anyone.",
    name: "Amina Wanjiku",
    role: "Owner, Wanjiku Supermarket, Nairobi",
    initials: "AW",
    color: "#229de7",
  },
  {
    body:
      "Opening our second branch used to be a nightmare. With Bizaa, we had it set up in one afternoon — stock transfer and all.",
    name: "Kevin Ochieng",
    role: "Director, Ochieng Hardware, Kisumu",
    initials: "KO",
    color: "#16a34a",
  },
  {
    body:
      "The HRM module saves us hours every payroll. Attendance, overtime, leaves — it's all there, automatic.",
    name: "Mary Njeri",
    role: "HR Manager, Njeri Foods Ltd, Mombasa",
    initials: "MN",
    color: "#dc2626",
  },
];

const footerLinks = [
  {
    heading: "Product",
    links: [
      { label: "Features", href: "#features" },
      { label: "Pricing", href: "#pricing" },
      { label: "How it works", href: "#how" },
      { label: "Changelog", href: "#" },
    ],
  },
  {
    heading: "Company",
    links: [
      { label: "About", href: "#" },
      { label: "Blog", href: "#" },
      { label: "Careers", href: "#" },
      { label: "Contact", href: "#contact" },
    ],
  },
  {
    heading: "Support",
    links: [
      { label: "Help centre", href: "#" },
      { label: "WhatsApp us", href: "#" },
      { label: "API docs", href: "#" },
      { label: "System status", href: "#" },
    ],
  },
];
</script>

<style scoped>
/* ── Reset & base ────────────────────────────────────── */
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
.landing {
  font-family: "Inter", system-ui, -apple-system, sans-serif;
  color: #111827;
  background: #fff;
  overflow-x: hidden;
  line-height: 1.6;
}
a {
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}
.container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ── Buttons ─────────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  border: 1.5px solid transparent;
  transition: all 0.15s;
  cursor: pointer;
}
.btn--lg {
  padding: 12px 24px;
  font-size: 15px;
  border-radius: 9px;
}
.btn--md {
  padding: 10px 20px;
}
.btn--sm {
  padding: 7px 14px;
  font-size: 13px;
}
.btn--primary {
  background: #229de7;
  color: #fff;
  border-color: #229de7;
}
.btn--primary:hover {
  background: #1682c4;
  border-color: #1682c4;
}
.btn--outline {
  border-color: #d1d5db;
  color: #374151;
  background: #fff;
}
.btn--outline:hover {
  border-color: #9ca3af;
  color: #111827;
}
.btn--ghost {
  color: #6b7280;
  border-color: transparent;
}
.btn--ghost:hover {
  color: #111827;
  background: #f9fafb;
}
.btn--text {
  color: #374151;
  border: none;
  padding-left: 0;
  padding-right: 0;
}
.btn--text:hover {
  color: #111827;
}
.btn--white {
  background: #fff;
  color: #111827;
  border-color: #fff;
}
.btn--white:hover {
  background: #f3f4f6;
}
.btn--outline-white {
  border-color: rgba(255, 255, 255, 0.4);
  color: #fff;
}
.btn--outline-white:hover {
  border-color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.08);
}

/* ── Section ─────────────────────────────────────────── */
.section {
  padding: 88px 0;
}
.section--tinted {
  background: #f9fafb;
}
.section-header {
  text-align: center;
  max-width: 560px;
  margin: 0 auto 56px;
}
.section-header h2 {
  font-size: clamp(26px, 4vw, 40px);
  font-weight: 800;
  letter-spacing: -0.6px;
  margin: 8px 0 14px;
  color: #111827;
  line-height: 1.15;
}
.section-header .sub {
  font-size: 16px;
  color: #6b7280;
}
.label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: #229de7;
}

/* ── NAVBAR ──────────────────────────────────────────── */
.navbar {
  position: fixed;
  inset: 0 0 auto;
  z-index: 100;
  transition: background 0.25s, border-color 0.25s;
  border-bottom: 1px solid transparent;
}
.navbar--scrolled {
  background: rgba(255, 255, 255, 0.97);
  backdrop-filter: blur(10px);
  border-color: #e5e7eb;
}
.navbar__inner {
  height: 64px;
  display: flex;
  align-items: center;
  gap: 24px;
}
.navbar__logo {
  display: flex;
  align-items: center;
  gap: 9px;
  font-weight: 800;
  font-size: 17px;
  color: #111827;
}
.navbar__logo-img {
  border-radius: 7px;
  object-fit: contain;
  display: block;
}
.navbar__links {
  display: flex;
  gap: 2px;
  flex: 1;
}
.navbar__links a {
  padding: 7px 13px;
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  border-radius: 7px;
  transition: all 0.12s;
}
.navbar__links a:hover {
  color: #111827;
  background: #f3f4f6;
}
.navbar__actions {
  display: flex;
  gap: 8px;
  align-items: center;
}
.navbar__burger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  margin-left: auto;
}
.navbar__burger span {
  display: block;
  width: 20px;
  height: 2px;
  background: #374151;
  border-radius: 2px;
  transition: all 0.2s;
}
.navbar__burger span.open:nth-child(1) {
  transform: rotate(45deg) translate(5px, 5px);
}
.navbar__burger span.open:nth-child(2) {
  opacity: 0;
}
.navbar__burger span.open:nth-child(3) {
  transform: rotate(-45deg) translate(5px, -5px);
}

.navbar__mobile {
  display: none;
  flex-direction: column;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  padding: 12px 24px 20px;
  gap: 2px;
}
.navbar__mobile.active {
  display: flex;
}
.navbar__mobile a {
  padding: 10px 0;
  font-size: 15px;
  color: #374151;
  font-weight: 500;
  border-bottom: 1px solid #f3f4f6;
}
.navbar__mobile a:last-of-type {
  border-bottom: none;
}
.mobile-btns {
  display: flex;
  gap: 10px;
  padding-top: 14px;
}
.mobile-btns .btn {
  flex: 1;
  justify-content: center;
}

/* ── HERO ────────────────────────────────────────────── */
.hero {
  padding: 120px 0 80px;
  background: #fff;
}
.hero__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
}
.hero__pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  border: 1px solid #e5e7eb;
  border-radius: 100px;
  padding: 5px 12px 5px 8px;
  margin-bottom: 24px;
  transition: border-color 0.15s;
}
.hero__pill:hover {
  border-color: #9ca3af;
}
.pill-dot {
  width: 7px;
  height: 7px;
  background: #16a34a;
  border-radius: 50%;
}
.hero__text h1 {
  font-size: clamp(32px, 4.5vw, 54px);
  font-weight: 900;
  letter-spacing: -1.5px;
  line-height: 1.1;
  color: #111827;
  margin-bottom: 20px;
}
.hero__text h1 em {
  font-style: normal;
  color: #229de7;
}
.hero__text p {
  font-size: 17px;
  color: #6b7280;
  line-height: 1.7;
  margin-bottom: 32px;
  max-width: 440px;
}
.hero__ctas {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 36px;
}
.hero__meta {
  display: flex;
  align-items: center;
  gap: 12px;
}
.hero__avatars {
  display: flex;
}
.ha {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--c, #229de7);
  border: 2px solid #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #fff;
  margin-left: -7px;
}
.ha:first-child {
  margin-left: 0;
}
.hero__meta span {
  font-size: 13px;
  color: #6b7280;
}
.hero__meta strong {
  color: #111827;
}

/* Dashboard frame */
.dash-frame {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 20px 48px -12px rgba(0, 0, 0, 0.1);
}
.dash-titlebar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}
.dash-dots {
  display: flex;
  gap: 5px;
}
.dd {
  width: 9px;
  height: 9px;
  border-radius: 50%;
}
.dd--r {
  background: #ef4444;
}
.dd--y {
  background: #f59e0b;
}
.dd--g {
  background: #22c55e;
}
.dash-url {
  font-size: 11px;
  color: #9ca3af;
  font-family: monospace;
}
.dash-svg {
  width: 100%;
  display: block;
}

/* ── LOGO BAR ────────────────────────────────────────── */
.logobar {
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
  padding: 20px 0;
  background: #f9fafb;
}
.logobar__inner {
  display: flex;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
}
.logobar__inner > span {
  font-size: 13px;
  color: #9ca3af;
  white-space: nowrap;
}
.logobar__cities {
  display: flex;
  gap: 0;
  flex-wrap: wrap;
}
.logobar__cities span {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  padding: 0 16px;
  border-right: 1px solid #e5e7eb;
}
.logobar__cities span:last-child {
  border-right: none;
}

/* ── FEATURES ────────────────────────────────────────── */
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: #e5e7eb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}
.feat-card {
  background: #fff;
  padding: 28px 24px;
  transition: background 0.15s;
}
.feat-card:hover {
  background: #fafafa;
}
.feat-icon {
  width: 40px;
  height: 40px;
  border-radius: 9px;
  background: #eaf5fd;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #229de7;
  margin-bottom: 14px;
}
.feat-card h3 {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
}
.feat-card p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

/* ── WHY BIZAA ───────────────────────────────────────── */
.why__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 72px;
  align-items: center;
}
.why__copy .label {
  display: block;
  margin-bottom: 12px;
}
.why__copy h2 {
  font-size: clamp(24px, 3.5vw, 36px);
  font-weight: 800;
  letter-spacing: -0.5px;
  margin-bottom: 32px;
  line-height: 1.2;
  color: #111827;
}
.why-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.why-item {
  display: flex;
  gap: 14px;
}
.why-item__icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  border-radius: 9px;
  background: #eaf5fd;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #229de7;
}
.why-item h4 {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 3px;
}
.why-item p {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.6;
}
.why__visual svg {
  width: 100%;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 12px 32px rgba(0, 0, 0, 0.08);
}

/* ── HOW IT WORKS ────────────────────────────────────── */
.steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}
.step {
  text-align: center;
  padding: 0 16px;
}
.step__num {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #229de7;
  margin-bottom: 16px;
  font-family: monospace;
}
.step__icon {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  border: 1.5px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 18px;
  color: #374151;
}
.step h3 {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 10px;
}
.step p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.65;
}

/* ── PRICING ─────────────────────────────────────────── */
.billing-toggle {
  display: inline-flex;
  gap: 4px;
  background: #f3f4f6;
  border-radius: 8px;
  padding: 4px;
  margin-top: 20px;
}
.billing-toggle button {
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background: transparent;
  color: #6b7280;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  gap: 6px;
}
.billing-toggle button.active {
  background: #fff;
  color: #111827;
  font-weight: 600;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.save-chip {
  background: #dcfce7;
  color: #15803d;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 4px;
}
.plans {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  max-width: 1200px;
  margin: 0 auto;
}
.plan {
  background: #fff;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px 20px;
  position: relative;
}
.plan--featured {
  border-color: #229de7;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.06);
}
.plan__badge {
  position: absolute;
  top: -12px;
  left: 24px;
  background: #229de7;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 4px;
  letter-spacing: 0.3px;
}
.plan__name {
  font-size: 16px;
  font-weight: 800;
  color: #111827;
  margin-bottom: 4px;
}
.plan__tagline {
  font-size: 13px;
  color: #9ca3af;
  margin-bottom: 20px;
}
.plan__price {
  display: flex;
  align-items: baseline;
  gap: 3px;
  margin-bottom: 20px;
}
.plan__price .currency {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}
.plan__price .amount {
  font-size: 34px;
  font-weight: 900;
  color: #111827;
  letter-spacing: -1px;
}
.plan__price .per {
  font-size: 13px;
  color: #9ca3af;
}
.plan .btn {
  width: 100%;
  justify-content: center;
  margin-bottom: 24px;
}
.plan ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 9px;
}
.plan li {
  display: flex;
  align-items: center;
  gap: 9px;
  font-size: 13px;
  color: #374151;
}

/* ── TESTIMONIALS ────────────────────────────────────── */
.testi-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.testi {
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  background: #fff;
}
.testi__stars {
  display: flex;
  gap: 2px;
  margin-bottom: 12px;
}
.testi p {
  font-size: 14px;
  color: #374151;
  line-height: 1.7;
  margin-bottom: 18px;
}
.testi__author {
  display: flex;
  align-items: center;
  gap: 10px;
}
.testi__av {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}
.testi__author strong {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: #111827;
}
.testi__author span {
  font-size: 12px;
  color: #9ca3af;
}

/* ── CTA BAND ────────────────────────────────────────── */
.cta-band {
  background: #111827;
  padding: 72px 0 20px;
}
.cta-band__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 40px;
  flex-wrap: wrap;
  padding-bottom: 40px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.cta-band__inner h2 {
  font-size: clamp(22px, 3vw, 32px);
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.4px;
  margin-bottom: 10px;
}
.cta-band__inner > div > p {
  font-size: 15px;
  color: #9ca3af;
}
.cta-band__btns {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
  flex-wrap: wrap;
}
.cta-band__note {
  text-align: center;
  font-size: 12px;
  color: #4b5563;
  padding: 16px 0 0;
}

/* ── FOOTER ──────────────────────────────────────────── */
.footer {
  background: #0f172a;
  color: #6b7280;
  padding: 64px 0 0;
}
.footer__top {
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr 1fr;
  gap: 48px;
  padding-bottom: 48px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}
.footer__brand p {
  font-size: 13px;
  line-height: 1.7;
  margin: 12px 0 18px;
  max-width: 200px;
}
.footer__brand .navbar__logo {
  margin-bottom: 0;
}
.footer__socials {
  display: flex;
  gap: 10px;
}
.footer__socials a {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  background: rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}
.footer__socials a:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}
.footer__col-head {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #fff;
  margin-bottom: 14px;
}
.footer__col ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.footer__col a {
  font-size: 13px;
  color: #6b7280;
  transition: color 0.12s;
}
.footer__col a:hover {
  color: #d1d5db;
}
.footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 0;
  font-size: 12px;
  flex-wrap: wrap;
  gap: 10px;
}
.footer__legal {
  display: flex;
  gap: 16px;
}
.footer__legal a {
  color: #6b7280;
}
.footer__legal a:hover {
  color: #d1d5db;
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 1024px) {
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .why__inner {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .why__visual {
    order: -1;
    max-width: 480px;
  }
  .plans {
    grid-template-columns: repeat(2, 1fr);
    max-width: 700px;
  }
  .footer__top {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .navbar__links,
  .navbar__actions {
    display: none;
  }
  .navbar__burger {
    display: flex;
  }

  .hero {
    padding: 96px 0 60px;
  }
  .hero__inner {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .hero__visual {
    display: none;
  }

  .section {
    padding: 64px 0;
  }
  .features-grid {
    grid-template-columns: 1fr;
  }
  .steps {
    grid-template-columns: 1fr;
    gap: 28px;
  }
  .testi-grid {
    grid-template-columns: 1fr;
  }
  .cta-band__inner {
    flex-direction: column;
    align-items: flex-start;
  }
  .logobar__inner {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .footer__top {
    grid-template-columns: 1fr 1fr;
    gap: 32px;
  }
}

@media (max-width: 480px) {
  .hero__ctas {
    flex-direction: column;
    align-items: flex-start;
  }
  .footer__top {
    grid-template-columns: 1fr;
  }
  .footer__bottom {
    flex-direction: column;
    text-align: center;
  }
  .footer__legal {
    justify-content: center;
  }
}

/* ── MOBILE APP SECTION ───────────────────────────────── */
.app-section {
  background: #fff;
}
.app__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 72px;
  align-items: center;
}
.app__copy .label {
  display: block;
  margin-bottom: 12px;
}
.app__copy h2 {
  font-size: clamp(24px, 3.5vw, 38px);
  font-weight: 800;
  letter-spacing: -0.5px;
  line-height: 1.2;
  color: #111827;
  margin-bottom: 16px;
}
.app__copy h2 em {
  font-style: normal;
  color: #229de7;
}
.app__copy > p {
  font-size: 15px;
  color: #6b7280;
  line-height: 1.7;
  margin-bottom: 20px;
  max-width: 440px;
}
.app-features {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 28px;
}
.app-features li {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #374151;
}
.app-btns {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.app-store-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 11px 18px;
  border-radius: 10px;
  font-size: 13px;
  transition: all 0.15s;
  border: 1.5px solid #111827;
  color: #111827;
  background: #111827;
  color: #fff;
}
.app-store-btn:hover {
  background: #1f2937;
  border-color: #1f2937;
}
.app-store-btn--android {
}
.store-sub {
  display: block;
  font-size: 10px;
  opacity: 0.75;
  line-height: 1;
}
.store-name {
  display: block;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.3;
}

/* Phone mockup */
.phone-mockup {
  display: flex;
  justify-content: center;
}
.phone-screen {
  width: 240px;
  background: #f9fafb;
  border-radius: 28px;
  overflow: hidden;
  border: 8px solid #111827;
  box-shadow: 0 20px 60px -12px rgba(0, 0, 0, 0.3), 0 4px 12px rgba(0, 0, 0, 0.1);
}
.phone-header {
  height: 28px;
  background: #111827;
  display: flex;
  align-items: center;
  justify-content: center;
}
.phone-status {
  width: 80px;
  height: 12px;
  background: #1f2937;
  border-radius: 6px;
}
.phone-svg {
  width: 100%;
  display: block;
}

/* ── FOOTER CONTACT ──────────────────────────────────── */
.footer__contact {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin: 12px 0 16px;
}
.footer__contact-item {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 12px;
  color: #9ca3af;
  transition: color 0.12s;
}
.footer__contact-item:hover {
  color: #d1d5db;
}
.footer__contact-item svg {
  flex-shrink: 0;
  opacity: 0.7;
}

@media (max-width: 1024px) {
  .app__inner {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  .phone-mockup {
    order: -1;
  }
}
@media (max-width: 768px) {
  .phone-mockup {
    display: none;
  }
  .app__inner {
    grid-template-columns: 1fr;
  }
}

/* ── PRICING — live plans ────────────────────────────── */
.plan__savings {
  display: inline-block;
  background: #dcfce7;
  color: #15803d;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 20px;
  margin-bottom: 12px;
}
.plans__trial-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 28px;
  font-size: 13px;
  color: #6b7280;
  text-align: center;
}
.plans__trial-note strong {
  color: #111827;
}

/* Skeleton loader */
.plan--skeleton {
  pointer-events: none;
}
@keyframes shimmer {
  0%,
  100% {
    opacity: 0.5;
  }
  50% {
    opacity: 1;
  }
}
.skel {
  background: #e5e7eb;
  border-radius: 6px;
  animation: shimmer 1.4s ease-in-out infinite;
}
.skel--name {
  height: 16px;
  width: 60%;
  margin-bottom: 10px;
}
.skel--price {
  height: 36px;
  width: 50%;
  margin-bottom: 16px;
}
.skel--btn {
  height: 38px;
  width: 100%;
  border-radius: 8px;
  margin-bottom: 18px;
}
.skel--line {
  height: 11px;
  width: 80%;
  margin-bottom: 8px;
}
.skel--line:nth-child(odd) {
  width: 65%;
}
</style>
