<template>
  <div class="flex h-screen bg-[#F0F6FB] overflow-hidden">
    <Sidebar :mobile-open="sidebarOpen" @close="sidebarOpen = false" />

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
      <Header @toggle-sidebar="sidebarOpen = true" />

      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import Sidebar from "@/components/Navs/Sidebar.vue";
import Header from "@/components/Navs/Header.vue";

const sidebarOpen = ref(false);
</script>
