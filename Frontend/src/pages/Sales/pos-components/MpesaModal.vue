<template>
  <transition name="modal-fade">
    <div v-if="show" class="modal-overlay" @click.self="closeModal">
      <div class="mpesa-box">
        <!-- ── Header ── -->
        <div class="mpesa-header">
          <div class="flex items-center gap-3">
            <div class="mpesa-logo">
              <svg
                class="w-5 h-5 text-white"
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
            <div>
              <div class="text-base font-extrabold text-slate-900">M-Pesa Payment</div>
              <div class="flex items-center gap-3 mt-0.5">
                <span class="text-xs text-slate-400">
                  Amount:
                  <span class="font-bold text-[#229DE7]"
                    >KES {{ grandTotal.toLocaleString() }}</span
                  >
                </span>
                <span class="ref-badge">Ref: {{ orderRef }}</span>
              </div>
            </div>
          </div>
          <button @click="closeModal" class="modal-close">×</button>
        </div>

        <!-- ── Tabs ── -->
        <div class="mpesa-tabs">
          <button
            v-for="t in tabs"
            :key="t.key"
            @click="activeTab = t.key"
            class="mpesa-tab"
            :class="{ active: activeTab === t.key }"
          >
            {{ t.label }}
          </button>
        </div>

        <!-- ══════════════════════════════
             TAB 1 — STK PUSH
        ══════════════════════════════ -->
        <div v-if="activeTab === 'stk'" class="mpesa-body">
          <p class="tab-desc">
            Send a payment request to the customer's phone. Order reference
            <strong>{{ orderRef }}</strong> will be attached to the transaction.
          </p>

          <div class="field-group">
            <label class="field-label">Phone Number</label>
            <div class="phone-input-wrap">
              <span class="phone-prefix">+254</span>
              <input
                v-model="stkPhone"
                type="tel"
                placeholder="7XX XXX XXX"
                class="phone-input"
                maxlength="9"
                :disabled="stkLoading || stkStatus?.type === 'pending'"
                @input="stkPhone = stkPhone.replace(/\D/g, '')"
              />
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Amount (KES)</label>
            <input :value="grandTotal" type="number" class="pos-field" readonly />
          </div>

          <!-- STK status banner -->
          <transition name="fade">
            <div v-if="stkStatus" class="stk-status" :class="stkStatus.type">
              <svg
                v-if="stkStatus.type === 'pending'"
                class="w-4 h-4 flex-shrink-0 animate-spin"
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
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              <svg
                v-else-if="stkStatus.type === 'success'"
                class="w-4 h-4 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              <svg
                v-else
                class="w-4 h-4 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
              <div class="flex-1 min-w-0">
                <div>{{ stkStatus.message }}</div>
                <div v-if="checkoutRequestId" class="stk-ref-line">
                  Checkout ID:
                  <span class="font-mono font-bold">{{ checkoutRequestId }}</span>
                </div>
              </div>
            </div>
          </transition>

          <!-- Poll countdown bar -->
          <div v-if="stkStatus?.type === 'pending'" class="poll-bar-wrap">
            <div class="poll-bar-label">
              <span>Auto-checking in {{ pollCountdown }}s…</span>
              <button @click="pollNow" class="poll-now-btn">Check now</button>
            </div>
            <div class="poll-bar">
              <div class="poll-bar-fill" :style="{ width: pollProgress + '%' }"></div>
            </div>
          </div>

          <!-- Send button (hidden while pending) -->
          <button
            v-if="!stkStatus || stkStatus.type === 'error'"
            @click="sendSTK"
            :disabled="stkLoading || !stkPhone || stkPhone.length < 9"
            class="mpesa-action-btn green"
          >
            <svg
              v-if="stkLoading"
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
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
              />
            </svg>
            {{ stkLoading ? "Sending Request…" : "Send STK Push" }}
          </button>

          <!-- Pending actions: cancel or manually confirm -->
          <div v-if="stkStatus?.type === 'pending'" class="pending-actions">
            <button @click="cancelSTK" class="mpesa-action-btn outline-only blue">
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
              Cancel &amp; Retry
            </button>
            <button
              v-if="canConfirmManualPayment"
              @click="confirmSTKPayment"
              class="mpesa-action-btn green"
              title="Only use this if you've independently verified the payment landed — Safaricom hasn't confirmed it yet"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              Confirm Paid
            </button>
          </div>
        </div>

        <!-- ══════════════════════════════
             TAB 2 — SYNC / DIRECT
        ══════════════════════════════ -->
        <div v-if="activeTab === 'sync'" class="mpesa-body">
          <div class="sync-topbar">
            <p class="tab-desc" style="margin-bottom: 0">
              Fetch recent M-Pesa transactions and select the matching one.
            </p>
            <button @click="syncPayments" :disabled="syncing" class="sync-btn">
              <svg
                class="w-3.5 h-3.5"
                :class="{ 'animate-spin': syncing }"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
              {{ syncing ? "Syncing…" : "Sync" }}
            </button>
          </div>

          <!-- Reference info -->
          <div class="ref-info-bar">
            <svg
              class="w-3.5 h-3.5 flex-shrink-0"
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
            <span
              >Order ref <strong>{{ orderRef }}</strong> will be saved with the selected
              transaction.</span
            >
          </div>

          <!-- Transaction list -->
          <div class="txn-list">
            <div v-if="syncing && payments.length === 0" class="txn-empty">
              <svg
                class="w-5 h-5 animate-spin text-[#229DE7]"
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
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
            </div>

            <div v-else-if="!syncing && payments.length === 0" class="txn-empty">
              <svg
                class="w-8 h-8 opacity-20 mb-1"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                />
              </svg>
              <span class="text-xs text-slate-400 font-semibold"
                >No transactions yet. Click Sync to fetch.</span
              >
            </div>

            <button
              v-for="txn in payments"
              :key="txn.id"
              @click="selectTransaction(txn)"
              class="txn-row"
              :class="{
                selected: selectedTxn?.id === txn.id,
                matched: isAmountMatch(txn),
              }"
            >
              <div class="txn-avatar">
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
                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div class="txn-info">
                <div class="txn-name">{{ txn.sender_name || txn.phone }}</div>
                <div class="txn-meta">
                  {{ txn.phone }} · {{ txn.time }} ·
                  <span class="txn-code">{{ txn.mpesa_code }}</span>
                </div>
              </div>
              <div class="txn-right">
                <div class="txn-amount" :class="{ match: isAmountMatch(txn) }">
                  KES {{ Number(txn.amount).toLocaleString() }}
                </div>
                <div v-if="isAmountMatch(txn)" class="txn-match-badge">✓ Match</div>
              </div>
            </button>
          </div>

          <!-- Selected preview -->
          <transition name="fade">
            <div v-if="selectedTxn" class="selected-ref-preview">
              <svg
                class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              <span>
                <strong>{{ selectedTxn.mpesa_code }}</strong> selected — will be linked to
                order <strong>{{ orderRef }}</strong>
              </span>
            </div>
          </transition>

          <!-- Manual code (supervisor override only — the backend
               independently re-checks this permission) -->
          <div v-if="canConfirmManualPayment" class="manual-entry">
            <div class="manual-label">Or enter M-Pesa code manually</div>
            <div class="manual-row">
              <input
                v-model="manualCode"
                type="text"
                placeholder="e.g. RDE7XXXXXX"
                class="pos-field"
                style="text-transform: uppercase"
                @input="manualCode = manualCode.toUpperCase()"
              />
              <button
                @click="confirmManual"
                :disabled="!manualCode || manualCode.length < 6"
                class="mpesa-action-btn green compact"
              >
                Use Code
              </button>
            </div>
          </div>
        </div>

        <!-- ── Footer ── -->
        <div class="mpesa-footer">
          <button @click="closeModal" class="mpesa-cancel-btn">Cancel</button>
          <button
            @click="confirmPayment"
            :disabled="!canConfirm"
            class="mpesa-action-btn green"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Confirm KES {{ grandTotal.toLocaleString() }} Payment
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from "vue";
import MpesaService from "@/services/MpesaService";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
// The backend independently re-checks this on submit — this only avoids
// offering a control that would just come back rejected. A cashier
// without the override can still complete the sale via a real STK
// confirmation or a synced transaction; only the "trust me" paths are
// gated.
const canConfirmManualPayment = computed(() =>
  auth.hasPermission("sales.confirm_manual_payment")
);

const props = defineProps({
  show: Boolean,
  grandTotal: { type: Number, default: 0 },
  orderRef: { type: String, default: "" }, // POS order reference e.g. "ORD-123456"
});

const emit = defineEmits(["close", "payment-confirmed"]);

/* ─── Tabs ───────────────────────────── */
const tabs = [
  { key: "stk", label: "📲 STK Push" },
  { key: "sync", label: "🔄 Sync Payments" },
];
const activeTab = ref("stk");

/* ─── STK Push ───────────────────────── */
const stkPhone = ref("");
const stkAmount = ref("");
const stkLoading = ref(false);
const stkStatus = ref(null); // { type: 'pending'|'success'|'error', message }
const checkoutRequestId = ref(""); // Safaricom CheckoutRequestID from STK response
const confirmedPaymentMeta = ref(null);

// Polling
const POLL_EVERY = 10; // seconds between auto-checks
let pollTimer = null;
let countdownTimer = null;
const pollCountdown = ref(POLL_EVERY);
const pollProgress = ref(100);

const sendSTK = async () => {
  stkLoading.value = true;
  stkStatus.value = null;
  checkoutRequestId.value = "";

  try {
    const phone = "254" + stkPhone.value.replace(/^0/, "");
    const amount = stkAmount.value || props.grandTotal;

    const res = await MpesaService.initiateSTK({
      phone,
      amount,
      account_reference: props.orderRef,
      transaction_desc: `Payment for ${props.orderRef}`,
    });

    // Store CheckoutRequestID returned by Safaricom via your backend
    checkoutRequestId.value =
      res.data?.CheckoutRequestID || res.data?.checkout_request_id || "";

    stkStatus.value = {
      type: "pending",
      message: `STK push sent to +254${stkPhone.value}. Waiting for customer to enter PIN…`,
    };

    startPolling();
  } catch (err) {
    stkStatus.value = {
      type: "error",
      message:
        err?.response?.data?.message || "Failed to send STK push. Please try again.",
    };
  } finally {
    stkLoading.value = false;
  }
};

const startPolling = () => {
  stopPolling();
  pollCountdown.value = POLL_EVERY;
  pollProgress.value = 100;

  countdownTimer = setInterval(() => {
    pollCountdown.value--;
    pollProgress.value = (pollCountdown.value / POLL_EVERY) * 100;

    if (pollCountdown.value <= 0) {
      pollCountdown.value = POLL_EVERY;
      pollProgress.value = 100;
      checkSTKStatus();
    }
  }, 1000);
};

const stopPolling = () => {
  clearInterval(pollTimer);
  clearInterval(countdownTimer);
  pollTimer = countdownTimer = null;
};

const pollNow = () => {
  pollCountdown.value = POLL_EVERY;
  pollProgress.value = 100;
  checkSTKStatus();
};

const checkSTKStatus = async () => {
  if (!checkoutRequestId.value) return;
  try {
    const res = await MpesaService.checkSTKStatus({
      checkout_request_id: checkoutRequestId.value,
      // order_ref: props.orderRef,
    });

    const paid = res.data?.paid ?? res.data?.status === "completed";
    const failed = res.data?.failed ?? res.data?.status === "failed";

    if (paid) {
      stopPolling();
      const mpesaCode = res.data?.mpesa_code || res.data?.MpesaReceiptNumber || "";
      confirmedPaymentMeta.value = {
        method: "mpesa_stk",
        phone: "254" + stkPhone.value,
        amount: stkAmount.value || props.grandTotal,
        code: mpesaCode,
        checkout_request_id: checkoutRequestId.value,
        order_ref: props.orderRef,
      };
      stkStatus.value = {
        type: "success",
        message: `Payment confirmed!${mpesaCode ? " Code: " + mpesaCode : ""}`,
      };
    } else if (failed) {
      stopPolling();
      stkStatus.value = {
        type: "error",
        message: res.data?.message || "Payment was declined or timed out.",
      };
    }
    // still pending → keep polling
  } catch {
    // network hiccup — keep polling silently
  }
};

const cancelSTK = () => {
  stopPolling();
  stkStatus.value = null;
  checkoutRequestId.value = "";
  confirmedPaymentMeta.value = null;
};

// Cashier manually confirms (fallback when auto-polling can't verify)
const confirmSTKPayment = () => {
  stopPolling();
  confirmedPaymentMeta.value = {
    method: "mpesa_stk",
    phone: "254" + stkPhone.value,
    amount: stkAmount.value || props.grandTotal,
    code: "MANUAL-CONFIRM",
    checkout_request_id: checkoutRequestId.value,
    order_ref: props.orderRef,
  };
  stkStatus.value = {
    type: "success",
    message: "Payment manually confirmed by cashier.",
  };
};

/* ─── Sync / Direct ──────────────────── */
const payments = ref([]);
const syncing = ref(false);
const selectedTxn = ref(null);
const manualCode = ref("");

const isAmountMatch = (txn) => Number(txn.amount) === props.grandTotal;

const syncPayments = async () => {
  syncing.value = true;
  try {
    // Pass orderRef so backend can pre-filter or tag matching transactions
    const res = await MpesaService.getRecentPayments({ order_ref: props.orderRef });
    payments.value = res.data ?? [];
  } catch {
    payments.value = [];
  } finally {
    syncing.value = false;
  }
};

const selectTransaction = (txn) => {
  selectedTxn.value = txn;
  confirmedPaymentMeta.value = {
    method: "mpesa_sync",
    phone: txn.phone,
    amount: txn.amount,
    code: txn.mpesa_code,
    sender: txn.sender_name,
    order_ref: props.orderRef,
  };
};

const confirmManual = () => {
  confirmedPaymentMeta.value = {
    method: "mpesa_manual",
    phone: "",
    amount: props.grandTotal,
    code: manualCode.value,
    order_ref: props.orderRef,
  };
  selectedTxn.value = {
    id: "manual",
    mpesa_code: manualCode.value,
    amount: props.grandTotal,
  };
};

/* ─── Confirm & emit ─────────────────── */
const canConfirm = computed(() => {
  if (activeTab.value === "stk") return stkStatus.value?.type === "success";
  if (activeTab.value === "sync") return !!confirmedPaymentMeta.value;
  return false;
});

const confirmPayment = () => {
  if (!canConfirm.value) return;
  emit("payment-confirmed", {
    payment_method: "mpesa",
    mpesa_meta: confirmedPaymentMeta.value, // always contains order_ref + code
    amount: props.grandTotal,
  });
  resetModal();
};

/* ─── Close / Reset ──────────────────── */
const closeModal = () => {
  stopPolling();
  emit("close");
};

const resetModal = () => {
  stopPolling();
  stkPhone.value = "";
  stkAmount.value = "";
  stkStatus.value = null;
  stkLoading.value = false;
  checkoutRequestId.value = "";
  confirmedPaymentMeta.value = null;
  selectedTxn.value = null;
  manualCode.value = "";
  payments.value = [];
  pollCountdown.value = POLL_EVERY;
  pollProgress.value = 100;
};

watch(
  () => props.show,
  (v) => {
    if (v) {
      resetModal();
      activeTab.value = "stk";
    } else {
      stopPolling();
    }
  }
);

onBeforeUnmount(() => stopPolling());
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 300;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.mpesa-box {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  max-height: 88vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
}

/* Header */
.mpesa-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
}
.mpesa-logo {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #00a859, #007a40);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ref-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 20px;
  background: #eaf5fd;
  border: 1px solid #b3defa;
  font-size: 10px;
  font-weight: 700;
  color: #229de7;
  font-family: monospace;
  letter-spacing: 0.04em;
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

/* Tabs */
.mpesa-tabs {
  display: flex;
  border-bottom: 1px solid #eef5fb;
  flex-shrink: 0;
  background: #fafcff;
}
.mpesa-tab {
  flex: 1;
  padding: 10px 12px;
  font-size: 12px;
  font-weight: 700;
  color: #94a3b8;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.15s;
  background: none;
}
.mpesa-tab:hover {
  color: #229de7;
}
.mpesa-tab.active {
  color: #00a859;
  border-bottom-color: #00a859;
  background: white;
}

/* Body */
.mpesa-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-height: 0;
}
.tab-desc {
  font-size: 12px;
  color: #64748b;
  line-height: 1.6;
}

/* Fields */
.field-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.field-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #94a3b8;
}
.phone-input-wrap {
  display: flex;
  align-items: center;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  overflow: hidden;
  background: #f5faff;
  transition: border-color 0.15s;
}
.phone-input-wrap:focus-within {
  border-color: #00a859;
  background: white;
}
.phone-prefix {
  padding: 9px 10px 9px 14px;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  border-right: 1px solid #d9ebf7;
  background: #eef5fb;
  flex-shrink: 0;
}
.phone-input {
  flex: 1;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  border: none;
  outline: none;
  background: transparent;
}
.phone-input:disabled,
.pos-field:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.pos-field {
  width: 100%;
  padding: 9px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s;
}
.pos-field:focus {
  border-color: #00a859;
  background: white;
}

/* STK status */
.stk-status {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.5;
}
.stk-status.pending {
  background: #fef9ec;
  color: #92400e;
  border: 1px solid #fcd34d;
}
.stk-status.success {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #6ee7b7;
}
.stk-status.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}
.stk-ref-line {
  font-size: 10px;
  opacity: 0.7;
  margin-top: 3px;
}

/* Poll bar */
.poll-bar-wrap {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.poll-bar-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}
.poll-now-btn {
  font-size: 10px;
  font-weight: 700;
  color: #229de7;
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
}
.poll-bar {
  height: 4px;
  border-radius: 4px;
  background: #e2e8f0;
  overflow: hidden;
}
.poll-bar-fill {
  height: 100%;
  border-radius: 4px;
  background: linear-gradient(90deg, #229de7, #00a859);
  transition: width 1s linear;
}

/* Pending actions */
.pending-actions {
  display: flex;
  gap: 8px;
}

/* Ref info */
.ref-info-bar {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  padding: 9px 12px;
  border-radius: 9px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  font-size: 11px;
  color: #0369a1;
  line-height: 1.5;
}
.selected-ref-preview {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 9px 12px;
  border-radius: 9px;
  background: #ecfdf5;
  border: 1px solid #6ee7b7;
  font-size: 11px;
  color: #065f46;
}

/* Sync */
.sync-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.sync-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  background: #eaf5fd;
  border: 1.5px solid #b3defa;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  color: #229de7;
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}
.sync-btn:hover:not(:disabled) {
  background: #229de7;
  color: white;
}
.sync-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Txn list */
.txn-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  max-height: 220px;
  overflow-y: auto;
  border: 1.5px solid #eef5fb;
  border-radius: 12px;
  padding: 6px;
}
.txn-list::-webkit-scrollbar {
  width: 4px;
}
.txn-list::-webkit-scrollbar-thumb {
  background: #d9ebf7;
  border-radius: 4px;
}
.txn-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  gap: 6px;
  color: #94a3b8;
}
.txn-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1.5px solid transparent;
  cursor: pointer;
  transition: all 0.15s;
  text-align: left;
  background: #fafcff;
  width: 100%;
}
.txn-row:hover {
  background: #f0f9ff;
  border-color: #bae6fd;
}
.txn-row.selected {
  background: #ecfdf5;
  border-color: #6ee7b7;
}
.txn-row.matched {
  border-color: #86efac;
}
.txn-avatar {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.txn-info {
  flex: 1;
  min-width: 0;
}
.txn-name {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
}
.txn-meta {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 1px;
}
.txn-code {
  font-family: monospace;
  font-weight: 700;
  color: #64748b;
}
.txn-right {
  text-align: right;
  flex-shrink: 0;
}
.txn-amount {
  font-size: 13px;
  font-weight: 900;
  color: #1e293b;
}
.txn-amount.match {
  color: #16a34a;
}
.txn-match-badge {
  display: inline-block;
  margin-top: 2px;
  font-size: 9px;
  font-weight: 700;
  background: #dcfce7;
  color: #166534;
  padding: 1px 6px;
  border-radius: 20px;
}

/* Manual */
.manual-entry {
  border-top: 1px solid #eef5fb;
  padding-top: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.manual-label {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
}
.manual-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

/* Footer */
.mpesa-footer {
  display: flex;
  gap: 8px;
  padding: 14px 20px;
  border-top: 1px solid #eef5fb;
  flex-shrink: 0;
  background: #fafcff;
}
.mpesa-cancel-btn {
  padding: 10px 20px;
  border-radius: 10px;
  background: white;
  border: 1.5px solid #d9ebf7;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}
.mpesa-cancel-btn:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

/* Action btns */
.mpesa-action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 11px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s;
  border: 1.5px solid transparent;
}
.mpesa-action-btn.green {
  background: linear-gradient(135deg, #00a859, #007a40);
  color: white;
  border-color: #00a859;
  box-shadow: 0 4px 14px rgba(0, 168, 89, 0.3);
}
.mpesa-action-btn.green:hover:not(:disabled) {
  background: linear-gradient(135deg, #007a40, #005c30);
}
.mpesa-action-btn.green:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}
.mpesa-action-btn.blue {
  background: #eaf5fd;
  color: #229de7;
  border-color: #b3defa;
}
.mpesa-action-btn.blue:hover {
  background: #229de7;
  color: white;
}
.mpesa-action-btn.outline-only {
  background: white;
  color: #229de7;
  border-color: #b3defa;
}
.mpesa-action-btn.compact {
  flex: none;
  padding: 9px 16px;
  font-size: 12px;
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
