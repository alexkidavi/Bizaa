<template>
  <div class="mkt-container store-layout">
    <!-- Sidebar filters -->
    <aside class="store-filters">
      <div class="store-filters__head">
        <h3>Filters</h3>
        <button @click="resetFilters">Clear all</button>
      </div>

      <div class="store-filters__group">
        <h4>Availability</h4>
        <div class="store-filter-item">
          <input type="checkbox" id="inStock" v-model="filterInStock" />
          <label for="inStock">In stock only</label>
        </div>
      </div>

      <div class="store-filters__group">
        <h4>Price Range</h4>
        <div style="display:flex;gap:8px;align-items:center">
          <input v-model.number="priceMin" type="number" placeholder="Min" style="width:80px;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;font-size:13px;outline:none" />
          <span style="font-size:12px;color:#94a3b8">–</span>
          <input v-model.number="priceMax" type="number" placeholder="Max" style="width:80px;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;font-size:13px;outline:none" />
        </div>
      </div>


      <!-- Store info card -->
      <div class="store-info-card" style="margin-top:16px;border-radius:0;border-left:none;border-right:none;border-bottom:none">
        <div class="store-info-card__head">Store Info</div>
        <div class="store-info-card__body">
          <div class="store-info-row">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.17h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6 6l.93-.94a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <div><strong>Phone</strong><span>{{ store.phone }}</span></div>
          </div>
          <div class="store-info-row">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <div><strong>Email</strong><span>{{ store.email }}</span></div>
          </div>
          <div class="store-info-row">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <div><strong>Hours</strong><span>{{ store.hours }}</span></div>
          </div>
          <div class="store-info-row">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
            <div><strong>Location</strong><span>{{ store.location }}</span></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main products area -->
    <div>
      <div class="store-products__toolbar">
        <div class="store-products__search">
          <input v-model="searchQ" type="text" :placeholder="`Search in ${store.name}…`" />
          <button>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <span class="store-products__count">{{ filtered.length }} products</span>
          <div class="store-products__sort">
            Sort by
            <select v-model="sortBy">
              <option value="default">Relevance</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="newest">Newest</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filtered.length === 0" style="text-align:center;padding:60px 20px;color:#64748b">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 16px;display:block;color:#cbd5e1"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <p style="font-size:16px;font-weight:600;color:#334155;margin-bottom:6px">No products found</p>
        <p style="font-size:14px">Try adjusting your filters or search terms</p>
      </div>

      <div v-else class="store-products-grid">
        <div v-for="p in filtered" :key="p.id" class="mkt-product-card" style="cursor:pointer" @click="goToProduct(p)">
          <div class="mkt-product-card__img" :style="cardBg(p)">
            <div class="mkt-product-card__badges">
              <span v-if="p.stock <= 0" class="mkt-badge mkt-badge--out">Out of stock</span>
            </div>
            <div class="mkt-product-card__img-inner">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" v-html="catIcon(p.category)"></svg>
            </div>
            <button class="mkt-product-card__wishlist" @click.stop>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
          </div>

          <div class="mkt-product-card__body">
            <p class="mkt-product-card__name">{{ p.name }}</p>
            <div class="mkt-product-card__price">
              <span class="mkt-product-card__amount">KES {{ Number(p.selling_price).toLocaleString() }}</span>
            </div>
            <div class="mkt-product-card__footer">
              <button class="mkt-product-card__cart" :class="{ 'mkt-product-card__cart--out': p.stock <= 0 }" @click.stop>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                {{ p.stock > 0 ? "Add to cart" : "Out of stock" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { getCategoryIcon, getCategoryColor } from "../utils/categoryConfig.js";

const props = defineProps({
  store: { type: Object, required: true },
  products: { type: Array, required: true },
});

const router = useRouter();

function goToProduct(p) {
  router.push(`/marketplace/product/${p.id}`);
}

const searchQ = ref("");
const sortBy = ref("default");
const filterInStock = ref(false);
const priceMin = ref(null);
const priceMax = ref(null);

function resetFilters() {
  searchQ.value = "";
  sortBy.value = "default";
  filterInStock.value = false;
  priceMin.value = null;
  priceMax.value = null;
}

const filtered = computed(() => {
  let list = [...props.products];

  if (searchQ.value.trim()) {
    const q = searchQ.value.toLowerCase();
    list = list.filter(p => p.name.toLowerCase().includes(q));
  }
  if (filterInStock.value) list = list.filter(p => p.stock > 0);
  if (priceMin.value) list = list.filter(p => Number(p.selling_price) >= priceMin.value);
  if (priceMax.value) list = list.filter(p => Number(p.selling_price) <= priceMax.value);

  if (sortBy.value === "price-asc") list.sort((a, b) => Number(a.selling_price) - Number(b.selling_price));
  else if (sortBy.value === "price-desc") list.sort((a, b) => Number(b.selling_price) - Number(a.selling_price));
  else if (sortBy.value === "newest") list.sort((a, b) => b.id - a.id);

  return list;
});

function cardBg(p) {
  const color = p.color || getCategoryColor(p.category);
  return { background: `linear-gradient(135deg, ${color}cc 0%, ${color} 100%)` };
}

function catIcon(cat) {
  return getCategoryIcon(cat);
}
</script>
