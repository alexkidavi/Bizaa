<template>
  <div class="min-h-screen bg-slate-50 p-4 md:p-8">

    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-extrabold text-slate-900">Subscription</h1>
      <p class="text-sm text-slate-500 mt-1">Manage your Bizaa plan and billing</p>
    </div>

    <!-- CURRENT PLAN CARD -->
    <div v-if="!loadingCurrent" class="mb-8">

      <!-- Expired banner -->
      <div v-if="current && !current.is_active"
        class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 rounded-2xl p-4">
        <ExclamationCircleIcon class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" />
        <div>
          <p class="font-bold text-red-700">Subscription Expired</p>
          <p class="text-sm text-red-600 mt-0.5">
            Your {{ current.plan?.name ?? 'plan' }} expired on {{ fmtDate(current.ends_at) }}.
            Choose a plan below to continue using Bizaa.
          </p>
        </div>
      </div>

      <!-- Trial warning -->
      <div v-else-if="current?.status === 'trial' && current.days_remaining <= 5"
        class="mb-4 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <ExclamationTriangleIcon class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" />
        <div>
          <p class="font-bold text-amber-700">Trial ending soon</p>
          <p class="text-sm text-amber-600 mt-0.5">
            Your free trial expires in
            <strong>{{ current.days_remaining }} day{{ current.days_remaining === 1 ? '' : 's' }}</strong>.
            Subscribe now to avoid interruption.
          </p>
        </div>
      </div>

      <!-- Active plan card -->
      <div v-if="current" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div :class="[
              'w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0',
              current.is_active ? 'bg-blue-50' : 'bg-red-50'
            ]">
              <CheckBadgeIcon v-if="current.is_active" class="w-8 h-8 text-[#229DE7]" />
              <XCircleIcon v-else class="w-8 h-8 text-red-400" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-lg font-extrabold text-slate-900">{{ current.plan?.name ?? 'Unknown Plan' }}</h2>
                <span :class="statusBadgeClass(current.status)">{{ statusLabel(current.status) }}</span>
              </div>
              <p class="text-sm text-slate-500 mt-0.5">
                <span v-if="current.is_active">
                  {{ current.days_remaining }} day{{ current.days_remaining === 1 ? '' : 's' }} remaining
                  &bull; Expires {{ fmtDate(current.ends_at) }}
                </span>
                <span v-else>Expired on {{ fmtDate(current.ends_at) }}</span>
              </p>
            </div>
          </div>

          <!-- Days ring -->
          <div v-if="current.is_active" class="flex-shrink-0">
            <div class="relative w-20 h-20">
              <svg class="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="34" fill="none" stroke="#EFF6FF" stroke-width="8" />
                <circle cx="40" cy="40" r="34" fill="none" stroke="#229DE7" stroke-width="8" stroke-linecap="round"
                  :stroke-dasharray="`${daysPercent * 213.6} 213.6`" style="transition: stroke-dasharray 1s ease" />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-xl font-extrabold text-slate-900 leading-none">{{ current.days_remaining }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">days</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Features of current plan -->
        <div v-if="current.plan?.features?.length" class="mt-5 pt-5 border-t border-slate-100">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Included in your plan</p>
          <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
            <li v-for="f in current.plan.features" :key="f" class="flex items-center gap-2 text-sm text-slate-600">
              <CheckIcon class="w-4 h-4 text-emerald-500 flex-shrink-0" />
              {{ f }}
            </li>
          </ul>
        </div>
      </div>

      <!-- No subscription yet -->
      <div v-else class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center">
        <SparklesIcon class="w-12 h-12 text-slate-300 mx-auto mb-3" />
        <p class="font-bold text-slate-600">No active subscription</p>
        <p class="text-sm text-slate-400 mt-1">Choose a plan below to get started</p>
      </div>
    </div>
    <div v-else class="mb-8 bg-white rounded-2xl border border-slate-200 h-40 animate-pulse" />

    <!-- PLANS -->
    <div class="mb-10">
      <h2 class="text-lg font-extrabold text-slate-800 mb-4">Choose a Plan</h2>

      <div v-if="loadingPlans" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-slate-200 h-64 animate-pulse" />
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-for="plan in plans" :key="plan.id" :class="[
          'relative bg-white rounded-2xl border-2 p-6 flex flex-col transition-all duration-200 hover:shadow-md',
          plan.slug === 'quarterly' ? 'border-[#229DE7] shadow-md' : 'border-slate-200',
        ]">
          <!-- Popular badge -->
          <div v-if="plan.slug === 'quarterly'"
            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#229DE7] text-white text-[10px] font-extrabold uppercase tracking-widest px-3 py-1 rounded-full whitespace-nowrap">
            Most Popular
          </div>

          <!-- Plan name & price -->
          <div class="mb-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">{{
              periodLabel(plan.billing_period) }}</p>
            <h3 class="text-xl font-extrabold text-slate-900">{{ plan.name }}</h3>
            <div class="flex items-end gap-1 mt-2">
              <span class="text-3xl font-extrabold text-slate-900">KES {{ fmtPrice(plan.price) }}</span>
              <span class="text-sm text-slate-400 mb-1">/ {{ plan.billing_period === 'annually' ? 'year' :
                plan.billing_period === 'quarterly' ? 'quarter' : 'month' }}</span>
            </div>
            <p v-if="plan.slug === 'quarterly'" class="text-xs text-emerald-600 font-semibold mt-1">Save KES 100 vs
              monthly</p>
            <p v-if="plan.slug === 'annually'" class="text-xs text-emerald-600 font-semibold mt-1">Save KES 1,000 vs
              monthly</p>
          </div>

          <!-- Features -->
          <ul class="space-y-1.5 flex-1 mb-5">
            <li v-for="f in plan.features" :key="f" class="flex items-center gap-2 text-sm text-slate-600">
              <CheckIcon class="w-4 h-4 text-emerald-500 flex-shrink-0" />
              {{ f }}
            </li>
          </ul>

          <!-- M-Pesa badge + Subscribe button -->
          <div class="space-y-2">
            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400">
              <DevicePhoneMobileIcon class="w-3.5 h-3.5" />
              <span>Pay via M-Pesa</span>
            </div>
            <button @click="openModal(plan)" :class="[
              'w-full py-2.5 rounded-xl font-bold text-sm transition-all',
              plan.slug === 'quarterly'
                ? 'bg-[#229DE7] text-white hover:bg-[#1b85c8]'
                : 'bg-slate-900 text-white hover:bg-slate-700',
            ]">
              Subscribe — KES {{ fmtPrice(plan.price) }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PAYMENT HISTORY -->
    <div>
      <h2 class="text-lg font-extrabold text-slate-800 mb-4">Billing History</h2>

      <div v-if="loadingHistory" class="bg-white rounded-2xl border border-slate-200 h-32 animate-pulse" />

      <div v-else-if="history.length === 0" class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
        <p class="text-sm text-slate-400">No billing history yet.</p>
      </div>

      <div v-else class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="text-left px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Plan</th>
              <th class="text-left px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Period</th>
              <th class="text-left px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Amount</th>
              <th class="text-left px-5 py-3 text-xs font-bold text-slate-400 uppercase tracking-wide">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in history" :key="item.id">
              <td class="px-5 py-3 font-semibold text-slate-700">{{ item.plan_name }}</td>
              <td class="px-5 py-3 text-slate-500">{{ fmtDate(item.starts_at) }} – {{ fmtDate(item.ends_at) }}</td>
              <td class="px-5 py-3 text-slate-700 font-semibold">
                {{ item.amount_paid > 0 ? `KES ${fmtPrice(item.amount_paid)}` : 'Free' }}
              </td>
              <td class="px-5 py-3">
                <span :class="statusBadgeClass(item.status)">{{ statusLabel(item.status) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         M-PESA MODAL
    ══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="modal.open"
        class="fixed inset-0 z-50 flex items-start sm:items-center justify-center overflow-y-auto p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
        <div
          class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[calc(100dvh-1.5rem)] sm:max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden my-auto"
          @click.stop>

          <!-- STEP 1: Offline payment instructions and confirmation -->
          <template v-if="modal.step === 'input'">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-extrabold text-slate-900">Pay via M-Pesa</h3>
                <p class="text-sm text-slate-400 mt-0.5">{{ modal.plan?.name }} — KES {{ fmtPrice(modal.plan?.price ??
                  0) }}</p>
              </div>
              <button @click="closeModal" aria-label="Close payment modal"
                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                <XMarkIcon class="w-4 h-4 text-slate-500" />
              </button>
            </div>

            <div class="p-6 space-y-5 overflow-y-auto min-h-0 overscroll-contain">
              <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <p class="text-xs text-green-700 font-bold uppercase tracking-wide mb-1">Manual M-Pesa payment</p>
                <p class="text-2xl font-extrabold text-green-800 mb-3">KES {{ fmtPrice(modal.plan?.price ?? 0) }}</p>
                <div class="space-y-2 text-sm text-green-900">
                  <div class="flex items-center justify-between gap-3"><span class="text-green-700">Paybill
                      number</span><strong>522533</strong></div>
                  <div class="flex items-center justify-between gap-3"><span class="text-green-700">Account
                      number</span><strong>7992114</strong></div>
                </div>
              </div>

              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-bold text-slate-800 mb-2">How to pay</p>
                <ol class="list-decimal pl-5 space-y-1.5 text-sm text-slate-600">
                  <li>Open M-Pesa and select <strong>Lipa na M-Pesa</strong>, then <strong>Paybill</strong>.</li>
                  <li>Enter Paybill <strong>522533</strong> and account number <strong>7992114</strong>.</li>
                  <li>Pay exactly <strong>KES {{ fmtPrice(modal.plan?.price ?? 0) }}</strong>.</li>
                  <li>Copy and paste the complete confirmation SMS below after payment.</li>
                </ol>
              </div>

              <div>
                <label for="mpesa-confirmation"
                  class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">M-Pesa confirmation
                  message</label>
                <textarea id="mpesa-confirmation" v-model="form.confirmationMessage" rows="4" maxlength="1500"
                  placeholder="Paste the full M-Pesa confirmation SMS here, including the transaction code, amount and recipient details."
                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#229DE7]/30 focus:border-[#229DE7] resize-y" />
                <p class="text-xs text-slate-400 mt-1.5">Paste the complete message exactly as received. Never share
                  your M-Pesa PIN.</p>
              </div>

              <div class="flex items-start gap-2 rounded-xl bg-amber-50 border border-amber-200 p-3">
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-amber-800">Your subscription will not activate immediately. We’ll verify the
                  payment first and activate your plan once the transaction is confirmed.</p>
              </div>

              <p v-if="modal.error" class="text-sm text-red-600 font-medium bg-red-50 rounded-xl px-4 py-3">{{
                modal.error }}</p>
            </div>

            <div class="px-6 pb-6 flex gap-3">
              <button @click="closeModal"
                class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
              <button @click="submitOfflinePayment" :disabled="modal.loading"
                class="flex-1 py-3 rounded-xl bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition disabled:opacity-60 flex items-center justify-center gap-2">
                <svg v-if="modal.loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="40 60" />
                </svg>
                {{ modal.loading ? 'Submitting...' : 'Submit for Verification' }}
              </button>
            </div>
          </template>

          <!-- ── STEP 3: Success ── -->
          <template v-if="modal.step === 'success'">
            <div class="p-8 text-center">
              <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-5">
                <CheckCircleIcon class="w-12 h-12 text-emerald-500" />
              </div>
              <h3 class="text-xl font-extrabold text-slate-900 mb-2">Payment Submitted</h3>
              <p class="text-sm text-slate-500 mb-1">
                Your payment confirmation for <strong>{{ modal.plan?.name }}</strong> has been submitted.
              </p>
              <p class="text-sm text-slate-400 mb-8">We will verify the transaction before activating your subscription.
              </p>
              <button @click="closeModal"
                class="w-full py-3 rounded-xl bg-[#229DE7] text-white font-bold text-sm hover:bg-[#1b85c8] transition">
                Continue to Bizaa
              </button>
            </div>
          </template>

          <!-- ── STEP 4: Failed ── -->
          <template v-if="modal.step === 'failed'">
            <div class="p-8 text-center">
              <div class="w-20 h-20 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
                <XCircleIcon class="w-12 h-12 text-red-400" />
              </div>
              <h3 class="text-xl font-extrabold text-slate-900 mb-2">Payment Failed</h3>
              <p class="text-sm text-slate-500 mb-8">{{ modal.failureReason }}</p>
              <div class="flex gap-3">
                <button @click="closeModal"
                  class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                  Close
                </button>
                <button @click="retryPayment"
                  class="flex-1 py-3 rounded-xl bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition">
                  Try Again
                </button>
              </div>
            </div>
          </template>

        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
  CheckBadgeIcon,
  CheckIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  SparklesIcon,
  DevicePhoneMobileIcon,
} from "@heroicons/vue/24/outline";
import SubscriptionService from "@/services/SubscriptionService";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();

// ── State ───────────────────────────────────────────────────────────────────
const current = ref(null);
const plans = ref([]);
const history = ref([]);
const loadingCurrent = ref(true);
const loadingPlans = ref(true);
const loadingHistory = ref(true);

const modal = ref({
  open: false,
  plan: null,
  step: "input",        // input | success | failed
  loading: false,
  error: null,
  failureReason: "",
});
const form = ref({ confirmationMessage: "" });

// ── Computed ────────────────────────────────────────────────────────────────
const daysPercent = computed(() => {
  if (!current.value?.is_active) return 0;
  const total = current.value.plan?.slug === "trial" ? 14 : 30;
  return Math.min(1, current.value.days_remaining / total);
});

// ── Loaders ─────────────────────────────────────────────────────────────────
async function loadCurrent() {
  try {
    const res = await SubscriptionService.getCurrent();
    current.value = res.data?.data ?? null;
    auth.setSubscription(current.value);
  } catch {
    current.value = null;
  } finally {
    loadingCurrent.value = false;
  }
}

async function loadPlans() {
  try {
    const res = await SubscriptionService.getPlans();
    plans.value = res.data?.data ?? [];
  } catch {
    plans.value = [];
  } finally {
    loadingPlans.value = false;
  }
}

async function loadHistory() {
  try {
    const res = await SubscriptionService.getHistory();
    history.value = res.data?.data ?? [];
  } catch {
    history.value = [];
  } finally {
    loadingHistory.value = false;
  }
}

onMounted(() => { loadCurrent(); loadPlans(); loadHistory(); });

// ── Modal ────────────────────────────────────────────────────────────────────
function openModal(plan) {
  modal.value = {
    open: true,
    plan,
    step: "input",
    loading: false,
    error: null,
  };
  form.value.confirmationMessage = "";
}

function closeModal() {
  modal.value.open = false;
  if (modal.value.step === "success") {
    loadHistory();
  }
}

function retryPayment() {
  modal.value.step = "input";
  modal.value.error = null;
  modal.value.loading = false;
}

// ── Offline M-Pesa payment submission ────────────────────────────────────────
async function submitOfflinePayment() {
  const confirmationMessage = form.value.confirmationMessage.trim();

  if (!confirmationMessage) {
    modal.value.error = "Paste your M-Pesa confirmation message before submitting.";
    return;
  }

  if (confirmationMessage.length < 15) {
    modal.value.error = "The confirmation message looks too short. Please paste the complete M-Pesa SMS.";
    return;
  }

  modal.value.loading = true;
  modal.value.error = null;

  try {
    await SubscriptionService.submitOfflinePayment({
      plan_id: modal.value.plan.id,
      confirmation_message: confirmationMessage,
    });
    modal.value.step = "success";
  } catch (err) {
    modal.value.error = err?.response?.data?.message
      ?? "We could not submit your payment confirmation. Please check your connection and try again.";
  } finally {
    modal.value.loading = false;
  }
}

// ── Formatters ────────────────────────────────────────────────────────────────
function fmtDate(iso) {
  if (!iso) return "—";
  return new Date(iso).toLocaleDateString("en-KE", { day: "numeric", month: "short", year: "numeric" });
}

function fmtPrice(v) {
  return Number(v).toLocaleString("en-KE");
}

function periodLabel(p) {
  return { monthly: "Monthly", quarterly: "Quarterly", annually: "Annual" }[p] ?? p;
}

function statusLabel(s) {
  return { trial: "Trial", active: "Active", expired: "Expired", cancelled: "Cancelled" }[s] ?? s;
}

function statusBadgeClass(s) {
  const base = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold";
  return {
    trial: `${base} bg-blue-100 text-blue-700`,
    active: `${base} bg-emerald-100 text-emerald-700`,
    expired: `${base} bg-red-100 text-red-700`,
    cancelled: `${base} bg-slate-100 text-slate-500`,
  }[s] ?? `${base} bg-slate-100 text-slate-500`;
}
</script>
