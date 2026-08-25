<template>
  <header class="pos-topbar">
    <!-- Logo -->
    <div class="flex items-center gap-2 flex-shrink-0">
      <div class="topbar-logo">
        <span class="text-white font-extrabold text-sm">B</span>
      </div>

      <div class="hidden sm:block">
        <div class="text-sm font-extrabold text-white leading-none">BRIPOS</div>
        <div class="text-[10px] font-bold text-[#5BB8F5] uppercase tracking-widest">
          Point of Sale
        </div>
      </div>
    </div>

    <!-- Right side -->
    <div class="topbar-right">
      <!-- Branch -->
      <div class="topbar-pill">
        <svg
          class="w-3 h-3"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"
          />
        </svg>

        <select
          :value="modelBranch"
          @change="$emit('update:modelBranch', $event.target.value)"
          class="topbar-select"
        >
          <option v-for="b in branches" :key="b.id" :value="b.id">
            {{ b.name }}
          </option>
        </select>
      </div>

      <!-- Cashier -->
      <div class="topbar-pill hidden md:flex">
        <svg
          class="w-3 h-3"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
          />
        </svg>
        <span>{{ cashierName }}</span>
      </div>

      <!-- Time -->
      <div class="topbar-pill hidden sm:flex font-mono">
        <svg
          class="w-3 h-3"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <circle cx="12" cy="12" r="10" />
          <path stroke-linecap="round" d="M12 6v6l4 2" />
        </svg>
        {{ currentTime }}
      </div>

      <!-- Held orders -->
      <button
        v-if="heldCount > 0"
        @click="$emit('open-held')"
        class="topbar-pill bg-amber-500 border-amber-400 text-white"
      >
        <svg
          class="w-3 h-3"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6M4 6h16" />
        </svg>

        <span class="hidden sm:inline">{{ heldCount }} on hold</span>
        <span class="sm:hidden">{{ heldCount }}</span>
      </button>

      <!-- Menu -->
      <div class="relative cursor-pointer">
        <button @click="toggleMenu" class="topbar-pill">
          <svg
            class="w-3 h-3"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
          <span class="hidden sm:inline">Menu</span>
        </button>

        <div v-if="menuOpen" class="dropdown-menu">
          <button @click="go('dashboard')" class="dropdown-item">Dashboard</button>
          <button @click="go('sales')" class="dropdown-item">Sales</button>
          <button @click="go('payments')" class="dropdown-item">Payments</button>
          <button @click="go('customers')" class="dropdown-item">Customers</button>
        </div>
      </div>

      <!-- Close -->
      <button
        @click="$emit('close-pos')"
        class="topbar-pill bg-red-600 border-red-500 text-white"
      >
        <svg
          class="w-3 h-3"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
          />
        </svg>
        <span class="hidden sm:inline">Close</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";

defineProps({
  branches: Array,
  modelBranch: [String, Number],
  cashierName: String,
  currentTime: String,
  heldCount: { type: Number, default: 0 },
});

const emit = defineEmits(["update:modelBranch", "open-held", "close-pos", "navigate"]);

const menuOpen = ref(false);

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value;
};

const go = (page) => {
  menuOpen.value = false;
  emit("navigate", page);
};
</script>

<style scoped>
/* Layout */
.pos-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 10px;
  height: 52px;
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
  border-bottom: 1px solid #1e40af33;
  gap: 8px;
  flex-shrink: 0;
}

/* Right section responsive wrapper */
.topbar-right {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

/* Logo */
.topbar-logo {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(135deg, #229de7, #5bb8f5);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Pill */
.topbar-pill {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 9px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
  color: #cbd5e1;
  white-space: nowrap;
}

/* Select */
.topbar-select {
  background: transparent;
  border: none;
  outline: none;
  color: #cbd5e1;
  font-size: 11px;
  font-weight: 600;
  max-width: 90px;
}

/* Dropdown */
.dropdown-menu {
  position: absolute;
  right: 0;
  top: 110%;
  background: white;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  min-width: 160px;
  overflow: hidden;
  z-index: 50;
}

.dropdown-item {
  width: 100%;
  padding: 10px 14px;
  font-size: 12px;
  font-weight: 600;
  text-align: left;
  background: white;
  border: none;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f0f9ff;
  color: #229de7;
}

/* Mobile tuning */
@media (max-width: 640px) {
  .pos-topbar {
    height: auto;
    padding: 8px;
  }

  .topbar-right {
    width: 100%;
    justify-content: space-between;
  }

  .topbar-pill {
    font-size: 10px;
    padding: 4px 8px;
  }
}
</style>
