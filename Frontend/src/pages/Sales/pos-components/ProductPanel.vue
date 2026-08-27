<template>
  <section class="pos-left">
    <!-- ── Search bar ── -->
    <div class="pos-search-bar">
      <div class="relative flex-1">
        <!-- Search icon or spinner -->
        <transition name="fade-icon" mode="out-in">
          <svg
            v-if="!loading"
            key="search"
            class="abs-icon text-slate-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
          <svg
            v-else
            key="spinner"
            class="abs-icon text-[#229DE7] animate-spin"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-20"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="3"
            />
            <path
              class="opacity-80"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            />
          </svg>
        </transition>

        <input
          ref="searchInput"
          v-model="searchQuery"
          @input="onSearchInput"
          @keydown.enter="$emit('add-first')"
          type="text"
          placeholder="Search product or scan barcode..."
          class="pos-search-input"
        />

        <button v-if="searchQuery" @click="clearSearch" class="abs-clear">×</button>
      </div>

      <button
        @click="$emit('focus-search')"
        class="pos-icon-btn"
        title="Barcode Scanner (F2)"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3 9V6a3 3 0 013-3h3M3 15v3a3 3 0 003 3h3m6-18h3a3 3 0 013 3v3m0 6v3a3 3 0 01-3 3h-3M8 7v10M12 7v10M16 7v10"
          />
        </svg>
      </button>
    </div>

    <!-- ── Category pills (no "All") ── -->
    <div class="pos-categories" v-if="categories.length > 0">
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="$emit('set-category', cat)"
        class="pos-cat-pill"
        :class="{ active: activeCat === cat.id }"
      >
        {{ cat.name }}
      </button>
    </div>

    <!-- ── IDLE STATE ── -->
    <div v-if="!hasActiveQuery" class="pos-idle-state">
      <div class="idle-icon-wrap">
        <svg
          class="w-12 h-12 text-[#229DE7]"
          fill="none"
          stroke="currentColor"
          stroke-width="1.2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"
          />
        </svg>
      </div>
      <p class="idle-title">Search or Browse Products</p>
      <p class="idle-sub">
        Type a product name, scan a barcode,<br />or select a category above to get
        started.
      </p>
      <div class="idle-hints">
        <div class="idle-hint"><kbd>↵ Enter</kbd><span>Add first result</span></div>
        <div class="idle-hint"><kbd>F2</kbd><span>Focus search</span></div>
      </div>
    </div>

    <!-- ── RESULTS STATE ── -->
    <template v-else>
      <!-- Active filter indicator -->
      <div v-if="activeCat !== null || searchQuery" class="results-bar">
        <div class="flex items-center gap-2 flex-wrap">
          <span v-if="activeCatName" class="filter-chip cat">
            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            {{ activeCatName }}
            <button @click="$emit('set-category', null)" class="chip-x">×</button>
          </span>
          <span v-if="searchQuery" class="filter-chip search">
            "{{ searchQuery }}"
            <button @click="clearSearch" class="chip-x">×</button>
          </span>
        </div>
        <span class="results-count">
          <template v-if="loading">Searching…</template>
          <template v-else
            >{{ totalProducts }} result{{ totalProducts !== 1 ? "s" : "" }}</template
          >
        </span>
      </div>

      <!-- Product grid — max-height so it doesn't stretch full height -->
      <div class="pos-products-grid" ref="gridEl">
        <!-- Skeletons while loading -->
        <template v-if="loading && products.length === 0">
          <div v-for="n in 8" :key="n" class="pos-product-card skeleton"></div>
        </template>

        <!-- Empty state -->
        <div
          v-else-if="!loading && products.length === 0"
          class="col-span-full empty-results"
        >
          <svg
            class="w-10 h-10 mb-2 opacity-30"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"
            />
          </svg>
          <p class="font-bold text-sm text-slate-400">No products found</p>
          <p class="text-xs text-slate-300 mt-1">Try a different search or category</p>
        </div>

        <!-- Cards -->
        <button
          v-for="p in products"
          :key="p.id"
          @click="$emit('add-to-cart', p)"
          class="pos-product-card"
          :class="{ 'out-of-stock': stockOf(p) === 0 }"
          :disabled="stockOf(p) === 0"
        >
          <!-- Loading overlay for stale results -->
          <div v-if="loading" class="card-loading-overlay"></div>
          <div class="product-avatar">{{ p.name?.charAt(0).toUpperCase() }}</div>
          <div class="product-name">{{ p.name }}</div>
          <div class="product-code">{{ p.product_code }}</div>
          <div class="product-price">
            KES {{ Number(p.selling_price).toLocaleString() }}
          </div>
          <div
            class="product-stock"
            :class="{ low: stockOf(p) > 0 && stockOf(p) < 5 }"
          >
            {{ stockOf(p) === 0 ? "Out of stock" : `${stockOf(p)} left` }}
          </div>
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pos-pagination">
        <button
          :disabled="currentPage === 1"
          @click="$emit('page-change', currentPage - 1)"
          class="pos-page-btn"
        >
          ←
        </button>

        <div class="flex items-center gap-1">
          <button
            v-if="currentPage > 2"
            @click="$emit('page-change', 1)"
            class="pos-page-num"
          >
            1
          </button>
          <span v-if="currentPage > 3" class="text-slate-400 text-xs px-1">…</span>

          <button
            v-for="p in surroundingPages"
            :key="p"
            @click="$emit('page-change', p)"
            class="pos-page-num"
            :class="{ active: p === currentPage }"
          >
            {{ p }}
          </button>

          <span v-if="currentPage < totalPages - 2" class="text-slate-400 text-xs px-1"
            >…</span
          >
          <button
            v-if="currentPage < totalPages - 1"
            @click="$emit('page-change', totalPages)"
            class="pos-page-num"
          >
            {{ totalPages }}
          </button>
        </div>

        <button
          :disabled="currentPage >= totalPages"
          @click="$emit('page-change', currentPage + 1)"
          class="pos-page-btn"
        >
          →
        </button>
        <span class="text-[10px] text-slate-400 ml-1"
          >Page {{ currentPage }}/{{ totalPages }}</span
        >
      </div>
    </template>
  </section>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  products: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  categories: { type: Array, default: () => [] },
  activeCat: { type: [Number, String, null], default: null },
  activeCatName: { type: String, default: "" },
  totalProducts: { type: Number, default: 0 },
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
});

const emit = defineEmits([
  "add-to-cart",
  "add-first",
  "set-category",
  "search-change",
  "page-change",
  "focus-search",
]);

const searchQuery = ref("");
const searchInput = ref(null);
const gridEl = ref(null);
let searchTimer = null;

// current_stock arrives as a decimal-cast string (e.g. "0.0000") from the
// backend, not a number — strict `=== 0` checks against it silently never
// match. Normalize once here rather than at every comparison site.
const stockOf = (p) => Number(p.current_stock ?? 0);

const hasActiveQuery = computed(
  () => searchQuery.value.trim().length > 0 || props.activeCat !== null
);

const surroundingPages = computed(() => {
  const pages = [];
  const start = Math.max(1, props.currentPage - 1);
  const end = Math.min(props.totalPages, props.currentPage + 1);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    emit("search-change", searchQuery.value);
  }, 350);
};

const clearSearch = () => {
  searchQuery.value = "";
  emit("search-change", "");
};

// Expose focus method for parent
const focusInput = () => searchInput.value?.focus();
defineExpose({ focusInput, clearSearch });

// Scroll grid to top on page change
watch(
  () => props.currentPage,
  () => {
    gridEl.value?.scrollTo({ top: 0, behavior: "smooth" });
  }
);
</script>

<style scoped>
.pos-left {
  display: flex;
  flex-direction: column;
  min-height: 0;
  height: 100%; /* fill the grid cell */
  overflow: hidden; /* contain children */
  border-right: 1px solid #d9ebf7;
  background: #f8fbff;
}

/* Search bar */
.pos-search-bar {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: white;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
}
.pos-search-input {
  width: 100%;
  padding: 9px 36px 9px 36px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
}
.pos-search-input:focus {
  border-color: #229de7;
  background: white;
}

.abs-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  pointer-events: none;
}
.abs-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  padding: 2px 4px;
  transition: color 0.15s;
}
.abs-clear:hover {
  color: #475569;
}

.pos-icon-btn {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  background: #eaf5fd;
  border: 1.5px solid #b3defa;
  border-radius: 10px;
  color: #229de7;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}
.pos-icon-btn:hover {
  background: #d9ebf7;
}

/* Categories */
.pos-categories {
  display: flex;
  gap: 6px;
  padding: 10px 16px;
  background: white;
  border-bottom: 1px solid #eef5fb;
  overflow-x: auto;
  flex-shrink: 0;
}
.pos-categories::-webkit-scrollbar {
  height: 3px;
}
.pos-categories::-webkit-scrollbar-thumb {
  background: #b3defa;
  border-radius: 3px;
}
.pos-cat-pill {
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  color: #64748b;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s;
}
.pos-cat-pill:hover {
  background: #eaf5fd;
  border-color: #229de7;
  color: #229de7;
}
.pos-cat-pill.active {
  background: #229de7;
  border-color: #229de7;
  color: white;
}

/* Idle state */
.pos-idle-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
  gap: 8px;
}
.idle-icon-wrap {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
  opacity: 0.8;
}
.idle-title {
  font-size: 15px;
  font-weight: 800;
  color: #334155;
}
.idle-sub {
  font-size: 12px;
  color: #94a3b8;
  text-align: center;
  line-height: 1.6;
}
.idle-hints {
  display: flex;
  gap: 16px;
  margin-top: 12px;
}
.idle-hint {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #94a3b8;
}
kbd {
  display: inline-flex;
  align-items: center;
  padding: 2px 7px;
  background: white;
  border: 1.5px solid #d9ebf7;
  border-radius: 5px;
  font-size: 10px;
  font-weight: 700;
  color: #64748b;
  font-family: monospace;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
}

/* Results bar */
.results-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 16px;
  background: white;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
  gap: 8px;
}
.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}
.filter-chip.cat {
  background: #eaf5fd;
  color: #229de7;
}
.filter-chip.search {
  background: #f1f5f9;
  color: #475569;
}
.chip-x {
  background: none;
  border: none;
  font-size: 14px;
  line-height: 1;
  cursor: pointer;
  color: inherit;
  opacity: 0.6;
  padding: 0 2px;
  margin-left: 2px;
  transition: opacity 0.15s;
}
.chip-x:hover {
  opacity: 1;
}
.results-count {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Product grid — constrained height, scrollable */
.pos-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 8px;
  padding: 10px 12px;
  overflow-y: auto;
  max-height: 480px;
  flex-shrink: 0;
}
.pos-products-grid::-webkit-scrollbar {
  width: 4px;
}
.pos-products-grid::-webkit-scrollbar-thumb {
  background: #d9ebf7;
  border-radius: 4px;
}

@media (max-width: 767px) {
  .pos-products-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    max-height: none; /* on mobile the panel is full-height, let it scroll naturally */
    flex: 1;
    overflow-y: auto;
  }
}

.empty-results {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
}

/* Product card */
.pos-product-card {
  position: relative;
  display: flex;
  flex-direction: column;
  background: white;
  border: 1.5px solid #d9ebf7;
  border-radius: 14px;
  padding: 12px 10px 10px;
  cursor: pointer;
  transition: all 0.15s;
  text-align: center;
}
.pos-product-card:hover:not(:disabled) {
  border-color: #229de7;
  box-shadow: 0 4px 16px rgba(34, 157, 231, 0.15);
  transform: translateY(-2px);
}
.pos-product-card.out-of-stock {
  opacity: 0.45;
  cursor: not-allowed;
}
.pos-product-card.skeleton {
  background: #f1f5f9;
  min-height: 130px;
  animation: pulse 1.4s ease-in-out infinite;
  border: none;
}
@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.45;
  }
}

/* Stale-data loading overlay on cards */
.card-loading-overlay {
  position: absolute;
  inset: 0;
  border-radius: 13px;
  background: rgba(255, 255, 255, 0.5);
  pointer-events: none;
  z-index: 1;
}

.product-avatar {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  color: #229de7;
  font-size: 19px;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 8px;
}
.product-name {
  font-size: 11px;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
  margin-bottom: 2px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.product-code {
  font-size: 9px;
  color: #94a3b8;
  font-family: monospace;
  margin-bottom: 4px;
}
.product-price {
  font-size: 12.5px;
  font-weight: 900;
  color: #229de7;
  margin-bottom: 2px;
}
.product-stock {
  font-size: 10px;
  font-weight: 600;
  color: #10b981;
}
.product-stock.low {
  color: #f59e0b;
}

/* Pagination */
.pos-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 10px 16px;
  border-top: 1px solid #eef5fb;
  background: white;
  flex-shrink: 0;
}
.pos-page-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: #eaf5fd;
  border: 1.5px solid #b3defa;
  color: #229de7;
  font-weight: 900;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}
.pos-page-btn:hover:not(:disabled) {
  background: #d9ebf7;
}
.pos-page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.pos-page-num {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: transparent;
  border: 1.5px solid transparent;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.pos-page-num:hover {
  background: #eaf5fd;
  color: #229de7;
}
.pos-page-num.active {
  background: #229de7;
  border-color: #229de7;
  color: white;
}

/* Icon transition */
.fade-icon-enter-active,
.fade-icon-leave-active {
  transition: opacity 0.15s, transform 0.15s;
}
.fade-icon-enter-from {
  opacity: 0;
  transform: translateY(-50%) scale(0.7);
}
.fade-icon-leave-to {
  opacity: 0;
  transform: translateY(-50%) scale(0.7);
}
</style>
