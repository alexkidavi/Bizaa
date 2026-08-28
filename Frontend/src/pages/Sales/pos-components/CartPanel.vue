<template>
  <section class="pos-right">
    <!-- M-Pesa Modal -->
    <MpesaModal
      :show="showMpesaModal"
      :grand-total="grandTotal"
      :order-ref="orderRef"
      @close="showMpesaModal = false"
      @payment-confirmed="onMpesaConfirmed"
    />
    <!-- ── Customer + Order bar ── -->
    <div class="pos-cart-topbar">
      <button @click="$emit('open-customer')" class="cart-customer-btn">
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
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
          />
        </svg>
        <span class="truncate">{{
          selectedCustomer ? selectedCustomer.name : "Walk-in Customer"
        }}</span>
        <svg
          class="w-3 h-3 text-slate-400 flex-shrink-0"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <div class="flex items-center gap-2">
        <span class="text-xs font-mono font-bold text-[#229DE7]">{{ orderRef }}</span>
        <button @click="$emit('new-order')" class="cart-action-icon" title="New Order">
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
        </button>
        <button
          @click="$emit('hold-order')"
          class="cart-action-icon"
          title="Hold Order"
          :disabled="cart.length === 0"
        >
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6" />
          </svg>
        </button>
      </div>
    </div>

    <!-- ── Sell-by-Price Toggle (very prominent) ── -->
    <div
      class="sbp-banner"
      :class="{ active: sellByPrice }"
      @click="$emit('toggle-sell-by-price')"
    >
      <div class="sbp-icon">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="sbp-text">
        <div class="sbp-label">
          Sell by Price
          <span class="sbp-badge" :class="sellByPrice ? 'on' : 'off'">{{ sellByPrice ? 'ON' : 'OFF' }}</span>
        </div>
        <div class="sbp-desc">{{ sellByPrice ? 'Enter amount → qty calculated automatically' : 'Tap to enter KES amount per item' }}</div>
      </div>
      <label class="sbp-switch" @click.stop>
        <input type="checkbox" :checked="sellByPrice" @change="$emit('toggle-sell-by-price')" />
        <span class="sbp-slider"></span>
      </label>
    </div>

    <!-- ── Cart Items ── -->
    <div class="pos-cart-items" ref="cartEl">
      <div v-if="cart.length === 0" class="cart-empty">
        <svg
          class="w-14 h-14 text-slate-200 mb-3"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"
          />
        </svg>
        <p class="text-slate-400 text-sm font-semibold">Cart is empty</p>
        <p class="text-slate-300 text-xs mt-1">Click a product or scan a barcode</p>
      </div>

      <div v-else class="cart-list">
        <div
          v-for="(item, index) in cart"
          :key="item.cart_key"
          class="cart-item"
          :class="{ highlight: item._new }"
        >
          <div class="cart-item-main">
            <div class="cart-item-avatar">{{ item.name?.charAt(0).toUpperCase() }}</div>
            <div class="cart-item-info">
              <div class="cart-item-name">{{ item.name }}</div>
              <div class="cart-item-meta">
                <span v-if="item.enteredPrice != null" class="sbp-qty-meta">
                  {{ fmtQty(item.qty) }} {{ item.unit_name || 'unit' }} @ KES {{ Number(item.selling_price).toLocaleString() }}
                </span>
                <template v-else>
                  <span class="text-[#229DE7] font-bold"
                    >KES {{ Number(item.selling_price).toLocaleString() }}</span
                  >
                  <span
                    v-if="item.discount > 0"
                    class="text-emerald-500 text-[10px] font-bold"
                    >-{{ item.discount }}%</span
                  >
                </template>
              </div>
            </div>
            <!-- Sell-by-price: show orange qty badge, no +/- -->
            <div v-if="item.enteredPrice != null" class="sbp-qty-badge">
              {{ fmtQty(item.qty) }}
            </div>
            <!-- Normal: +/- controls -->
            <div v-else class="cart-item-controls">
              <button @click="$emit('decrease-qty', index)" class="qty-btn">−</button>
              <input
                type="number"
                :value="item.qty"
                @change="$emit('set-qty', index, +$event.target.value)"
                min="0.001"
                step="any"
                class="qty-input"
              />
              <button
                @click="$emit('increase-qty', index)"
                class="qty-btn"
              >
                +
              </button>
            </div>
            <div class="cart-item-total">KES {{ lineTotal(item).toLocaleString() }}</div>
            <button @click="$emit('remove', index)" class="cart-remove-btn">×</button>
          </div>

          <div class="cart-item-extras">
            <button @click="$emit('toggle-discount', index)" class="extras-btn">
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
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              Discount
            </button>
            <div v-if="item._showDiscount" class="inline-discount">
              <input
                type="number"
                :value="item.discount"
                @change="$emit('set-item-discount', index, +$event.target.value)"
                min="0"
                max="100"
                placeholder="%"
                class="discount-input"
              />
              %
            </div>
            <input
              type="text"
              :value="item.note"
              @input="$emit('set-item-note', index, $event.target.value)"
              placeholder="Item note..."
              class="item-note-input"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- ── Totals ── -->
    <div class="pos-totals">
      <div class="totals-row">
        <span>Subtotal</span>
        <span>KES {{ subtotal.toLocaleString() }}</span>
      </div>
      <div class="totals-row">
        <div class="flex items-center gap-2">
          <span>Discount</span>
          <div class="flex items-center gap-1">
            <select
              :value="discountType"
              @change="$emit('update:discountType', $event.target.value)"
              class="discount-type-select"
            >
              <option value="percent">%</option>
              <option value="fixed">KES</option>
            </select>
            <input
              type="number"
              :value="globalDiscount"
              @input="$emit('update:globalDiscount', +$event.target.value)"
              class="discount-value-input"
              placeholder="0"
              min="0"
            />
          </div>
        </div>
        <span class="text-emerald-600">- KES {{ discountAmount.toLocaleString() }}</span>
      </div>
      <div class="totals-row">
        <div class="flex items-center gap-2">
          <span>Tax (VAT 16%)</span>
          <label class="toggle-switch">
            <input
              type="checkbox"
              :checked="applyTax"
              @change="$emit('update:applyTax', $event.target.checked)"
            />
            <span class="toggle-slider"></span>
          </label>
        </div>
        <span>KES {{ taxAmount.toLocaleString() }}</span>
      </div>
      <div class="totals-divider"></div>
      <div class="totals-total-row">
        <span>TOTAL</span>
        <span>KES {{ grandTotal.toLocaleString() }}</span>
      </div>
    </div>

    <!-- ── Payment Methods ── -->
    <div class="pos-payment-methods">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">
        Payment Method
      </p>
      <div class="payment-pills">
        <button
          v-for="m in paymentMethods"
          :key="m.value"
          @click="$emit('update:selectedPayment', m.value)"
          class="payment-pill"
          :class="{ active: selectedPayment === m.value }"
        >
          <component :is="m.icon" />
          {{ m.label }}
        </button>
      </div>

      <!-- Split -->
      <div v-if="selectedPayment === 'split'" class="split-payment-grid">
        <div v-for="m in splitMethods" :key="m.key" class="split-row">
          <span class="split-label">{{ m.label }}</span>
          <input
            type="number"
            v-model.number="m.amount"
            class="split-input"
            placeholder="0"
            min="0"
          />
        </div>
      </div>

      <!-- Cash tendered -->
      <div v-if="selectedPayment === 'cash'" class="cash-tendered-row">
        <div class="flex-1">
          <label
            class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-1"
            >Cash Tendered</label
          >
          <input
            type="number"
            :value="cashTendered"
            @input="$emit('update:cashTendered', +$event.target.value)"
            class="pos-input"
            placeholder="Enter amount"
          />
        </div>
        <div v-if="cashTendered >= grandTotal && grandTotal > 0" class="change-display">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">
            Change
          </div>
          <div class="text-xl font-extrabold text-emerald-500">
            KES {{ change.toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Pay with Points -->
      <div v-if="selectedPayment === 'points'" class="points-payment-box">
        <div v-if="!selectedCustomer" class="points-warning">
          Select a customer first — points can only be redeemed for a registered customer.
        </div>
        <div v-else-if="loadingPreview" class="points-loading">Checking points balance…</div>
        <template v-else-if="pointsPreview">
          <div v-if="!pointsPreview.enabled" class="points-warning">Loyalty points aren't enabled for this business.</div>
          <div v-else-if="!pointsPreview.meets_minimum" class="points-warning">
            {{ selectedCustomer.name }} has {{ pointsPreview.balance }} points, below the minimum of
            {{ pointsPreview.min_redeem_points }} required to redeem.
          </div>
          <div v-else class="points-summary" :class="{ short: !pointsPreview.can_cover }">
            <div class="points-row">
              <span>{{ selectedCustomer.name }}'s balance</span>
              <strong>{{ pointsPreview.balance }} pts (~KES {{ Number(pointsPreview.available_cash).toLocaleString() }})</strong>
            </div>
            <div class="points-row">
              <span>Points needed for this sale</span>
              <strong>{{ pointsPreview.points_required }} pts</strong>
            </div>
            <div class="points-verdict">
              {{ pointsPreview.can_cover
                ? '✓ Enough points to cover this sale in full.'
                : '✗ Not enough points to cover this sale — choose another payment method.' }}
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- ── Charge + Quick Actions ── -->
    <div class="pos-charge-area">
      <button
        @click="handleCharge"
        :disabled="cart.length === 0 || processing || pointsChargeBlocked"
        class="pos-charge-btn"
        :class="{ processing }"
      >
        <span v-if="processing" class="flex items-center gap-2">
          <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          Processing...
        </span>
        <span v-else class="flex items-center justify-between w-full">
          <span class="flex items-center gap-2">
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"
              />
            </svg>
            Charge
          </span>
          <span class="text-xl font-black">KES {{ grandTotal.toLocaleString() }}</span>
        </span>
      </button>

      <div class="quick-actions">
        <button
          @click="$emit('print-quote')"
          :disabled="cart.length === 0"
          class="quick-btn"
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
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Quote
        </button>
        <button @click="$emit('open-held')" class="quick-btn">
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
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          {{ heldCount }} Held
        </button>
        <button
          @click="$emit('clear-cart')"
          :disabled="cart.length === 0"
          class="quick-btn danger"
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
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            />
          </svg>
          Clear
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";
import MpesaModal from "./MpesaModal.vue";
import LoyaltyService from "@/services/LoyaltyService";

const props = defineProps({
  cart: { type: Array, default: () => [] },
  sellByPrice: { type: Boolean, default: false },
  selectedCustomer: { type: Object, default: null },
  orderRef: String,
  subtotal: Number,
  discountAmount: Number,
  taxAmount: Number,
  grandTotal: Number,
  discountType: String,
  globalDiscount: Number,
  applyTax: Boolean,
  selectedPayment: String,
  cashTendered: Number,
  change: Number,
  processing: Boolean,
  heldCount: { type: Number, default: 0 },
});

const emit = defineEmits([
  "open-customer",
  "new-order",
  "hold-order",
  "remove",
  "increase-qty",
  "decrease-qty",
  "set-qty",
  "toggle-discount",
  "set-item-discount",
  "set-item-note",
  "update:discountType",
  "update:globalDiscount",
  "update:applyTax",
  "update:selectedPayment",
  "update:cashTendered",
  "process-payment",
  "print-quote",
  "open-held",
  "clear-cart",
  "toggle-sell-by-price",
]);

const cartEl = ref(null);
const showMpesaModal = ref(false);

// Intercept charge — open M-Pesa modal if mpesa is selected, block points
// charging until there's a customer + a preview that says it's covered
// (the backend re-validates and is the real authority, but this avoids a
// pointless round-trip for an obviously-doomed charge), else emit normally.
const handleCharge = () => {
  if (props.selectedPayment === "mpesa") {
    showMpesaModal.value = true;
    return;
  }
  if (props.selectedPayment === "points") {
    if (!props.selectedCustomer || !pointsPreview.value?.can_cover) return;
  }
  emit("process-payment");
};

// M-Pesa confirmed from modal — pass meta to parent then trigger payment
const onMpesaConfirmed = (mpesaMeta) => {
  showMpesaModal.value = false;
  emit("process-payment", mpesaMeta);
};

const lineTotal = (item) => {
  if (item.enteredPrice != null) return item.enteredPrice;
  const base = item.selling_price * item.qty;
  return Math.round(base * (1 - (item.discount || 0) / 100));
};

const fmtQty = (qty) => {
  if (qty === Math.trunc(qty)) return String(Math.trunc(qty));
  return parseFloat(qty.toFixed(4)).toString();
};

// Scroll to bottom when items added
watch(
  () => props.cart.length,
  (n, o) => {
    if (n > o)
      nextTick(() => {
        if (cartEl.value) cartEl.value.scrollTop = cartEl.value.scrollHeight;
      });
  }
);

const paymentMethods = [
  {
    value: "cash",
    label: "Cash",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>`,
    },
  },
  {
    value: "mpesa",
    label: "M-Pesa",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`,
    },
  },
  {
    value: "card",
    label: "Card",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>`,
    },
  },
  {
    value: "credit",
    label: "Credit",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>`,
    },
  },
  {
    value: "split",
    label: "Split",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>`,
    },
  },
  {
    value: "points",
    label: "Points",
    icon: {
      template: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>`,
    },
  },
];

// "Pay with Points" needs a live check against the selected customer's
// balance — fetched whenever this method is chosen (or the customer/total
// changes while it's active) so the cashier sees "enough" or "not enough"
// before hitting Charge, matching the same live-feedback pattern as the
// cash-tendered change display.
const pointsPreview = ref(null);
const loadingPreview = ref(false);
let previewDebounce = null;

const fetchPointsPreview = () => {
  clearTimeout(previewDebounce);
  if (props.selectedPayment !== "points" || !props.selectedCustomer || !props.grandTotal) {
    pointsPreview.value = null;
    return;
  }
  previewDebounce = setTimeout(async () => {
    loadingPreview.value = true;
    try {
      const res = await LoyaltyService.previewRedeem(props.selectedCustomer.id, props.grandTotal);
      pointsPreview.value = res;
    } catch {
      pointsPreview.value = null;
    } finally {
      loadingPreview.value = false;
    }
  }, 300);
};

watch(() => [props.selectedPayment, props.selectedCustomer, props.grandTotal], fetchPointsPreview, { immediate: true });

const pointsChargeBlocked = computed(() =>
  props.selectedPayment === "points" && (!props.selectedCustomer || !pointsPreview.value?.can_cover)
);

const splitMethods = ref([
  { key: "cash", label: "Cash", amount: 0 },
  { key: "mpesa", label: "M-Pesa", amount: 0 },
  { key: "card", label: "Card", amount: 0 },
]);
</script>

<style scoped>
.pos-right {
  display: flex;
  flex-direction: column;
  background: white;
  min-height: 0;
  height: 100%; /* fill the grid cell */
  overflow-y: auto; /* let the panel scroll as a whole on short viewports instead of clipping content */
  overflow-x: hidden;
}

/* ── Sell-by-Price Banner ── */
.sbp-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 8px 12px;
  padding: 10px 14px;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
  user-select: none;
}
.sbp-banner:hover {
  border-color: #fb923c;
  background: #fff7ed;
}
.sbp-banner.active {
  border-color: #f97316;
  background: #fff7ed;
}
.sbp-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  background: #f97316;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sbp-banner:not(.active) .sbp-icon {
  background: #e2e8f0;
  color: #94a3b8;
}
.sbp-text {
  flex: 1;
  min-width: 0;
}
.sbp-label {
  font-size: 12px;
  font-weight: 800;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 6px;
}
.sbp-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
}
.sbp-badge.on {
  background: #f97316;
  color: white;
}
.sbp-badge.off {
  background: #e2e8f0;
  color: #64748b;
}
.sbp-desc {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 2px;
}
.sbp-switch {
  position: relative;
  width: 38px;
  height: 22px;
  flex-shrink: 0;
}
.sbp-switch input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}
.sbp-slider {
  position: absolute;
  inset: 0;
  border-radius: 999px;
  background: #e2e8f0;
  cursor: pointer;
  transition: background 0.2s;
}
.sbp-slider::before {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  left: 3px;
  top: 3px;
  border-radius: 50%;
  background: white;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.sbp-switch input:checked + .sbp-slider {
  background: #f97316;
}
.sbp-switch input:checked + .sbp-slider::before {
  transform: translateX(16px);
}
/* Sell-by-price item display */
.sbp-qty-badge {
  padding: 3px 10px;
  border-radius: 6px;
  background: rgba(249,115,22,0.1);
  border: 1.5px solid rgba(249,115,22,0.3);
  font-size: 11px;
  font-weight: 700;
  color: #f97316;
  white-space: nowrap;
}
.sbp-qty-meta {
  font-size: 10px;
  color: #f97316;
  font-weight: 600;
}

.pos-cart-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
}
.cart-customer-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s;
  max-width: 190px;
}
.cart-customer-btn:hover {
  border-color: #229de7;
  color: #229de7;
}
.cart-action-icon {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}
.cart-action-icon:hover:not(:disabled) {
  background: #eaf5fd;
  border-color: #b3defa;
  color: #229de7;
}
.cart-action-icon:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pos-cart-items {
  flex: 1 1 0; /* grow and shrink, baseline 0 so flex can divide space properly */
  overflow-y: auto;
  overflow-x: hidden;
  min-height: 160px; /* always show room for cart items, even on short viewports where siblings would otherwise squeeze this to nothing */
  padding: 8px 12px;
}
.pos-cart-items::-webkit-scrollbar {
  width: 4px;
}
.pos-cart-items::-webkit-scrollbar-thumb {
  background: #d9ebf7;
  border-radius: 4px;
}

.cart-empty {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.cart-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.cart-item {
  background: #f8fafb;
  border: 1.5px solid #e8f0f8;
  border-radius: 12px;
  padding: 8px 10px;
  min-height: 52px; /* fixed baseline card height so items render consistently across devices */
  flex-shrink: 0;
  transition: all 0.3s;
}
.cart-item.highlight {
  border-color: #229de7;
  background: #eaf5fd;
  animation: flash 0.6s ease;
}
@keyframes flash {
  0% {
    transform: scale(1.02);
  }
  50% {
    transform: scale(1);
  }
}

.cart-item-main {
  display: flex;
  align-items: center;
  gap: 8px;
}
.cart-item-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  color: #229de7;
  font-weight: 900;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.cart-item-info {
  flex: 1;
  min-width: 0;
}
.cart-item-name {
  font-size: 11.5px;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cart-item-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 10px;
}
.cart-item-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}
.qty-btn {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: #eaf5fd;
  border: 1.5px solid #b3defa;
  color: #229de7;
  font-weight: 900;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qty-btn:hover:not(:disabled) {
  background: #d9ebf7;
}
.qty-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.qty-input {
  width: 36px;
  text-align: center;
  padding: 2px 0;
  font-size: 12px;
  font-weight: 800;
  border: 1.5px solid #d9ebf7;
  border-radius: 6px;
  outline: none;
  color: #1e293b;
}
.cart-item-total {
  font-size: 12px;
  font-weight: 900;
  color: #1e293b;
  min-width: 70px;
  text-align: right;
  flex-shrink: 0;
}
.cart-remove-btn {
  width: 20px;
  height: 20px;
  border-radius: 5px;
  color: #94a3b8;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: color 0.15s;
  flex-shrink: 0;
}
.cart-remove-btn:hover {
  color: #ef4444;
  background: #fef2f2;
}

.cart-item-extras {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px solid #eef5fb;
}
.extras-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}
.extras-btn:hover {
  border-color: #229de7;
  color: #229de7;
}
.inline-discount {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
}
.discount-input {
  width: 44px;
  text-align: center;
  padding: 3px;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  font-size: 11px;
  outline: none;
}
.item-note-input {
  flex: 1;
  padding: 3px 8px;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  font-size: 10px;
  color: #64748b;
  outline: none;
  background: white;
}

/* Totals */
.pos-totals {
  padding: 10px 14px;
  border-top: 1px solid #eef5fb;
  flex-shrink: 0;
  background: #fafcff;
}
.totals-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  padding: 3px 0;
}
.totals-divider {
  height: 1px;
  background: #eef5fb;
  margin: 6px 0;
}
.totals-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 15px;
  font-weight: 900;
  color: #1e293b;
}
.discount-type-select {
  padding: 2px 4px;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  font-size: 10px;
  outline: none;
  background: white;
}
.discount-value-input {
  width: 52px;
  padding: 2px 6px;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  outline: none;
  text-align: center;
}
.toggle-switch {
  position: relative;
  display: inline-flex;
  align-items: center;
  cursor: pointer;
}
.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.toggle-slider {
  width: 28px;
  height: 15px;
  background: #d9ebf7;
  border-radius: 15px;
  transition: background 0.2s;
  position: relative;
}
.toggle-slider::before {
  content: "";
  position: absolute;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: white;
  top: 2px;
  left: 2px;
  transition: transform 0.2s;
}
.toggle-switch input:checked + .toggle-slider {
  background: #229de7;
}
.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(13px);
}

/* Payment */
.pos-payment-methods {
  padding: 10px 14px;
  border-top: 1px solid #eef5fb;
  flex-shrink: 0;
}
.payment-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}
.payment-pill {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
  flex: 1;
  justify-content: center;
  min-width: 0;
}
.payment-pill:hover {
  border-color: #229de7;
  color: #229de7;
}
.payment-pill.active {
  background: #229de7;
  border-color: #229de7;
  color: white;
}

@media (max-width: 767px) {
  .payment-pill {
    padding: 7px 6px;
    font-size: 10px;
  }
}
.cash-tendered-row {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}
.change-display {
  padding: 8px 12px;
  background: #ecfdf5;
  border: 1.5px solid #a7f3d0;
  border-radius: 10px;
  text-align: center;
  flex-shrink: 0;
}
.pos-input {
  width: 100%;
  padding: 8px 12px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  outline: none;
}
.pos-input:focus {
  border-color: #229de7;
  background: white;
}
.split-payment-grid {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.split-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.split-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  width: 50px;
  flex-shrink: 0;
}
.split-input {
  flex: 1;
  padding: 6px 10px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  outline: none;
}

/* Pay with Points */
.points-payment-box {
  font-size: 12px;
}
.points-warning {
  padding: 10px 12px;
  background: #fff7ed;
  border: 1.5px solid #fed7aa;
  border-radius: 10px;
  color: #c2410c;
  font-weight: 600;
  font-size: 11.5px;
}
.points-loading {
  padding: 10px 12px;
  color: #94a3b8;
  font-weight: 600;
  font-size: 11.5px;
}
.points-summary {
  padding: 10px 12px;
  background: #ecfdf5;
  border: 1.5px solid #a7f3d0;
  border-radius: 10px;
}
.points-summary.short {
  background: #fef2f2;
  border-color: #fecaca;
}
.points-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11.5px;
  color: #475569;
  padding: 2px 0;
}
.points-row strong {
  color: #1e293b;
  font-weight: 800;
}
.points-verdict {
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  font-size: 11px;
  font-weight: 700;
}
.points-summary:not(.short) .points-verdict {
  color: #059669;
}
.points-summary.short .points-verdict {
  color: #dc2626;
}

/* Charge */
.pos-charge-area {
  padding: 12px 14px;
  border-top: 1px solid #eef5fb;
  flex-shrink: 0;
}
.pos-charge-btn {
  width: 100%;
  padding: 14px 16px;
  background: linear-gradient(135deg, #229de7, #1682c4);
  color: white;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 900;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(34, 157, 231, 0.4);
  transition: all 0.15s;
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}
.pos-charge-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #1682c4, #0f6fab);
  transform: translateY(-1px);
}
.pos-charge-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}
.pos-charge-btn.processing {
  background: #64748b;
  box-shadow: none;
}

.quick-actions {
  display: flex;
  gap: 6px;
}
.quick-btn {
  flex: 1;
  padding: 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
}
.quick-btn:hover:not(:disabled) {
  border-color: #229de7;
  color: #229de7;
  background: #eaf5fd;
}
.quick-btn.danger:hover:not(:disabled) {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}
.quick-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
