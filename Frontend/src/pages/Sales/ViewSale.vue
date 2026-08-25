<template>
  <div class="sale-root">
    <!-- ── Header ── -->
    <div class="page-header">
      <div class="header-left">
        <router-link to="/sales" class="back-btn">
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Sales
        </router-link>
        <div class="header-divider"></div>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="page-title">{{ sale?.reference || "…" }}</h1>
            <span v-if="sale" class="status-pill" :class="sale.status">{{
              sale.status
            }}</span>
          </div>
          <p class="page-sub">{{ sale ? formatDateTime(sale.created_at) : "" }}</p>
        </div>
      </div>
      <div class="header-actions">
        <button v-if="!editing" @click="editing = true" class="btn-outline">
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
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
            />
          </svg>
          Edit Sale
        </button>
        <button v-if="editing" @click="cancelEdit" class="btn-outline danger">
          Cancel
        </button>
        <button v-if="editing" @click="saveSale" :disabled="saving" class="btn-primary">
          <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
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
          <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          {{ saving ? "Saving…" : "Save Changes" }}
        </button>
        <button @click="printReceipt" class="btn-outline">
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
          Print Receipt
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <span>Loading sale…</span>
    </div>

    <template v-else-if="sale">
      <div class="sale-body">
        <!-- ══════════════════════
             LEFT COLUMN
        ══════════════════════ -->
        <div class="left-col">
          <!-- Sale Items -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">
                <svg
                  class="w-4 h-4 text-[#229DE7]"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                  />
                </svg>
                Items ({{ saleItems.length }})
              </h2>
            </div>

            <!-- View mode -->
            <div v-if="!editing" class="items-list">
              <div v-for="item in saleItems" :key="item.id" class="item-row">
                <div class="item-avatar">{{ item.product?.name?.charAt(0) || "P" }}</div>
                <div class="item-info">
                  <div class="item-name">
                    {{ item.product?.name || item.product_name || "Product" }}
                  </div>
                  <div class="item-meta">
                    {{ item.quantity }} × KES
                    {{ Number(item.unit_price).toLocaleString() }}
                    <span v-if="item.discount > 0" class="text-emerald-600"
                      >(-{{ item.discount }}%)</span
                    >
                    <span v-if="item.note" class="item-note">· {{ item.note }}</span>
                  </div>
                </div>
                <div class="item-total">KES {{ itemTotal(item).toLocaleString() }}</div>
              </div>
            </div>

            <!-- Edit mode -->
            <div v-else class="items-list">
              <div
                v-for="(item, i) in editItems"
                :key="item.id || i"
                class="item-row edit-mode"
              >
                <div class="item-avatar">{{ item.product?.name?.charAt(0) || "P" }}</div>
                <div class="item-info flex-1">
                  <div class="item-name">
                    {{ item.product?.name || item.product_name }}
                  </div>
                  <div class="edit-item-controls">
                    <div class="edit-field-group">
                      <label class="edit-label">Qty</label>
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        min="1"
                        class="edit-input w-16"
                      />
                    </div>
                    <div class="edit-field-group">
                      <label class="edit-label">Price</label>
                      <input
                        v-model.number="item.unit_price"
                        type="number"
                        min="0"
                        class="edit-input w-24"
                      />
                    </div>
                    <div class="edit-field-group">
                      <label class="edit-label">Disc %</label>
                      <input
                        v-model.number="item.discount"
                        type="number"
                        min="0"
                        max="100"
                        class="edit-input w-16"
                      />
                    </div>
                    <div class="edit-field-group flex-1">
                      <label class="edit-label">Note</label>
                      <input
                        v-model="item.note"
                        type="text"
                        placeholder="Optional note"
                        class="edit-input"
                      />
                    </div>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <div class="item-total">KES {{ itemTotal(item).toLocaleString() }}</div>
                  <button @click="removeEditItem(i)" class="remove-item-btn">×</button>
                </div>
              </div>

              <!-- Edit totals -->
              <div class="edit-totals">
                <div class="edit-totals-row">
                  <span>Subtotal</span>
                  <span>KES {{ editSubtotal.toLocaleString() }}</span>
                </div>
                <div class="edit-totals-row">
                  <div class="flex items-center gap-2">
                    <span>Discount</span>
                    <select v-model="editDiscountType" class="mini-select">
                      <option value="percent">%</option>
                      <option value="fixed">KES</option>
                    </select>
                    <input
                      v-model.number="editDiscount"
                      type="number"
                      min="0"
                      class="edit-input w-20"
                    />
                  </div>
                  <span class="text-emerald-600"
                    >- KES {{ editDiscountAmount.toLocaleString() }}</span
                  >
                </div>
                <div class="edit-totals-row bold">
                  <span>New Total</span>
                  <span>KES {{ editGrandTotal.toLocaleString() }}</span>
                </div>
              </div>

              <!-- Edit notes -->
              <div class="p-4 border-t border-[#eef5fb]">
                <label class="edit-label mb-1 block">Sale Notes</label>
                <textarea
                  v-model="editNotes"
                  rows="2"
                  placeholder="Internal notes…"
                  class="edit-textarea"
                ></textarea>
              </div>
            </div>

            <!-- Totals (view mode) -->
            <div v-if="!editing" class="totals-section">
              <div class="totals-row">
                <span>Subtotal</span>
                <span>KES {{ Number(sale.subtotal).toLocaleString() }}</span>
              </div>
              <div v-if="Number(sale.discount) > 0" class="totals-row">
                <span>Discount</span>
                <span class="text-emerald-600"
                  >- KES {{ Number(sale.discount).toLocaleString() }}</span
                >
              </div>
              <div v-if="Number(sale.tax) > 0" class="totals-row">
                <span>VAT (16%)</span>
                <span>KES {{ Number(sale.tax).toLocaleString() }}</span>
              </div>
              <div class="totals-row bold">
                <span>Total</span>
                <span>KES {{ Number(sale.total).toLocaleString() }}</span>
              </div>
              <div class="totals-row">
                <span>Paid</span>
                <span class="text-emerald-600">KES {{ totalPaid.toLocaleString() }}</span>
              </div>
              <div v-if="balance > 0" class="totals-row balance-due">
                <span>Balance Due</span>
                <span>KES {{ balance.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Payments -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">
                <svg
                  class="w-4 h-4 text-emerald-500"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                  />
                </svg>
                Payments
              </h2>
              <span class="card-badge">{{ payments.length }}</span>
            </div>

            <div v-if="payments.length === 0" class="empty-section">
              <span>No payments recorded yet.</span>
            </div>

            <div v-else class="payments-list">
              <div v-for="pmt in payments" :key="pmt.id" class="payment-row">
                <div class="payment-icon" :class="pmt.method">
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="pmt.method === 'cash'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                    />
                  </svg>
                </div>
                <div class="payment-info">
                  <div class="payment-method">{{ methodLabel(pmt.method) }}</div>
                  <div class="payment-meta">
                    {{ formatDateTime(pmt.created_at) }}
                    <span v-if="pmt.reference" class="payment-ref"
                      >· {{ pmt.reference }}</span
                    >
                    <span v-if="pmt.mpesa_code" class="payment-ref"
                      >· {{ pmt.mpesa_code }}</span
                    >
                  </div>
                  <div v-if="pmt.note" class="payment-note">{{ pmt.note }}</div>
                </div>
                <div class="payment-amount">
                  KES {{ Number(pmt.amount).toLocaleString() }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ══════════════════════
             RIGHT COLUMN
        ══════════════════════ -->
        <div class="right-col">
          <!-- Sale Info -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">
                <svg
                  class="w-4 h-4 text-slate-400"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Sale Info
              </h2>
            </div>
            <div class="info-grid">
              <div class="info-row">
                <span class="info-label">Reference</span>
                <span class="info-value mono">{{ sale.reference }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Date</span>
                <span class="info-value">{{ formatDateTime(sale.created_at) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value">{{ sale.branch?.name || "—" }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Cashier</span>
                <span class="info-value">{{ sale.cashier?.name || "—" }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="method-pill" :class="sale.payment_method">{{
                  methodLabel(sale.payment_method)
                }}</span>
              </div>
              <div v-if="sale.notes" class="info-row col-span">
                <span class="info-label">Notes</span>
                <span class="info-value">{{ sale.notes }}</span>
              </div>
            </div>
          </div>

          <!-- Customer -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">
                <svg
                  class="w-4 h-4 text-slate-400"
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
                Customer
              </h2>
            </div>
            <div v-if="sale.customer" class="customer-block">
              <div class="customer-big-avatar">
                {{ sale.customer.name?.charAt(0).toUpperCase() }}
              </div>
              <div class="customer-details">
                <div class="customer-name-big">{{ sale.customer.name }}</div>
                <div v-if="sale.customer.phone" class="customer-meta">
                  {{ sale.customer.phone }}
                </div>
                <div v-if="sale.customer.email" class="customer-meta">
                  {{ sale.customer.email }}
                </div>
                <div
                  v-if="sale.customer.outstanding_balance > 0"
                  class="customer-balance"
                >
                  Outstanding: KES
                  {{ Number(sale.customer.outstanding_balance).toLocaleString() }}
                </div>
              </div>
            </div>
            <div v-else class="empty-section">Walk-in Customer</div>
          </div>

          <!-- Receive Payment form — only when balance > 0 -->
          <div v-if="balance > 0" class="card payment-form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg
                  class="w-4 h-4 text-emerald-500"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                Receive Payment
              </h2>
              <span class="balance-badge">KES {{ balance.toLocaleString() }} due</span>
            </div>

            <div class="payment-form">
              <!-- Amount -->
              <div class="form-field">
                <label class="form-label">Amount (KES)</label>
                <input
                  v-model.number="newPayment.amount"
                  type="number"
                  :placeholder="balance"
                  min="1"
                  :max="balance"
                  class="form-input"
                />
                <button @click="newPayment.amount = balance" class="full-amount-btn">
                  Full amount
                </button>
              </div>

              <!-- Method -->
              <div class="form-field">
                <label class="form-label">Payment Method</label>
                <div class="method-pills">
                  <button
                    v-for="m in paymentMethods"
                    :key="m.value"
                    @click="newPayment.method = m.value"
                    class="method-pill-btn"
                    :class="{ active: newPayment.method === m.value }"
                  >
                    {{ m.label }}
                  </button>
                </div>
              </div>

              <!-- M-Pesa: open modal for STK/sync flow -->
              <div v-if="newPayment.method === 'mpesa'" class="mpesa-trigger-wrap">
                <!-- Show code if already confirmed via modal -->
                <div v-if="newPayment.mpesa_code" class="mpesa-confirmed-row">
                  <div class="mpesa-confirmed-info">
                    <svg
                      class="w-4 h-4 text-emerald-500"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    <div>
                      <div class="mpesa-confirmed-label">M-Pesa Confirmed</div>
                      <div class="mpesa-confirmed-code">{{ newPayment.mpesa_code }}</div>
                    </div>
                  </div>
                  <button @click="clearMpesa" class="mpesa-clear-btn">Change</button>
                </div>

                <!-- Button to open modal -->
                <button v-else @click="openMpesaModal" class="mpesa-open-btn">
                  <div class="mpesa-btn-logo">
                    <svg
                      class="w-4 h-4 text-white"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  <div class="text-left">
                    <div class="mpesa-btn-title">Pay via M-Pesa</div>
                    <div class="mpesa-btn-sub">STK Push · Sync · Manual code</div>
                  </div>
                  <svg
                    class="w-4 h-4 ml-auto text-emerald-600"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                </button>
              </div>

              <!-- Reference -->
              <div class="form-field">
                <label class="form-label">Reference / Note</label>
                <input
                  v-model="newPayment.note"
                  type="text"
                  placeholder="Optional note…"
                  class="form-input"
                />
              </div>

              <!-- Date -->
              <div class="form-field">
                <label class="form-label">Payment Date</label>
                <input
                  v-model="newPayment.date"
                  type="datetime-local"
                  class="form-input"
                />
              </div>

              <button
                @click="receivePayment"
                :disabled="
                  !newPayment.amount ||
                  newPayment.amount <= 0 ||
                  receivingPayment ||
                  (newPayment.method === 'mpesa' && !newPayment.mpesa_code)
                "
                class="receive-btn"
              >
                <svg
                  v-if="receivingPayment"
                  class="w-4 h-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"
                  />
                </svg>
                <svg
                  v-else
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                {{
                  receivingPayment
                    ? "Recording…"
                    : `Record KES ${(newPayment.amount || 0).toLocaleString()} Payment`
                }}
              </button>
            </div>
          </div>

          <!-- Fully paid banner -->
          <div v-else class="paid-banner">
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Fully Paid
          </div>
        </div>
      </div>
    </template>
  </div>

  <!-- M-Pesa Modal — used when mpesa is the selected payment method -->
  <MpesaModal
    :show="showMpesaModal"
    :grand-total="mpesaAmount"
    :order-ref="sale?.reference || ''"
    @close="showMpesaModal = false"
    @payment-confirmed="onMpesaConfirmed"
  />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import SalesService from "@/services/SaleService";
import MpesaModal from "./pos-components/MpesaModal.vue";
import { useAuthStore } from "@/stores/auth";
import { printReceipt as doPrintReceipt, resolveBusiness } from "@/utils/printReceipt";

const route = useRoute();
const router = useRouter();
const saleId = route.params.id;

/* ── State ──────────────────────────── */
const sale = ref(null);
const payments = ref([]);
const loading = ref(true);
const saving = ref(false);
const editing = ref(false);

// Edit copies
const editItems = ref([]);
const editDiscount = ref(0);
const editDiscountType = ref("percent");
const editNotes = ref("");

/* ── Payment form ────────────────────── */
const receivingPayment = ref(false);
const newPayment = ref({
  amount: 0,
  method: "cash",
  mpesa_code: "",
  mpesa_meta: null,
  note: "",
  date: new Date().toISOString().slice(0, 16),
});

/* ── M-Pesa modal ────────────────────── */
const showMpesaModal = ref(false);
const mpesaAmount = computed(() => newPayment.value.amount || balance.value);

const openMpesaModal = () => {
  // Default amount to full balance if not set
  if (!newPayment.value.amount) newPayment.value.amount = balance.value;
  showMpesaModal.value = true;
};

const onMpesaConfirmed = (meta) => {
  showMpesaModal.value = false;
  newPayment.value.mpesa_code = meta.mpesa_meta?.code || "";
  newPayment.value.mpesa_meta = meta.mpesa_meta;
  newPayment.value.amount = meta.amount || balance.value;
  // Auto-fill note with the M-Pesa code for reference
  if (!newPayment.value.note && meta.mpesa_meta?.code) {
    newPayment.value.note = `M-Pesa: ${meta.mpesa_meta.code}`;
  }
};

const clearMpesa = () => {
  newPayment.value.mpesa_code = "";
  newPayment.value.mpesa_meta = null;
  newPayment.value.note = "";
};

const paymentMethods = [
  { value: "cash", label: "Cash" },
  { value: "mpesa", label: "M-Pesa" },
  { value: "card", label: "Card" },
  { value: "credit", label: "Credit" },
];

/* ── Computed ────────────────────────── */
const saleItems = computed(() => sale.value?.items ?? []);
const totalPaid = computed(() =>
  (Array.isArray(payments.value) ? payments.value : []).reduce(
    (s, p) => s + Number(p.amount),
    0
  )
);
const balance = computed(() =>
  Math.max(0, Number(sale.value?.total ?? 0) - totalPaid.value)
);

const itemTotal = (item) =>
  Math.round(
    Number(item.unit_price) *
      Number(item.quantity) *
      (1 - (Number(item.discount) || 0) / 100)
  );

// Edit computed
const editSubtotal = computed(() =>
  editItems.value.reduce((s, i) => s + itemTotal(i), 0)
);
const editDiscountAmount = computed(() => {
  if (editDiscountType.value === "percent")
    return Math.round(editSubtotal.value * (editDiscount.value / 100));
  return Math.min(editDiscount.value, editSubtotal.value);
});
const editGrandTotal = computed(() => editSubtotal.value - editDiscountAmount.value);

/* ── Fetch ───────────────────────────── */
const fetchSale = async () => {
  loading.value = true;
  try {
    const saleRes = await SalesService.getById(saleId);
    const data = saleRes.data?.data ?? saleRes.data ?? saleRes;
    sale.value = data;
    // Payments are nested inside the sale response — no separate API call needed
    payments.value = Array.isArray(data.payments) ? data.payments : [];

    // Auto-trigger print if ?print=1
    if (route.query.print === "1") {
      setTimeout(printReceipt, 500);
    }
  } catch {
    Swal.fire({
      icon: "error",
      title: "Not Found",
      text: "Sale could not be loaded.",
      confirmButtonColor: "#229DE7",
    });
    router.push("/sales");
  } finally {
    loading.value = false;
  }
};

/* ── Edit ────────────────────────────── */
const startEdit = () => {
  editItems.value = JSON.parse(JSON.stringify(saleItems.value));
  editDiscount.value = Number(sale.value?.discount ?? 0);
  editDiscountType.value = "fixed";
  editNotes.value = sale.value?.notes ?? "";
};

const cancelEdit = () => {
  editing.value = false;
};

const removeEditItem = (i) => editItems.value.splice(i, 1);

const saveSale = async () => {
  if (saving.value) return;
  saving.value = true;
  try {
    const payload = {
      items: editItems.value.map((i) => ({
        id: i.id,
        product_id: i.product_id,
        quantity: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount || 0,
        note: i.note || "",
      })),
      discount: editDiscountAmount.value,
      total: editGrandTotal.value,
      notes: editNotes.value,
    };
    await SalesService.update(saleId, payload);
    await fetchSale();
    editing.value = false;
    Swal.fire({
      icon: "success",
      title: "Sale Updated",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Update Failed",
      text: err?.response?.data?.message || "Please try again.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    saving.value = false;
  }
};

/* ── Receive Payment ─────────────────── */
const receivePayment = async () => {
  if (!newPayment.value.amount || receivingPayment.value) return;
  receivingPayment.value = true;
  try {
    await SalesService.recordPayment(saleId, {
      amount: newPayment.value.amount,
      method: newPayment.value.method,
      mpesa_code: newPayment.value.mpesa_code || undefined,
      note: newPayment.value.note || undefined,
      paid_at: newPayment.value.date,
    });
    await fetchSale();
    newPayment.value = {
      amount: 0,
      method: "cash",
      mpesa_code: "",
      mpesa_meta: null,
      note: "",
      date: new Date().toISOString().slice(0, 16),
    };
    Swal.fire({
      icon: "success",
      title: "Payment Recorded",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Payment Failed",
      text: err?.response?.data?.message || "Please try again.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    receivingPayment.value = false;
  }
};

/* ── Print ───────────────────────────── */
const printReceipt = () => {
  if (!sale.value) return;
  const s = sale.value;
  const auth = useAuthStore();
  doPrintReceipt(
    {
      ref: s.reference,
      date: formatDateTime(s.created_at),
      branch: s.branch?.name,
      cashier: s.cashier?.name,
      customer: s.customer?.name,
      items: (s.items ?? []).map((i) => ({
        name: i.product?.name || i.product_name,
        qty: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount || 0,
        line_total: itemTotal(i),
      })),
      subtotal: s.subtotal,
      discount: s.discount,
      tax: s.tax,
      total: s.total,
      paid: totalPaid.value,
      balance: balance.value,
      payments: (s.payments ?? []).map((p) => ({ method: p.method, amount: p.amount })),
      qrUrl: s.etims_url || s.offline_url || null,
      kraSerial: s.kra_serial_number || null,
      etimsConfirmed: !!s.etims_url,
    },
    resolveBusiness(auth.user)
  );
};

/* ── Helpers ─────────────────────────── */
const formatDateTime = (dt) =>
  dt
    ? new Date(dt).toLocaleString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      })
    : "—";
const methodLabel = (m) =>
  ({ cash: "Cash", mpesa: "M-Pesa", card: "Card", credit: "Credit", split: "Split" }[m] ??
  m ??
  "—");

// Watch editing toggle to copy data
import { watch } from "vue";
watch(editing, (v) => {
  if (v) startEdit();
});

onMounted(fetchSale);
</script>

<style scoped>
.sale-root {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 24px;
  min-height: 100vh;
  background: #f0f6fb;
  font-family: "Segoe UI", system-ui, sans-serif;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 12px;
  border-radius: 9px;
  background: white;
  border: 1.5px solid #d9ebf7;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-decoration: none;
  transition: all 0.15s;
}
.back-btn:hover {
  border-color: #229de7;
  color: #229de7;
}
.header-divider {
  width: 1px;
  height: 24px;
  background: #d9ebf7;
}
.page-title {
  font-size: 20px;
  font-weight: 900;
  color: #0f172a;
  font-family: monospace;
}
.page-sub {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 2px;
}
.header-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  background: linear-gradient(135deg, #229de7, #1682c4);
  color: white;
  font-size: 13px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 4px 12px rgba(34, 157, 231, 0.3);
}
.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1682c4, #0f6fab);
}
.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  background: white;
  border: 1.5px solid #d9ebf7;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-outline:hover {
  border-color: #229de7;
  color: #229de7;
}
.btn-outline.danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

/* Status pill */
.status-pill {
  display: inline-flex;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-pill.paid {
  background: #ecfdf5;
  color: #059669;
}
.status-pill.partial {
  background: #fef9ec;
  color: #d97706;
}
.status-pill.unpaid {
  background: #fef2f2;
  color: #dc2626;
}
.status-pill.refunded {
  background: #f5f3ff;
  color: #7c3aed;
}

/* Loading */
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 60px;
  color: #94a3b8;
  font-weight: 600;
}
.loading-spinner {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 3px solid #d9ebf7;
  border-top-color: #229de7;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Layout */
.sale-body {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 20px;
  align-items: start;
}
.left-col {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.right-col {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Cards */
.card {
  background: white;
  border-radius: 16px;
  border: 1.5px solid #e8f0f8;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid #eef5fb;
}
.card-title {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 14px;
  font-weight: 800;
  color: #1e293b;
}
.card-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  background: #f1f5f9;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
}

/* Items */
.items-list {
  display: flex;
  flex-direction: column;
}
.item-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 18px;
  border-bottom: 1px solid #f8fafc;
}
.item-row:last-child {
  border-bottom: none;
}
.item-avatar {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  color: #229de7;
  font-weight: 900;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.item-info {
  flex: 1;
  min-width: 0;
}
.item-name {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}
.item-meta {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 2px;
}
.item-note {
  font-style: italic;
}
.item-total {
  font-size: 13px;
  font-weight: 800;
  color: #1e293b;
  flex-shrink: 0;
  white-space: nowrap;
}

/* Edit mode */
.edit-mode {
  flex-direction: column;
  align-items: stretch;
  gap: 8px;
}
.edit-item-controls {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 6px;
}
.edit-field-group {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.edit-label {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #94a3b8;
}
.edit-input {
  padding: 6px 8px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  outline: none;
}
.edit-input:focus {
  border-color: #229de7;
  background: white;
}
.edit-textarea {
  width: 100%;
  padding: 8px 10px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 8px;
  font-size: 12px;
  color: #1e293b;
  outline: none;
  resize: vertical;
}
.remove-item-btn {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #ef4444;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.remove-item-btn:hover {
  background: #ef4444;
  color: white;
}

.edit-totals {
  border-top: 1px solid #eef5fb;
  padding: 12px 18px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  background: #fafcff;
}
.edit-totals-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
}
.edit-totals-row.bold {
  font-size: 14px;
  font-weight: 900;
  color: #1e293b;
  border-top: 1px solid #eef5fb;
  padding-top: 8px;
  margin-top: 2px;
}
.mini-select {
  padding: 2px 5px;
  border: 1px solid #d9ebf7;
  border-radius: 5px;
  font-size: 11px;
  outline: none;
  background: white;
}

/* Totals */
.totals-section {
  border-top: 1px solid #eef5fb;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  background: #fafcff;
}
.totals-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
}
.totals-row.bold {
  font-size: 15px;
  font-weight: 900;
  color: #1e293b;
  border-top: 1px solid #eef5fb;
  padding-top: 8px;
  margin-top: 2px;
}
.totals-row.balance-due {
  font-size: 14px;
  font-weight: 800;
  color: #dc2626;
  background: #fef2f2;
  margin: 4px -18px -14px;
  padding: 10px 18px;
}

/* Payments */
.payments-list {
  display: flex;
  flex-direction: column;
}
.payment-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 18px;
  border-bottom: 1px solid #f8fafc;
}
.payment-row:last-child {
  border-bottom: none;
}
.payment-icon {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: #ecfdf5;
  color: #059669;
}
.payment-icon.mpesa {
  background: #dcfce7;
  color: #16a34a;
}
.payment-icon.card {
  background: #eff6ff;
  color: #2563eb;
}
.payment-icon.credit {
  background: #fef9ec;
  color: #d97706;
}
.payment-info {
  flex: 1;
  min-width: 0;
}
.payment-method {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
}
.payment-meta {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 2px;
}
.payment-ref {
  font-family: monospace;
  font-weight: 700;
  color: #64748b;
}
.payment-note {
  font-size: 10px;
  color: #94a3b8;
  font-style: italic;
  margin-top: 2px;
}
.payment-amount {
  font-size: 13px;
  font-weight: 800;
  color: #059669;
  flex-shrink: 0;
}

/* Info grid */
.info-grid {
  display: flex;
  flex-direction: column;
  padding: 14px 18px;
  gap: 10px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}
.info-label {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  flex-shrink: 0;
}
.info-value {
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  text-align: right;
}
.info-value.mono {
  font-family: monospace;
  color: #229de7;
}

/* Customer block */
.customer-block {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px 18px;
}
.customer-big-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #eaf5fd, #d9ebf7);
  color: #229de7;
  font-weight: 900;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.customer-name-big {
  font-size: 14px;
  font-weight: 800;
  color: #1e293b;
}
.customer-meta {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 2px;
}
.customer-balance {
  font-size: 11px;
  font-weight: 700;
  color: #ef4444;
  margin-top: 4px;
}

/* Payment form */
.payment-form-card {
  border-color: #86efac;
}
.payment-form {
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.balance-badge {
  display: inline-flex;
  padding: 3px 10px;
  border-radius: 20px;
  background: #fef2f2;
  color: #dc2626;
  font-size: 11px;
  font-weight: 700;
}
.form-field {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.form-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #94a3b8;
}
.form-input {
  padding: 9px 12px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
  width: 100%;
}
.form-input:focus {
  border-color: #00a859;
  background: white;
}
.full-amount-btn {
  align-self: flex-start;
  padding: 3px 9px;
  background: #ecfdf5;
  border: 1px solid #86efac;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  color: #059669;
  cursor: pointer;
  transition: all 0.15s;
}
.full-amount-btn:hover {
  background: #059669;
  color: white;
}
.method-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.method-pill-btn {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}
.method-pill-btn:hover {
  border-color: #229de7;
  color: #229de7;
}
.method-pill-btn.active {
  background: #229de7;
  border-color: #229de7;
  color: white;
}
.receive-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px;
  border-radius: 12px;
  background: linear-gradient(135deg, #00a859, #007a40);
  color: white;
  font-size: 13px;
  font-weight: 800;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 4px 14px rgba(0, 168, 89, 0.3);
}
.receive-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #007a40, #005c30);
}
.receive-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

/* Paid banner */
.paid-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 16px;
  border-radius: 14px;
  background: #ecfdf5;
  border: 1.5px solid #86efac;
  font-size: 14px;
  font-weight: 800;
  color: #059669;
}

/* Method pill (info grid) */
.method-pill {
  display: inline-flex;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
  background: #f1f5f9;
  color: #64748b;
}
.method-pill.cash {
  background: #ecfdf5;
  color: #059669;
}
.method-pill.mpesa {
  background: #dcfce7;
  color: #16a34a;
}
.method-pill.card {
  background: #eff6ff;
  color: #2563eb;
}
.method-pill.credit {
  background: #fef9ec;
  color: #d97706;
}

.empty-section {
  padding: 20px 18px;
  font-size: 12px;
  color: #94a3b8;
}

/* M-Pesa trigger */
.mpesa-trigger-wrap {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.mpesa-open-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1.5px solid #86efac;
  cursor: pointer;
  transition: all 0.15s;
  text-align: left;
  width: 100%;
}
.mpesa-open-btn:hover {
  border-color: #00a859;
  box-shadow: 0 4px 14px rgba(0, 168, 89, 0.15);
  transform: translateY(-1px);
}
.mpesa-btn-logo {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #00a859, #007a40);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.mpesa-btn-title {
  font-size: 13px;
  font-weight: 800;
  color: #065f46;
}
.mpesa-btn-sub {
  font-size: 10px;
  color: #6ee7b7;
  margin-top: 1px;
}

.mpesa-confirmed-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border-radius: 12px;
  background: #ecfdf5;
  border: 1.5px solid #6ee7b7;
  gap: 10px;
}
.mpesa-confirmed-info {
  display: flex;
  align-items: center;
  gap: 8px;
}
.mpesa-confirmed-label {
  font-size: 11px;
  font-weight: 700;
  color: #059669;
}
.mpesa-confirmed-code {
  font-size: 13px;
  font-weight: 900;
  color: #065f46;
  font-family: monospace;
}
.mpesa-clear-btn {
  padding: 4px 10px;
  border-radius: 7px;
  background: white;
  border: 1.5px solid #d9ebf7;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}
.mpesa-clear-btn:hover {
  border-color: #ef4444;
  color: #ef4444;
}

/* Responsive */
@media (max-width: 1024px) {
  .sale-body {
    grid-template-columns: 1fr 300px;
  }
}
@media (max-width: 768px) {
  .sale-root {
    padding: 14px;
    gap: 14px;
  }
  .sale-body {
    grid-template-columns: 1fr;
  }
  .right-col {
    order: -1;
  }
  .header-actions {
    width: 100%;
  }
  .edit-item-controls {
    flex-direction: column;
  }
}
</style>
