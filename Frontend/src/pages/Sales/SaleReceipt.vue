<template>
  <div class="receipt-root">
    <!-- Loading -->
    <div v-if="loading" class="loading-wrap">
      <div class="spinner"></div>
      <p>Loading receipt…</p>
    </div>

    <template v-else-if="sale">
      <!-- Screen controls (hidden on print) -->
      <div class="controls no-print">
        <button @click="$router.back()" class="btn-back">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Back
        </button>
        <div class="ctrl-right">
          <button @click="handleEmail" :disabled="sending" class="btn-email">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            {{ sending ? "Sending…" : "Email Receipt" }}
          </button>
          <button @click="handlePrint" class="btn-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Receipt
          </button>
        </div>
      </div>

      <!-- Receipt card -->
      <div class="receipt-card">
        <!-- Header -->
        <div class="receipt-header">
          <div v-if="business.logo" class="logo-wrap">
            <img :src="business.logo" alt="logo" class="logo-img" />
          </div>
          <div v-else class="logo-initials">{{ businessInitials }}</div>
          <div class="biz-name">{{ business.name }}</div>
          <div v-if="business.address" class="biz-meta">{{ business.address }}</div>
          <div v-if="business.phone || business.email" class="biz-meta">
            {{ [business.phone, business.email].filter(Boolean).join(' · ') }}
          </div>
        </div>

        <div class="divider-dashed"></div>

        <!-- Reference & date -->
        <div class="receipt-meta">
          <div class="meta-row">
            <span class="meta-label">Receipt #</span>
            <span class="meta-value bold">{{ sale.reference }}</span>
          </div>
          <div class="meta-row">
            <span class="meta-label">Date</span>
            <span class="meta-value">{{ formatDateTime(sale.created_at) }}</span>
          </div>
          <div v-if="sale.branch" class="meta-row">
            <span class="meta-label">Branch</span>
            <span class="meta-value">{{ sale.branch.name }}</span>
          </div>
          <div v-if="sale.cashier" class="meta-row">
            <span class="meta-label">Cashier</span>
            <span class="meta-value">{{ sale.cashier.name }}</span>
          </div>
          <div v-if="sale.customer" class="meta-row">
            <span class="meta-label">Customer</span>
            <span class="meta-value">{{ sale.customer.name }}</span>
          </div>
        </div>

        <div class="divider-dashed"></div>

        <!-- Items -->
        <div class="items-section">
          <div class="items-header">
            <span>Item</span>
            <span>Amt</span>
          </div>
          <div v-for="item in sale.items" :key="item.id" class="item-block">
            <div class="item-name">{{ item.product?.name || item.product_name }}</div>
            <div class="item-row">
              <span>{{ item.quantity }} × {{ fmtNum(item.unit_price) }}</span>
              <span>{{ fmtNum(itemTotal(item)) }}</span>
            </div>
            <div v-if="item.discount > 0" class="item-disc">Discount: {{ item.discount }}%</div>
          </div>
        </div>

        <div class="divider-solid"></div>

        <!-- Totals -->
        <div class="totals-section">
          <div class="total-row">
            <span>Subtotal</span>
            <span>{{ fmtNum(sale.subtotal) }}</span>
          </div>
          <div v-if="Number(sale.discount) > 0" class="total-row discount">
            <span>Discount</span>
            <span>-{{ fmtNum(sale.discount) }}</span>
          </div>
          <div v-if="Number(sale.tax) > 0" class="total-row tax">
            <span>VAT</span>
            <span>{{ fmtNum(sale.tax) }}</span>
          </div>
          <div class="total-row grand">
            <span>TOTAL</span>
            <span>{{ fmtNum(sale.total) }}</span>
          </div>
          <div class="total-row paid">
            <span>Paid</span>
            <span>{{ fmtNum(totalPaid) }}</span>
          </div>
          <div v-if="balance > 0" class="total-row balance">
            <span>Balance Due</span>
            <span>{{ fmtNum(balance) }}</span>
          </div>
          <div v-else class="total-row change">
            <span>Change</span>
            <span>{{ fmtNum(Math.abs(balance)) }}</span>
          </div>
        </div>

        <!-- Payments breakdown -->
        <template v-if="sale.payments?.length">
          <div class="divider-dashed"></div>
          <div class="pay-section">
            <div class="pay-title">Payment Methods</div>
            <div v-for="p in sale.payments" :key="p.id" class="total-row">
              <span>{{ capitalize(p.method) }}</span>
              <span>{{ fmtNum(p.amount) }}</span>
            </div>
          </div>
        </template>

        <!-- eTIMS Tax Compliance QR -->
        <template v-if="qrTargetUrl">
          <div class="divider-dashed"></div>
          <div class="etims-section">
            <img v-if="qrDataUrl" :src="qrDataUrl" alt="eTIMS QR Code" class="etims-qr" />
            <div class="etims-label">KRA eTIMS Compliant Invoice</div>
            <div v-if="sale.kra_serial_number" class="etims-meta">
              CU Serial: {{ sale.kra_serial_number }}
            </div>
            <div class="etims-hint">
              {{ sale.etims_url ? "Scan to verify this invoice on KRA eTIMS" : "Scan to view receipt · eTIMS verification pending" }}
            </div>
          </div>
        </template>

        <div class="divider-dashed"></div>

        <!-- Footer -->
        <div class="receipt-footer">
          <div class="thank-you">Thank You!</div>
          <div class="powered">Powered by Bizaa</div>
        </div>
      </div>
    </template>

    <div v-else class="error-state no-print">
      <p>Receipt not found.</p>
      <button @click="$router.back()" class="btn-back">Go back</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import QRCode from "qrcode";
import Swal from "sweetalert2";
import SalesService from "@/services/SaleService";
import { useAuthStore } from "@/stores/auth";
import { resolveBusiness } from "@/utils/printReceipt.js";

const route = useRoute();
const auth = useAuthStore();
const saleId = route.params.id;

/* ── Business ─────────────────────────── */
const business = resolveBusiness(auth.user);

const businessInitials = computed(() =>
  (business.name || "B").substring(0, 2).toUpperCase()
);

/* ── State ─────────────────────────────── */
const sale = ref(null);
const loading = ref(true);
const qrDataUrl = ref(null);

/* ── Computed ──────────────────────────── */
const totalPaid = computed(() =>
  (sale.value?.payments ?? []).reduce((s, p) => s + Number(p.amount ?? 0), 0)
);

const balance = computed(() => Number(sale.value?.total ?? 0) - totalPaid.value);

/* ── DigiTax / eTIMS QR ──────────────────
   etims_url only populates once KRA finishes processing (can take a
   while); offline_url is available immediately, so the QR shows that
   first and upgrades once etims_url arrives. */
const qrTargetUrl = computed(() => sale.value?.etims_url || sale.value?.offline_url || null);

/* ── Helpers ─────────────────────────── */
const fmtNum = (val) =>
  "KES " + Number(val ?? 0).toLocaleString("en-KE", { minimumFractionDigits: 2 });

const formatDateTime = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleString("en-KE", {
    day: "2-digit", month: "short", year: "numeric",
    hour: "2-digit", minute: "2-digit",
  });
};

const capitalize = (str) =>
  str ? str.charAt(0).toUpperCase() + str.slice(1) : "—";

const itemTotal = (item) => {
  const base = Number(item.unit_price) * Number(item.quantity);
  const disc = item.discount ? base * (Number(item.discount) / 100) : 0;
  return base - disc;
};

/* ── Print ──────────────────────────── */
const handlePrint = () => window.print();

/* ── Email ──────────────────────────── */
const sending = ref(false);

const handleEmail = async () => {
  const { value: email, isConfirmed } = await Swal.fire({
    icon: "question",
    title: "Email this receipt",
    input: "email",
    inputValue: sale.value?.customer?.email || "",
    inputPlaceholder: "customer@example.com",
    confirmButtonText: "Send",
    confirmButtonColor: "#229DE7",
    showCancelButton: true,
    inputValidator: (value) => (!value ? "Enter an email address to send to." : undefined),
  });

  if (!isConfirmed || !email) return;

  sending.value = true;
  try {
    const res = await SalesService.emailReceipt(sale.value.id, email);
    Swal.fire({
      icon: "success",
      title: "Receipt sent",
      text: res?.data?.message || `Receipt is being sent to ${email}.`,
      confirmButtonColor: "#229DE7",
    });
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: "error",
      title: "Could not send receipt",
      text: err?.response?.data?.message || "Something went wrong. Please try again.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    sending.value = false;
  }
};

/* ── Fetch ──────────────────────────── */
onMounted(async () => {
  try {
    const res = await SalesService.getById(saleId);
    sale.value = res?.data?.data ?? res?.data ?? null;

    if (qrTargetUrl.value) {
      qrDataUrl.value = await QRCode.toDataURL(qrTargetUrl.value, {
        width: 140,
        margin: 1,
      });
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.receipt-root {
  min-height: 100vh;
  background: #f0f6fb;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px 16px 48px;
  font-family: 'Courier New', Courier, monospace;
}

/* Controls */
.controls {
  width: 100%;
  max-width: 380px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #229DE7;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}
.ctrl-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-print {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  background: #229DE7;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-print:hover { background: #1682C4; }
.btn-email {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #229DE7;
  background: #fff;
  border: 1.5px solid #229DE7;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-email:hover { background: #eaf5fd; }
.btn-email:disabled { opacity: 0.6; cursor: not-allowed; }

/* Receipt card */
.receipt-card {
  width: 100%;
  max-width: 380px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(34, 157, 231, 0.12);
  padding: 24px 20px;
  font-size: 12px;
  color: #1e293b;
}

/* Header */
.receipt-header {
  text-align: center;
  margin-bottom: 12px;
}
.logo-wrap {
  width: 64px;
  height: 64px;
  margin: 0 auto 8px;
  border-radius: 10px;
  overflow: hidden;
  border: 1.5px solid #e2e8f0;
}
.logo-img { width: 100%; height: 100%; object-fit: contain; }
.logo-initials {
  width: 64px;
  height: 64px;
  margin: 0 auto 8px;
  border-radius: 10px;
  background: #eaf5fd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 900;
  color: #229DE7;
  letter-spacing: 2px;
}
.biz-name {
  font-size: 18px;
  font-weight: 900;
  letter-spacing: 2px;
  text-transform: uppercase;
}
.biz-meta {
  font-size: 10px;
  color: #64748b;
  margin-top: 2px;
}

/* Dividers */
.divider-dashed {
  border-top: 1.5px dashed #cbd5e1;
  margin: 10px 0;
}
.divider-solid {
  border-top: 2px solid #1e293b;
  margin: 10px 0;
}

/* Meta */
.receipt-meta { margin: 8px 0; }
.meta-row {
  display: flex;
  justify-content: space-between;
  padding: 2px 0;
  font-size: 11px;
}
.meta-label { color: #64748b; }
.meta-value { font-weight: 600; }
.meta-value.bold { font-weight: 800; }

/* Items */
.items-section { margin: 4px 0; }
.items-header {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #94a3b8;
  margin-bottom: 6px;
}
.item-block { margin-bottom: 8px; }
.item-name { font-weight: 700; font-size: 12px; }
.item-row {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #475569;
}
.item-disc { font-size: 10px; color: #16a34a; }

/* Totals */
.totals-section { margin: 4px 0; }
.total-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  padding: 3px 0;
}
.total-row.discount { color: #dc2626; }
.total-row.tax { color: #d97706; }
.total-row.grand {
  font-weight: 900;
  font-size: 15px;
  padding: 6px 0 4px;
  border-top: 1.5px solid #1e293b;
}
.total-row.paid { color: #15803d; font-weight: 700; }
.total-row.balance { color: #dc2626; font-weight: 700; }
.total-row.change { color: #15803d; font-weight: 700; }

/* Payments */
.pay-section { margin: 4px 0; }
.pay-title {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #94a3b8;
  margin-bottom: 4px;
}

/* eTIMS */
.etims-section {
  text-align: center;
  margin: 6px 0;
}
.etims-qr {
  width: 100px;
  height: 100px;
  margin: 0 auto 6px;
  display: block;
  image-rendering: pixelated;
}
.etims-label {
  font-size: 10.5px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: #1e293b;
}
.etims-meta {
  font-size: 10px;
  color: #475569;
  font-family: 'Courier New', Courier, monospace;
}
.etims-hint {
  font-size: 9px;
  color: #94a3b8;
  margin-top: 2px;
}

/* Footer */
.receipt-footer { text-align: center; margin-top: 4px; }
.thank-you { font-size: 14px; font-weight: 900; letter-spacing: 2px; }
.powered { font-size: 9px; color: #94a3b8; margin-top: 4px; }

/* Misc */
.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 80px 0;
  color: #64748b;
}
.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #d9ebf7;
  border-top-color: #229DE7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-state {
  text-align: center;
  padding: 60px 0;
  color: #64748b;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

/* Print styles */
@media print {
  .no-print { display: none !important; }
  .receipt-root {
    background: #fff;
    padding: 0;
  }
  .receipt-card {
    box-shadow: none;
    border-radius: 0;
    max-width: 100%;
    padding: 8px;
  }
  @page { size: 80mm auto; margin: 4mm; }
}
</style>
