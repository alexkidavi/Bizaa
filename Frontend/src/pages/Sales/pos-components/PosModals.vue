<template>
  <!-- ═══ CUSTOMER MODAL ═══ -->
  <transition name="modal-fade">
    <div v-if="showCustomer" class="modal-overlay" @click.self="$emit('close-customer')">
      <div class="modal-box">
        <div class="modal-header">
          <h3 class="modal-title">Select Customer</h3>
          <button @click="$emit('close-customer')" class="modal-close">×</button>
        </div>
        <div class="p-4 border-b border-[#EEF5FB]">
          <div class="relative">
            <svg
              class="abs-icon text-slate-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
            <input
              v-model="customerSearch"
              type="text"
              placeholder="Search customers…"
              class="pos-modal-input pl-10"
              @input="filterCustomers"
            />
          </div>
        </div>
        <div class="modal-list">
          <button
            @click="$emit('select-customer', null)"
            class="modal-list-item"
            :class="{ active: !selectedCustomer }"
          >
            <div class="modal-item-avatar bg-slate-100 text-slate-500">W</div>
            <div>
              <div class="font-bold text-sm">Walk-in Customer</div>
              <div class="text-xs text-slate-400">No account required</div>
            </div>
          </button>
          <button
            v-for="c in filteredCustomers"
            :key="c.id"
            @click="$emit('select-customer', c)"
            class="modal-list-item"
            :class="{ active: selectedCustomer?.id === c.id }"
          >
            <div class="modal-item-avatar">{{ c.name?.charAt(0).toUpperCase() }}</div>
            <div class="flex-1 min-w-0">
              <div class="font-bold text-sm truncate">{{ c.name }}</div>
              <div class="text-xs text-slate-400">{{ c.phone }} · {{ c.email }}</div>
            </div>
            <div
              v-if="c.outstanding_balance > 0"
              class="text-xs font-bold text-red-500 flex-shrink-0"
            >
              Owes KES {{ Number(c.outstanding_balance).toLocaleString() }}
            </div>
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- ═══ HELD ORDERS MODAL ═══ -->
  <transition name="modal-fade">
    <div v-if="showHeld" class="modal-overlay" @click.self="$emit('close-held')">
      <div class="modal-box">
        <div class="modal-header">
          <h3 class="modal-title">Held Orders ({{ heldOrders.length }})</h3>
          <button @click="$emit('close-held')" class="modal-close">×</button>
        </div>
        <div class="modal-list">
          <div
            v-if="heldOrders.length === 0"
            class="py-12 text-center text-slate-400 text-sm font-semibold"
          >
            No held orders
          </div>
          <div v-for="(order, i) in heldOrders" :key="order.ref" class="held-order-item">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="font-bold text-sm text-slate-900">{{ order.ref }}</span>
                <span class="text-xs text-slate-400">{{
                  order.customer?.name || "Walk-in"
                }}</span>
              </div>
              <div class="text-xs text-slate-400">
                {{ order.items.length }} items · KES {{ order.total.toLocaleString() }} ·
                {{ order.time }}
              </div>
              <div class="held-expiry">
                <svg
                  class="w-3 h-3 flex-shrink-0"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <circle cx="12" cy="12" r="10" />
                  <path stroke-linecap="round" d="M12 6v6l4 2" />
                </svg>
                Expires in {{ timeRemaining(order) }}
              </div>
            </div>
            <div class="flex gap-2">
              <button @click="$emit('resume-order', i)" class="pos-btn-sm blue">
                Resume
              </button>
              <button @click="$emit('delete-held', i)" class="pos-btn-sm red">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <!-- ═══ RECEIPT MODAL ═══ -->
  <transition name="modal-fade">
    <div v-if="showReceipt" class="modal-overlay pos-print-receipt">
      <div class="modal-box receipt-modal">
        <div class="modal-header no-border">
          <h3 class="modal-title"><span class="sale-badge">✓</span> Sale Complete</h3>
          <button @click="$emit('close-receipt')" class="modal-close">×</button>
        </div>
        <div class="receipt-body" ref="receiptEl">
          <div class="receipt-header">
            <img v-if="business.logo" :src="business.logo" class="receipt-logo-img" alt="logo" />
            <div v-else class="receipt-logo">{{ business.name }}</div>
            <div class="receipt-business">{{ receiptData?.branch }}</div>
            <div class="receipt-date">{{ receiptData?.date }}</div>
            <div class="receipt-ref">Receipt #{{ receiptData?.ref }}</div>
          </div>
          <div class="receipt-divider">- - - - - - - - - - - - - - - - - -</div>
          <div class="receipt-items">
            <div
              v-for="item in receiptData?.items"
              :key="item.cart_key"
              class="receipt-item"
            >
              <div class="receipt-item-name">{{ item.name }}</div>
              <div class="receipt-item-row">
                <span class="text-slate-400"
                  >{{ item.qty }} × KES {{ Number(item.selling_price).toLocaleString() }}
                  <span v-if="item.discount > 0" class="text-emerald-600"
                    >(-{{ item.discount }}%)</span
                  >
                </span>
                <span class="font-bold">KES {{ lineTotal(item).toLocaleString() }}</span>
              </div>
            </div>
          </div>
          <div class="receipt-divider">- - - - - - - - - - - - - - - - - -</div>
          <div class="receipt-totals">
            <div class="receipt-total-row">
              <span>Subtotal</span
              ><span>KES {{ receiptData?.subtotal?.toLocaleString() }}</span>
            </div>
            <div v-if="receiptData?.discount > 0" class="receipt-total-row green">
              <span>Discount</span
              ><span>- KES {{ receiptData?.discount?.toLocaleString() }}</span>
            </div>
            <div v-if="receiptData?.tax > 0" class="receipt-total-row">
              <span>VAT (16%)</span
              ><span>KES {{ receiptData?.tax?.toLocaleString() }}</span>
            </div>
            <div class="receipt-total-row bold">
              <span>TOTAL</span
              ><span>KES {{ receiptData?.total?.toLocaleString() }}</span>
            </div>
            <div class="receipt-total-row">
              <span>{{ receiptData?.paymentMethod }}</span
              ><span>KES {{ receiptData?.paid?.toLocaleString() }}</span>
            </div>
            <div v-if="receiptData?.change > 0" class="receipt-total-row green">
              <span>Change</span
              ><span>KES {{ receiptData?.change?.toLocaleString() }}</span>
            </div>
          </div>
          <div class="receipt-divider">- - - - - - - - - - - - - - - - - -</div>
          <div class="receipt-footer">
            <div>Served by: {{ receiptData?.cashier }}</div>
            <div v-if="receiptData?.customer">Customer: {{ receiptData.customer }}</div>
            <div class="mt-2 font-bold">Thank you for shopping with us!</div>
            <div class="text-[10px] mt-1 opacity-60">Powered by Bizaa</div>
          </div>
        </div>
        <div class="receipt-actions">
          <button @click="$emit('print-receipt')" class="pos-btn-lg blue">
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
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
              />
            </svg>
            Print
          </button>
          <button @click="$emit('email-receipt')" class="pos-btn-lg outline">
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
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            Email
          </button>
          <button @click="$emit('close-receipt')" class="pos-btn-lg green">
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            New Sale
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { printReceipt, resolveBusiness } from "@/utils/printReceipt";

const auth = useAuthStore();
const business = computed(() => resolveBusiness(auth.user));

const props = defineProps({
  showCustomer: Boolean,
  showHeld: Boolean,
  showReceipt: Boolean,
  selectedCustomer: { type: Object, default: null },
  customers: { type: Array, default: () => [] },
  heldOrders: { type: Array, default: () => [] },
  receiptData: { type: Object, default: null },
  timeRemaining: { type: Function, default: () => "" },
});

const emit = defineEmits([
  "close-customer",
  "select-customer",
  "close-held",
  "resume-order",
  "delete-held",
  "close-receipt",
  "print-receipt",
  "email-receipt",
]);

const customerSearch = ref("");
const filteredCustomers = ref([]);
const receiptEl = ref(null);

// ── PRINT ─────────────────────────────────────────────────────────────────
const doPrintReceipt = () => {
  const d = props.receiptData;
  if (!d) return;

  const lineTotal = (item) => {
    const base = Number(item.selling_price) * Number(item.qty);
    return base * (1 - (Number(item.discount) || 0) / 100);
  };

  printReceipt(
    {
      ref: d.ref,
      date: d.date,
      branch: d.branch,
      cashier: d.cashier,
      customer: d.customer,
      items: (d.items || []).map((i) => ({
        name: i.name,
        qty: i.qty,
        unit_price: i.selling_price,
        discount: i.discount || 0,
        line_total: lineTotal(i),
      })),
      subtotal: d.subtotal,
      discount: d.discount,
      tax: d.tax,
      total: d.total,
      paid: d.paid,
      change: d.change,
      payments: d.paymentMethod ? [{ method: d.paymentMethod, amount: d.paid }] : [],
      qrUrl: d.etimsUrl || d.offlineUrl || null,
      kraSerial: d.kraSerial || null,
      etimsConfirmed: !!d.etimsUrl,
    },
    business.value
  );

  // Close modal after popup opens
  const checkClose = setInterval(() => {
    clearInterval(checkClose);
    emit("close-receipt");
  }, 800);
};

defineExpose({ printReceipt: doPrintReceipt });

// Sync customers list when it loads / modal opens
watch(
  () => props.customers,
  (val) => {
    filteredCustomers.value = val;
  },
  { immediate: true }
);
watch(
  () => props.showCustomer,
  (v) => {
    if (v) {
      customerSearch.value = "";
      filteredCustomers.value = props.customers;
    }
  }
);

const filterCustomers = () => {
  const q = customerSearch.value.toLowerCase();
  filteredCustomers.value = props.customers.filter(
    (c) => c.name?.toLowerCase().includes(q) || c.phone?.includes(q)
  );
};

const lineTotal = (item) =>
  Math.round(item.selling_price * item.qty * (1 - (item.discount || 0) / 100));
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-box {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
}

@media (max-width: 767px) {
  .modal-overlay {
    padding: 0;
    align-items: flex-end;
  }
  .modal-box {
    border-radius: 20px 20px 0 0;
    max-height: 92vh;
    max-width: 100%;
  }
  .receipt-modal {
    border-radius: 20px 20px 0 0;
    max-width: 100%;
  }
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
}
.modal-header.no-border {
  border-bottom: none;
}
.modal-title {
  font-size: 16px;
  font-weight: 900;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}
.sale-badge {
  display: inline-flex;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #10b981;
  color: white;
  font-size: 12px;
  align-items: center;
  justify-content: center;
}
.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  font-size: 20px;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.modal-close:hover {
  background: #fef2f2;
  border-color: #fecaca;
  color: #ef4444;
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
.pos-modal-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
}
.pos-modal-input:focus {
  border-color: #229de7;
  background: white;
}

.modal-list {
  overflow-y: auto;
  flex: 1;
}
.modal-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #eef5fb;
}
.modal-list-item:hover {
  background: #f5faff;
}
.modal-list-item.active {
  background: #eaf5fd;
}
.modal-item-avatar {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #eaf5fd;
  border: 1.5px solid #b3defa;
  color: #229de7;
  font-weight: 900;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.held-order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  border-bottom: 1px solid #eef5fb;
}
.held-expiry {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
  font-size: 10px;
  font-weight: 600;
  color: #f59e0b;
}

/* Receipt */
.receipt-modal {
  max-width: 360px;
}
.receipt-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
  font-family: "Courier New", monospace;
  font-size: 12px;
  background: #fffef7;
}
.receipt-header {
  text-align: center;
  margin-bottom: 8px;
}
.receipt-logo {
  font-size: 20px;
  font-weight: 900;
  letter-spacing: 4px;
  color: #1e293b;
}
.receipt-logo-img {
  max-height: 48px;
  max-width: 140px;
  object-fit: contain;
  display: block;
  margin: 0 auto 6px;
}
.receipt-business {
  font-size: 11px;
  color: #64748b;
}
.receipt-date,
.receipt-ref {
  font-size: 10px;
  color: #94a3b8;
}
.receipt-divider {
  text-align: center;
  color: #cbd5e1;
  font-size: 11px;
  margin: 8px 0;
}
.receipt-item {
  margin-bottom: 6px;
}
.receipt-item-name {
  font-weight: 700;
  font-size: 11px;
}
.receipt-item-row {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  color: #64748b;
}
.receipt-total-row {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  padding: 2px 0;
}
.receipt-total-row.bold {
  font-weight: 900;
  font-size: 14px;
  margin-top: 4px;
}
.receipt-total-row.green {
  color: #10b981;
}
.receipt-footer {
  text-align: center;
  color: #94a3b8;
  font-size: 10px;
  margin-top: 8px;
}
.receipt-actions {
  display: flex;
  gap: 8px;
  padding: 14px 16px;
  border-top: 1px solid #eef5fb;
  flex-shrink: 0;
}

/* Buttons */
.pos-btn-sm {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  border: 1.5px solid transparent;
}
.pos-btn-sm.blue {
  background: #eaf5fd;
  border-color: #b3defa;
  color: #229de7;
}
.pos-btn-sm.blue:hover {
  background: #229de7;
  color: white;
}
.pos-btn-sm.red {
  background: #fef2f2;
  border-color: #fecaca;
  color: #ef4444;
}
.pos-btn-sm.red:hover {
  background: #ef4444;
  color: white;
}
.pos-btn-lg {
  flex: 1;
  padding: 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: 1.5px solid transparent;
}
.pos-btn-lg.blue {
  background: #229de7;
  color: white;
  border-color: #229de7;
}
.pos-btn-lg.blue:hover {
  background: #1682c4;
}
.pos-btn-lg.green {
  background: #10b981;
  color: white;
  border-color: #10b981;
}
.pos-btn-lg.green:hover {
  background: #059669;
}
.pos-btn-lg.outline {
  background: white;
  color: #64748b;
  border-color: #d9ebf7;
}
.pos-btn-lg.outline:hover {
  border-color: #229de7;
  color: #229de7;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
