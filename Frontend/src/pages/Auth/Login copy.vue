<template>
  <div class="min-h-screen flex flex-col lg:flex-row bg-[#F4F7FA]">
    <!-- LEFT PANEL -->
    <div
      class="hidden lg:flex flex-col justify-between w-1/2 xl:w-3/5 p-10 xl:p-16 text-white"
      style="background: linear-gradient(135deg, #229de7 0%, #1565c0 100%)"
    >
      <div>
        <h1 class="text-3xl xl:text-5xl font-black leading-tight mb-4">Welcome Back</h1>
        <p class="text-blue-100 text-sm xl:text-lg max-w-md">
          Manage your system securely and efficiently from one powerful dashboard.
        </p>
      </div>

      <div class="text-xs opacity-60 mt-10">
        &copy; {{ new Date().getFullYear() }} CloudSacco
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="flex flex-1 items-center justify-center px-4 sm:px-6 lg:px-12 py-10">
      <div class="w-full max-w-md">
        <!-- LOGO -->
        <div class="flex justify-center mb-6 sm:mb-8">
          <img src="/logo.png" class="w-24 sm:w-28" />
        </div>

        <!-- TITLE -->
        <div class="text-center mb-6 sm:mb-8">
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Sign In</h2>
          <p class="text-slate-500 text-sm sm:text-base mt-2">
            Enter your details to continue
          </p>
        </div>

        <!-- ALERTS -->
        <div
          v-if="errorMessage"
          class="p-3 sm:p-4 bg-red-50 text-red-600 rounded-xl mb-4 text-sm"
        >
          ⚠️ {{ errorMessage }}
        </div>

        <div
          v-if="successMessage"
          class="p-3 sm:p-4 bg-green-50 text-green-600 rounded-xl mb-4 text-sm"
        >
          ✅ {{ successMessage }}
        </div>

        <!-- FORM -->
        <form @submit.prevent="login" class="space-y-4 sm:space-y-5">
          <!-- EMAIL -->
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">
              Email
            </label>
            <input
              v-model="email"
              type="email"
              placeholder="name@example.com"
              class="w-full px-4 py-3 text-sm sm:text-base bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-[#229DE7]/10 focus:border-[#229DE7] outline-none"
            />
          </div>

          <!-- PASSWORD -->
          <div class="relative">
            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">
              Password
            </label>
            <input
              :type="passwordVisible ? 'text' : 'password'"
              v-model="password"
              placeholder="Enter password"
              class="w-full px-4 py-3 text-sm sm:text-base bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-[#229DE7]/10 focus:border-[#229DE7] outline-none pr-10"
            />

            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute right-3 top-9 text-slate-400"
            >
              <span v-if="passwordVisible">🙈</span>
              <span v-else>👁️</span>
            </button>
          </div>

          <!-- LOGIN BUTTON -->
          <button
            :disabled="loading"
            type="submit"
            class="w-full py-3 text-sm sm:text-base bg-[#229DE7] text-white rounded-xl font-bold shadow hover:bg-[#1b8bc9] transition-all"
          >
            {{ loading ? "Logging in..." : "Login" }}
          </button>
        </form>

        <!-- DEMO BUTTON -->
        <button
          @click="demoLogin"
          class="w-full mt-4 py-3 text-sm sm:text-base bg-gray-500 text-white rounded-xl font-medium hover:bg-gray-600"
        >
          Demo Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import LoginService from "@/services/loginService";

const email = ref("");
const password = ref("");
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const passwordVisible = ref(false);

const router = useRouter();
const auth = useAuthStore();

const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value;
};

const demoLogin = () => {
  email.value = "admin@example.com";
  password.value = "Demo@2025";
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

    successMessage.value = "Login successful! Redirecting...";

    setTimeout(() => {
      router.push("/dashboard");
    }, 1000);
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = "Invalid email or password.";
    } else if (error.response?.status === 429) {
      errorMessage.value = "Too many attempts. Try later.";
    } else if (error.response?.status === 500) {
      errorMessage.value = "Server error.";
    } else {
      errorMessage.value = "Network error.";
    }
  } finally {
    loading.value = false;
  }
};
</script>
