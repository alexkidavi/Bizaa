<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl border border-[#D9EBF7] w-full max-w-xl overflow-hidden max-h-[85vh] flex flex-col"
    >
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] to-[#5BB8F5] flex-shrink-0"></div>

      <div class="p-6 pb-4 flex-shrink-0">
        <div class="flex items-center justify-between mb-1">
          <h2 class="text-lg font-extrabold text-slate-900">KRA Item Classification Codes</h2>
          <button
            @click="$emit('close')"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <p class="text-xs text-slate-400 mb-4">
          Search by code or description, then click a row to use it. This is the official
          KRA eTIMS classification table ({{ totalHint }}).
        </p>

        <div class="relative">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
          <input
            v-model="search"
            ref="searchInput"
            type="text"
            placeholder="e.g. milk, 99010000, medical..."
            class="w-full pl-10 pr-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] transition"
          />
        </div>
      </div>

      <div class="px-6 pb-6 overflow-y-auto flex-1">
        <div v-if="loading" class="flex items-center justify-center py-10 text-slate-400 text-sm gap-2">
          <div class="w-4 h-4 border-2 border-[#229DE7] border-t-transparent rounded-full animate-spin"></div>
          Searching...
        </div>

        <div v-else-if="search.trim().length < 2" class="text-center py-10 text-sm text-slate-400">
          Type at least 2 characters to search.
        </div>

        <div v-else-if="results.length === 0" class="text-center py-10 text-sm text-slate-400">
          No matching codes found.
        </div>

        <div v-else class="space-y-1.5">
          <button
            v-for="r in results"
            :key="r.code"
            type="button"
            @click="$emit('select', r.code); $emit('close')"
            class="w-full text-left flex items-start gap-3 p-3 rounded-xl border border-[#EEF5FB] hover:border-[#B3DEFA] hover:bg-[#F5FAFF] transition"
          >
            <span class="font-mono text-xs font-bold text-[#1682C4] bg-[#EAF5FD] border border-[#B3DEFA] rounded-lg px-2 py-1 flex-shrink-0 mt-0.5">
              {{ r.code }}
            </span>
            <span class="text-sm text-slate-700 leading-snug">{{ r.name }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from "vue";
import DigitaxService from "@/services/DigitaxService";

defineEmits(["close", "select"]);

const search = ref("");
const results = ref([]);
const loading = ref(false);
const searchInput = ref(null);
const totalHint = "1000+ codes";

let debounceTimer = null;

watch(search, (val) => {
  clearTimeout(debounceTimer);

  if (val.trim().length < 2) {
    results.value = [];
    return;
  }

  debounceTimer = setTimeout(async () => {
    loading.value = true;
    try {
      const res = await DigitaxService.searchItemClasses(val.trim());
      results.value = res?.data || [];
    } catch (err) {
      console.error("Failed to search item classes", err);
      results.value = [];
    } finally {
      loading.value = false;
    }
  }, 300);
});

onMounted(() => {
  nextTick(() => searchInput.value?.focus());
});
</script>
