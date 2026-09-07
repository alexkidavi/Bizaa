<template>
  <div class="min-h-screen flex w-full font-sans bg-[#F4F7FA] overflow-hidden">
    <!-- LEFT PANEL (REIMAGINED POS EXPERIENCE) -->
    <div
      class="hidden lg:flex flex-col justify-between flex-1 p-16 relative text-white overflow-hidden"
      style="background: linear-gradient(135deg, #229de7 0%, #1565c0 100%)"
    >
      <!-- Soft retail glow background -->
      <div class="absolute inset-0">
        <div
          class="absolute top-[-150px] left-[-100px] w-[500px] h-[500px] bg-white/10 blur-[120px] rounded-full"
        ></div>
        <div
          class="absolute bottom-[-150px] right-[-100px] w-[400px] h-[400px] bg-black/10 blur-[120px] rounded-full"
        ></div>
      </div>

      <!-- Top identity -->
      <div class="relative z-10">
        <h1 class="text-5xl font-black leading-tight mb-6">
          Every Sale.<br />
          <span class="text-blue-200">Tracked Instantly.</span>
        </h1>

        <p class="text-lg text-blue-50/90 max-w-md">
          A modern point-of-sale system built for speed, retail flow, and real-time
          transactions.
        </p>
      </div>

      <!-- LIVE POS FEEL (not corporate stats) -->
      <div class="relative z-10 space-y-4">
        <div class="p-5 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
          <div class="text-xs uppercase opacity-70 mb-1">Live Checkout</div>
          <div class="font-bold">Processing transactions in real time</div>
        </div>

        <div class="p-5 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
          <div class="text-xs uppercase opacity-70 mb-1">Inventory Sync</div>
          <div class="font-bold">Stock updates instantly per sale</div>
        </div>
      </div>

      <!-- Footer vibe -->
      <div class="relative z-10 text-xs opacity-60 tracking-widest uppercase">
        Checkout • Scan • Sell • Repeat
      </div>
    </div>

    <!-- RIGHT PANEL (CASHIER LOGIN TERMINAL STYLE) -->
    <div class="flex flex-1 items-center justify-center px-6 bg-white">
      <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-10">
          <img src="/bizaa-logo.png" alt="Bizaa POS" class="h-11 w-auto" />
        </div>

        <!-- Login header -->
        <div class="text-center mb-8">
          <h2 class="text-2xl font-bold text-slate-900">Sign In</h2>
          <p class="text-sm text-slate-500 mt-1">Access your store dashboard</p>
        </div>

        <!-- Error Message -->
        <div
          v-if="errorMessage"
          class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl"
        >
          <p class="text-sm text-red-600 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ errorMessage }}
          </p>
        </div>

        <!-- Offline: cached session can still run the POS -->
        <button
          v-if="canResumeOffline"
          type="button"
          @click="resumeOffline"
          class="mb-4 w-full p-3 bg-amber-50 border border-amber-300 rounded-xl text-sm font-semibold text-amber-800 hover:bg-amber-100"
        >
          You're offline — continue to the POS with your saved session
        </button>

        <!-- Success Message -->
        <div
          v-if="successMessage"
          class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl"
        >
          <p class="text-sm text-green-600 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ successMessage }}
          </p>
        </div>

        <!-- FORM -->
        <form @submit.prevent="login" class="space-y-5">
          <div>
            <label
              class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2"
            >
              Staff Email
            </label>
            <input
              v-model="email"
              type="email"
              placeholder="staff@yourstore.com"
              class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-[#229DE7]/10 focus:border-[#229DE7] outline-none transition font-medium"
            />
          </div>

          <div>
            <label
              class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2"
            >
              Password
            </label>
            <div class="relative">
              <input
                :type="passwordVisible ? 'text' : 'password'"
                v-model="password"
                placeholder="Enter your password"
                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-[#229DE7]/10 focus:border-[#229DE7] outline-none transition font-medium"
              />

              <button
                type="button"
                @click="passwordVisible = !passwordVisible"
                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#229DE7] transition"
              >
                <EyeIcon v-if="!passwordVisible" :size="18" />
                <EyeOffIcon v-else :size="18" />
              </button>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="cursor-pointer w-full py-4 bg-[#229DE7] text-white font-bold rounded-xl hover:bg-[#1b8bc9] transition shadow-md shadow-[#229DE7]/20 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ loading ? "Signing in..." : "Enter POS System" }}
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200"></div>
          </div>
          <div class="relative flex justify-center text-xs">
            <span class="px-3 bg-white text-slate-400">or</span>
          </div>
        </div>

        <!-- Try Demo — public, one-tap login to a live demo account so a
             prospect can explore the product without registering first. -->
        <button
          @click="demoLogin"
          :disabled="loading"
          class="cursor-pointer w-full py-3.5 text-sm font-semibold rounded-xl border border-[#229DE7]/30 text-[#229DE7] bg-[#229DE7]/5 hover:bg-[#229DE7]/10 hover:border-[#229DE7]/50 transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
            />
          </svg>
          Try Demo
        </button>

        <!-- Create Account Link -->
        <div class="mt-6 text-center">
          <p class="text-sm text-slate-500">
            Don't have an account?
            <button
              @click="goToRegister"
              class="text-[#229DE7] font-semibold hover:text-[#1b8bc9] hover:underline transition ml-1"
            >
              Create Account
            </button>
          </p>
        </div>

        <!-- Help text -->
        <p class="text-center text-xs text-slate-400 mt-6">
          Secure login • Your data is protected
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import LoginService from "@/services/loginService";
import { EyeIcon, EyeOffIcon } from "lucide-vue-next";

const email = ref("");
const password = ref("");
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const passwordVisible = ref(false);

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

// Offline + a still-cached session that can run the POS: the login form is
// useless here (no server to hit), so offer a direct way through instead of
// leaving the cashier stuck on "Network error". `verify-token` on the way
// back online re-checks it for real. `navigator.onLine` lies (it's true on a
// dead Wi-Fi), so a network error from the login attempt itself also counts.
const sawNetworkError = ref(false);
const canResumeOffline = computed(
  () =>
    (!navigator.onLine || sawNetworkError.value) &&
    !!auth.token &&
    auth.hasPermission("sales.create"),
);
const resumeOffline = () => router.push("/sales/pos");

// Landing page's hero "Try Demo" link lands here with ?demo=1 instead of
// duplicating the demo-login flow on the landing page itself.
onMounted(() => {
  if (route.query.demo === "1" && navigator.onLine) {
    demoLogin();
    return;
  }
  if (!navigator.onLine && auth.token && auth.hasPermission("sales.create")) {
    resumeOffline();
  }
});

const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value;
};

const demoLogin = async () => {
  email.value = "demo@gmail.com";
  password.value = "Demo@2026";
  await login();
};

const login = async () => {
  errorMessage.value = "";
  successMessage.value = "";

  if (!email.value || !password.value) {
    errorMessage.value = "Please fill in both email and password.";
    return;
  }

  loading.value = true;

  try {
    const response = await LoginService.post({
      email: email.value,
      password: password.value,
    });

    const { access_token, user } = response.data;

    auth.setToken(access_token);
    auth.setUser(user);
    auth.refreshUser();
    successMessage.value = "Login successful! Redirecting...";

    setTimeout(() => {
      router.push("/dashboard");
    }, 1000);
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = "Invalid email or password.";
    } else if (error.response?.status === 403) {
      errorMessage.value = error.response?.data?.message || "This account has been deactivated.";
    } else if (error.response?.status === 429) {
      errorMessage.value = "Too many attempts. Try later.";
    } else if (error.response?.status === 500) {
      errorMessage.value = "Server error. Please try again.";
    } else {
      errorMessage.value = "Network error. Check your connection.";
      sawNetworkError.value = true;
    }
  } finally {
    loading.value = false;
  }
};

const goToRegister = () => {
  router.push("/register");
};
</script>
