<template>
  <header
    class="bg-white shadow-sm px-4 md:px-6 py-3 flex items-center justify-between border-b border-[#D9EBF7] z-20"
  >
    <div class="flex items-center gap-3">
      <!-- Hamburger — mobile only -->
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-[#EAF5FD] transition"
      >
        <Bars3Icon class="w-5 h-5 text-slate-600" />
      </button>

      <!-- Page title -->
      <div class="text-sm md:text-base font-extrabold text-slate-800 tracking-tight">
        {{ currentPageTitle }}
      </div>
    </div>

    <!-- RIGHT: Bell + User -->
    <div class="flex items-center gap-2">
      <!-- Notification bell -->
      <button
        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-[#EAF5FD] transition relative"
      >
        <BellIcon class="w-5 h-5 text-slate-500" />
        <span
          class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 border-2 border-white"
        ></span>
      </button>

      <!-- USER MENU -->
      <div class="relative" ref="userMenuRef">
        <button
          @click.stop="dropdownOpen = !dropdownOpen"
          class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-[#EAF5FD] transition focus:outline-none"
        >
          <img
            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(
              auth.user?.name || 'U'
            )}&background=229DE7&color=fff&bold=true`"
            class="w-8 h-8 rounded-full object-cover border-2 border-[#B3DEFA]"
            alt="Avatar"
          />
          <span
            class="hidden md:block text-sm font-bold text-slate-700 max-w-[120px] truncate"
          >
            {{ auth.user?.name || "User" }}
          </span>
          <ChevronDownIcon
            :class="[
              'w-3.5 h-3.5 text-[#229DE7] transform transition-transform',
              dropdownOpen ? 'rotate-180' : '',
            ]"
          />
        </button>

        <!-- Dropdown -->
        <transition name="dropdown">
          <div
            v-if="dropdownOpen"
            class="absolute right-0 mt-2 w-52 bg-white border border-[#D9EBF7] rounded-2xl shadow-lg z-50 overflow-hidden"
          >
            <!-- User info -->
            <div class="px-4 py-3 border-b border-[#EEF5FB]">
              <p class="text-xs font-extrabold text-slate-800 truncate">
                {{ auth.user?.name || "User" }}
              </p>
              <p class="text-[10px] text-slate-400 font-semibold truncate mt-0.5">
                {{ auth.user?.email || "" }}
              </p>
            </div>

            <button
              @click="goToProfile"
              class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm hover:bg-[#EAF5FD] transition text-slate-700"
            >
              <UserIcon class="w-4 h-4 text-[#229DE7] flex-shrink-0" /> Profile
            </button>
            <button
              @click="goToPasswordUpdate"
              class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm hover:bg-[#EAF5FD] transition text-slate-700"
            >
              <LockClosedIcon class="w-4 h-4 text-[#229DE7] flex-shrink-0" /> Update
              Password
            </button>

            <div class="border-t border-[#EEF5FB]"></div>

            <button
              @click="logout"
              class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm hover:bg-red-50 transition text-red-600"
            >
              <ArrowRightOnRectangleIcon class="w-4 h-4 flex-shrink-0" /> Logout
            </button>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import {
  Bars3Icon,
  BellIcon,
  ChevronDownIcon,
  UserIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon,
} from "@heroicons/vue/24/outline";

defineEmits(["toggle-sidebar"]);

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const dropdownOpen = ref(false);
const userMenuRef = ref(null);

const currentPageTitle = computed(() => route.meta?.title ?? route.name ?? "Dashboard");

const handleOutsideClick = (e) => {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    dropdownOpen.value = false;
  }
};

onMounted(() => document.addEventListener("click", handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener("click", handleOutsideClick));

const logout = () => {
  auth.clearToken();
  router.push("/login");
};
const goToProfile = () => {
  dropdownOpen.value = false;
  router.push("/profile");
};
const goToPasswordUpdate = () => {
  dropdownOpen.value = false;
  router.push("/profile/password");
};
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}
</style>
