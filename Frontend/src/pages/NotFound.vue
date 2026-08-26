<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-[#F4F7FA] px-6 text-center">
    <div class="text-7xl font-black text-[#229DE7] mb-2">404</div>
    <h1 class="text-xl font-extrabold text-slate-900 mb-2">Page not found</h1>
    <p class="text-slate-500 text-sm mb-6 max-w-sm">
      The page you're looking for doesn't exist or may have been moved.
    </p>
    <button
      @click="goHome"
      class="px-5 py-2.5 rounded-xl bg-[#229DE7] text-white text-sm font-bold hover:bg-[#1682C4] transition"
    >
      Back to Dashboard
    </button>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
const goHome = () => router.push("/dashboard");
</script>
