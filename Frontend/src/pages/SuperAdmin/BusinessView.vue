<template>
  <div class="sa-page">
    <div class="sa-back" @click="$router.back()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Businesses
    </div>

    <div v-if="loading" class="sa-loading">Loading…</div>

    <template v-else-if="data">
      <!-- Header -->
      <div class="sa-biz-header">
        <div class="sa-biz-header__logo">{{ data.business.name?.charAt(0) }}</div>
        <div class="sa-biz-header__info">
          <h2>{{ data.business.name }}</h2>
          <p>{{ data.business.email }} · {{ data.business.phone }}</p>
          <p style="color:#64748b;font-size:12px">{{ data.business.industry }} · {{ data.business.location }} · Joined {{ data.business.created_at }}</p>
        </div>
        <div class="sa-biz-header__actions">
          <button class="sa-btn sa-btn--danger" @click="suspendBusiness" :disabled="actionLoading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            Suspend
          </button>
          <button class="sa-btn sa-btn--success" @click="reinstateBusiness" :disabled="actionLoading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            Reinstate
          </button>
          <button class="sa-btn sa-btn--primary" @click="showGrantModal = true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Grant Subscription
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="sa-tabs">
        <button class="sa-tab" :class="{ 'sa-tab--active': tab === 'subs' }" @click="tab = 'subs'">Subscriptions ({{ data.subscriptions.length }})</button>
        <button class="sa-tab" :class="{ 'sa-tab--active': tab === 'users' }" @click="tab = 'users'">Users ({{ data.users.length }})</button>
      </div>

      <!-- Subscriptions -->
      <div v-if="tab === 'subs'" class="sa-card">
        <table class="sa-table">
          <thead><tr><th>Plan</th><th>Status</th><th>Start</th><th>End</th><th>Amount</th><th>Method</th><th></th></tr></thead>
          <tbody>
            <tr v-for="s in data.subscriptions" :key="s.id">
              <td>{{ s.plan }}</td>
              <td><span class="sa-badge" :class="statusClass(s.status)">{{ s.status }}</span></td>
              <td>{{ s.starts_at }}</td>
              <td>{{ s.ends_at }}</td>
              <td>KES {{ Number(s.amount_paid).toLocaleString() }}</td>
              <td>{{ s.payment_method ?? '—' }}</td>
              <td>
                <div style="display:flex;gap:6px">
                  <button v-if="s.status === 'active' || s.status === 'trial'" class="sa-btn sa-btn--sm sa-btn--ghost" @click="extendSub(s.id)">+30d</button>
                  <button v-if="s.status !== 'cancelled'" class="sa-btn sa-btn--sm sa-btn--danger" @click="revokeSub(s.id)">Revoke</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="data.subscriptions.length === 0" class="sa-empty">No subscriptions.</p>
      </div>

      <!-- Users -->
      <div v-if="tab === 'users'" class="sa-card">
        <table class="sa-table">
          <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
          <tbody>
            <tr v-for="u in data.users" :key="u.id">
              <td>{{ u.name }}</td>
              <td>{{ u.email }}</td>
              <td>{{ u.role ?? '—' }}</td>
              <td><span class="sa-badge" :class="u.is_active ? 'sa-badge--green' : 'sa-badge--red'">{{ u.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td>{{ u.joined }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="data.users.length === 0" class="sa-empty">No users.</p>
      </div>
    </template>

    <!-- Grant Subscription Modal -->
    <Teleport to="body">
      <div v-if="showGrantModal" class="sa-modal-overlay" @click.self="showGrantModal = false">
        <div class="sa-modal">
          <h3 class="sa-modal__title">Grant Subscription</h3>
          <div class="sa-form-group">
            <label>Plan</label>
            <select v-model="grantForm.plan_id">
              <option value="">Select plan…</option>
              <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} ({{ p.billing_period }})</option>
            </select>
          </div>
          <div class="sa-form-group">
            <label>Days</label>
            <input v-model.number="grantForm.days" type="number" min="1" placeholder="30" />
          </div>
          <div class="sa-form-group">
            <label>Amount Paid (KES)</label>
            <input v-model.number="grantForm.amount_paid" type="number" min="0" placeholder="0" />
          </div>
          <div class="sa-form-group">
            <label>Notes (optional)</label>
            <textarea v-model="grantForm.notes" rows="2" placeholder="Internal notes…"></textarea>
          </div>
          <div class="sa-modal__actions">
            <button class="sa-btn sa-btn--ghost" @click="showGrantModal = false">Cancel</button>
            <button class="sa-btn sa-btn--primary" @click="grantSub" :disabled="!grantForm.plan_id || !grantForm.days || actionLoading">Grant</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import SuperAdminService from "@/services/SuperAdminService";

const route       = useRoute();
const loading     = ref(true);
const actionLoading = ref(false);
const data        = ref(null);
const plans       = ref([]);
const tab         = ref("subs");
const showGrantModal = ref(false);
const grantForm   = ref({ plan_id: "", days: 30, amount_paid: 0, notes: "" });

const statusClass = (s) => ({
  active:    "sa-badge--green",
  trial:     "sa-badge--blue",
  expired:   "sa-badge--red",
  cancelled: "sa-badge--red",
}[s] ?? "sa-badge--amber");

const load = async () => {
  loading.value = true;
  try {
    const [biz, pl] = await Promise.all([
      SuperAdminService.getBusiness(route.params.id),
      SuperAdminService.getPlans(),
    ]);
    data.value  = biz;
    plans.value = pl;
  } finally {
    loading.value = false;
  }
};

const suspendBusiness = async () => {
  if (!confirm(`Suspend ${data.value.business.name}? This deactivates all users.`)) return;
  actionLoading.value = true;
  try { await SuperAdminService.suspendBusiness(route.params.id); alert("Business suspended."); await load(); }
  finally { actionLoading.value = false; }
};

const reinstateBusiness = async () => {
  actionLoading.value = true;
  try { await SuperAdminService.reinstateBusiness(route.params.id); alert("Business reinstated."); await load(); }
  finally { actionLoading.value = false; }
};

const grantSub = async () => {
  actionLoading.value = true;
  try {
    await SuperAdminService.grantSubscription({ ...grantForm.value, business_id: Number(route.params.id) });
    showGrantModal.value = false;
    await load();
    alert("Subscription granted.");
  } catch (e) {
    alert(e.response?.data?.message ?? "Error granting subscription.");
  } finally {
    actionLoading.value = false;
  }
};

const revokeSub = async (id) => {
  if (!confirm("Cancel this subscription?")) return;
  actionLoading.value = true;
  try { await SuperAdminService.revokeSubscription(id); await load(); }
  finally { actionLoading.value = false; }
};

const extendSub = async (id) => {
  actionLoading.value = true;
  try { const r = await SuperAdminService.extendSubscription(id, 30); alert(r.message); await load(); }
  finally { actionLoading.value = false; }
};

onMounted(load);
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 20px; }
.sa-back { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 13px; cursor: pointer; width: fit-content; }
.sa-back:hover { color: #94a3b8; }
.sa-loading, .sa-empty { padding: 48px; text-align: center; color: #64748b; font-size: 13px; }

.sa-biz-header {
  background: #1e293b; border: 1px solid #334155; border-radius: 12px;
  padding: 24px; display: flex; align-items: center; gap: 18px;
}
.sa-biz-header__logo {
  width: 56px; height: 56px; border-radius: 14px; background: #1e3a5f;
  color: #60a5fa; font-size: 26px; font-weight: 800;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sa-biz-header__info h2 { font-size: 18px; font-weight: 700; color: #f1f5f9; }
.sa-biz-header__info p  { font-size: 13px; color: #94a3b8; margin-top: 3px; }
.sa-biz-header__actions { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }

.sa-tabs { display: flex; gap: 4px; border-bottom: 1px solid #334155; }
.sa-tab { padding: 10px 18px; background: none; border: none; color: #64748b; font-size: 13px; font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.15s; }
.sa-tab:hover { color: #94a3b8; }
.sa-tab--active { color: #60a5fa; border-bottom-color: #60a5fa; }

.sa-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sa-table th { padding: 12px 16px; font-size: 11px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #334155; text-align: left; }
.sa-table td { padding: 12px 16px; color: #cbd5e1; border-bottom: 1px solid #1e293b; }
.sa-table tr:last-child td { border-bottom: none; }

.sa-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
.sa-badge--green { background: #14532d; color: #4ade80; }
.sa-badge--blue  { background: #1e3a5f; color: #60a5fa; }
.sa-badge--amber { background: #451a03; color: #fbbf24; }
.sa-badge--red   { background: #450a0a; color: #f87171; }

.sa-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
.sa-btn--sm { padding: 4px 10px; font-size: 12px; }
.sa-btn--primary { background: #1e3a5f; color: #60a5fa; border: 1px solid #3b82f6; }
.sa-btn--primary:hover { background: #3b82f6; color: #fff; }
.sa-btn--success { background: #14532d; color: #4ade80; border: 1px solid #22c55e; }
.sa-btn--success:hover { background: #22c55e; color: #fff; }
.sa-btn--danger  { background: #450a0a; color: #f87171; border: 1px solid #ef4444; }
.sa-btn--danger:hover  { background: #ef4444; color: #fff; }
.sa-btn--ghost   { background: transparent; border: 1px solid #334155; color: #94a3b8; }
.sa-btn--ghost:hover { background: #0f172a; color: #e2e8f0; }
.sa-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.sa-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.sa-modal { background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 28px; width: 420px; max-width: 95vw; }
.sa-modal__title { font-size: 16px; font-weight: 700; color: #f1f5f9; margin-bottom: 20px; }
.sa-modal__actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.sa-form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.sa-form-group label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
.sa-form-group input, .sa-form-group select, .sa-form-group textarea {
  background: #0f172a; border: 1px solid #334155; border-radius: 8px;
  color: #e2e8f0; font-size: 13px; padding: 9px 12px; outline: none;
}
.sa-form-group input:focus, .sa-form-group select:focus, .sa-form-group textarea:focus { border-color: #3b82f6; }
</style>
