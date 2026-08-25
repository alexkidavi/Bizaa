<template>
  <header class="pos-topbar">
    <div class="flex items-center gap-3">
      <div class="topbar-logo">
        <span class="text-white font-extrabold text-sm">B</span>
      </div>
      <div>
        <div class="text-sm font-extrabold text-white leading-none">Bizaa</div>
        <div class="text-[10px] font-bold text-[#5BB8F5] uppercase tracking-widest">
          Point of Sale
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3">
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
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>

      <!-- Cashier -->
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
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
          />
        </svg>
        <span>{{ cashierName }}</span>
      </div>

      <!-- Time -->
      <div class="topbar-pill font-mono">
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

      <!-- Held orders badge -->
      <button
        v-if="heldCount > 0"
        @click="$emit('open-held')"
        class="topbar-pill bg-amber-500 border-amber-400 text-white cursor-pointer hover:bg-amber-400 transition"
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
        {{ heldCount }} on hold
      </button>
      <!-- Navigation Dropdown -->
      <div class="relative">
        <button @click="toggleMenu" class="topbar-pill cursor-pointer">
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
          Menu
        </button>

        <!-- Dropdown -->
        <div v-if="menuOpen" class="dropdown-menu">
          <button @click="go('dashboard')" class="dropdown-item">Dashboard</button>
          <button @click="go('sales')" class="dropdown-item">Sales</button>
          <button @click="go('payments')" class="dropdown-item">Payments</button>
          <button @click="go('customers')" class="dropdown-item">Customers</button>
        </div>
      </div>
      <!-- Close POS -->
      <button
        @click="$emit('close-pos')"
        class="topbar-pill bg-red-600 border-red-500 text-white cursor-pointer hover:bg-red-500 transition"
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
        Close POS
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
.pos-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  height: 52px;
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
  border-bottom: 1px solid #1e40af33;
  flex-shrink: 0;
  gap: 12px;
}
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
.topbar-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
  color: #cbd5e1;
  white-space: nowrap;
}
.topbar-select {
  background: transparent;
  border: none;
  outline: none;
  color: #cbd5e1;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}
.topbar-select option {
  background: #1e293b;
  color: white;
}
.dropdown-menu {
  position: absolute;
  right: 0;
  top: 110%;
  background: white;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  min-width: 160px;
  z-index: 50;
}

.dropdown-item {
  width: 100%;
  text-align: left;
  padding: 10px 14px;
  font-size: 12px;
  font-weight: 600;
  color: #1e293b;
  background: white;
  border: none;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f0f9ff;
  color: #229de7;
}
</style>
