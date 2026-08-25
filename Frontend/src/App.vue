<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();

if (auth.token) {
  auth.refreshUser();
}
</script>
