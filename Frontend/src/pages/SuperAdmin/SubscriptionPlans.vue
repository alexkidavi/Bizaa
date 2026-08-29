<template>
  <div class="sa-page">
    <p class="sa-page-desc">
      Manage the plans shown on the public pricing section and the in-portal Subscription page — both read
      live from this list, so changes here take effect immediately, no deploy required.
    </p>

    <div class="sa-toolbar">
      <span class="sa-count">{{ plans.length }} plan{{ plans.length === 1 ? "" : "s" }}</span>
      <button class="sa-btn sa-btn--primary" style="margin-left: auto" @click="openCreate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Add Plan
      </button>
    </div>

    <div class="sa-card">
      <div v-if="loading" class="sa-loading">Loading…</div>
      <div v-else-if="plans.length === 0" class="sa-empty">No plans yet — add your first one.</div>
      <div v-else class="sa-table-wrap">
        <table class="sa-table">
          <thead>
            <tr>
              <th></th>
              <th>Plan</th>
              <th>Billing</th>
              <th>Price (KES)</th>
              <th>Duration</th>
              <th>Features</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, idx) in plans" :key="p.id">
              <td>
                <div class="sa-order-arrows">
                  <button class="sa-order-btn" :disabled="idx === 0" @click="move(idx, -1)" title="Move up">▲</button>
                  <button class="sa-order-btn" :disabled="idx === plans.length - 1" @click="move(idx, 1)" title="Move down">▼</button>
                </div>
              </td>
              <td>
                <p style="font-weight:600;color:#f1f5f9">{{ p.name }}</p>
                <p style="font-size:11px;color:#64748b">{{ p.slug }}</p>
              </td>
              <td style="text-transform:capitalize">{{ p.billing_period }}</td>
              <td>{{ Number(p.price).toLocaleString() }}</td>
              <td>{{ p.duration_days }} days</td>
              <td>{{ (p.features || []).length }}</td>
              <td>
                <span class="sa-badge" :class="p.is_active ? 'sa-badge--green' : 'sa-badge--gray'">
                  {{ p.is_active ? "Active" : "Inactive" }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:6px;justify-content:flex-end">
                  <button class="sa-btn sa-btn--sm sa-btn--ghost" @click="toggleActive(p)">
                    {{ p.is_active ? "Deactivate" : "Activate" }}
                  </button>
                  <button class="sa-btn sa-btn--sm sa-btn--ghost" @click="openEdit(p)">Edit</button>
                  <button class="sa-btn sa-btn--sm sa-btn--danger" @click="remove(p)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════ CREATE / EDIT MODAL ══════════ -->
    <Teleport to="body">
      <div v-if="modal.open" class="sa-modal-backdrop" @click.self="closeModal">
        <div class="sa-modal">
          <div class="sa-modal__head">
            <h3>{{ modal.editing ? "Edit Plan" : "Add Plan" }}</h3>
            <button class="sa-modal__close" @click="closeModal">✕</button>
          </div>

          <form @submit.prevent="save" class="sa-modal__body">
            <div class="sa-field">
              <label>Plan Name</label>
              <input v-model="form.name" type="text" class="sa-input" placeholder="e.g. Monthly Plan" required />
            </div>

            <div class="sa-field-row">
              <div class="sa-field">
                <label>Billing Period</label>
                <select v-model="form.billing_period" class="sa-input">
                  <option value="trial">Trial</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="annually">Annually</option>
                </select>
              </div>
              <div class="sa-field">
                <label>Duration (days)</label>
                <input v-model.number="form.duration_days" type="number" min="1" class="sa-input" required />
              </div>
            </div>

            <div class="sa-field">
              <label>Price (KES)</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" class="sa-input" required />
            </div>

            <div class="sa-field">
              <label>Features (one per line)</label>
              <textarea v-model="form.featuresText" class="sa-input sa-textarea" rows="6" placeholder="Full POS access&#10;Inventory management&#10;Up to 5 staff accounts"></textarea>
            </div>

            <label class="sa-checkbox-row">
              <input v-model="form.is_active" type="checkbox" />
              Active — visible on pricing page and portal
            </label>

            <p v-if="formError" class="sa-form-error">{{ formError }}</p>

            <div class="sa-modal__foot">
              <button type="button" class="sa-btn sa-btn--ghost" @click="closeModal">Cancel</button>
              <button type="submit" class="sa-btn sa-btn--primary" :disabled="saving">
                {{ saving ? "Saving…" : modal.editing ? "Save Changes" : "Create Plan" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import superAdminService from "@/services/superAdminService";

const plans   = ref([]);
const loading = ref(true);
const saving  = ref(false);
const formError = ref("");

const modal = reactive({ open: false, editing: null });

const emptyForm = () => ({
  name: "",
  billing_period: "monthly",
  price: 0,
  duration_days: 30,
  featuresText: "",
  is_active: true,
});
const form = reactive(emptyForm());

const swalDark = { background: "#1e293b", color: "#e2e8f0", confirmButtonColor: "#3b82f6" };

async function fetchPlans() {
  loading.value = true;
  try {
    const res = await superAdminService.listPlanCatalog();
    plans.value = res?.data ?? [];
  } finally {
    loading.value = false;
  }
}
onMounted(fetchPlans);

function openCreate() {
  Object.assign(form, emptyForm());
  formError.value = "";
  modal.editing = null;
  modal.open = true;
}

function openEdit(plan) {
  Object.assign(form, {
    name: plan.name,
    billing_period: plan.billing_period,
    price: Number(plan.price),
    duration_days: plan.duration_days,
    featuresText: (plan.features || []).join("\n"),
    is_active: !!plan.is_active,
  });
  formError.value = "";
  modal.editing = plan;
  modal.open = true;
}

function closeModal() {
  modal.open = false;
}

async function save() {
  saving.value = true;
  formError.value = "";
  const payload = {
    name: form.name,
    billing_period: form.billing_period,
    price: form.price,
    duration_days: form.duration_days,
    is_active: form.is_active,
    features: form.featuresText
      .split("\n")
      .map((f) => f.trim())
      .filter(Boolean),
  };

  try {
    if (modal.editing) {
      const res = await superAdminService.updatePlan(modal.editing.id, payload);
      const idx = plans.value.findIndex((p) => p.id === modal.editing.id);
      if (idx !== -1) plans.value[idx] = res.data;
    } else {
      const res = await superAdminService.createPlan(payload);
      plans.value.push(res.data);
    }
    modal.open = false;
    Swal.fire({ icon: "success", title: modal.editing ? "Plan updated" : "Plan created", toast: true, position: "top-end", showConfirmButton: false, timer: 2000, background: "#1e293b", color: "#e2e8f0" });
  } catch (err) {
    formError.value = err?.response?.data?.message || "Could not save plan. Check the fields and try again.";
  } finally {
    saving.value = false;
  }
}

async function toggleActive(plan) {
  try {
    const res = await superAdminService.togglePlanActive(plan.id);
    const idx = plans.value.findIndex((p) => p.id === plan.id);
    if (idx !== -1) plans.value[idx] = res.data;
  } catch {
    Swal.fire({ icon: "error", title: "Could not update plan.", ...swalDark });
  }
}

async function remove(plan) {
  const result = await Swal.fire({
    icon: "warning",
    title: `Delete "${plan.name}"?`,
    text: "This cannot be undone. Plans that have ever had a business subscribed to them can't be deleted — deactivate instead.",
    showCancelButton: true,
    confirmButtonText: "Delete",
    ...swalDark,
    confirmButtonColor: "#ef4444",
  });
  if (!result.isConfirmed) return;

  try {
    await superAdminService.deletePlan(plan.id);
    plans.value = plans.value.filter((p) => p.id !== plan.id);
    Swal.fire({ icon: "success", title: "Plan deleted", toast: true, position: "top-end", showConfirmButton: false, timer: 2000, background: "#1e293b", color: "#e2e8f0" });
  } catch (err) {
    Swal.fire({ icon: "error", title: "Could not delete plan", text: err?.response?.data?.errors?.plan?.[0] || err?.response?.data?.message, ...swalDark });
  }
}

async function move(idx, dir) {
  const target = idx + dir;
  if (target < 0 || target >= plans.value.length) return;
  const reordered = [...plans.value];
  [reordered[idx], reordered[target]] = [reordered[target], reordered[idx]];
  plans.value = reordered;

  try {
    await superAdminService.reorderPlans(reordered.map((p) => p.id));
  } catch {
    Swal.fire({ icon: "error", title: "Could not save new order", ...swalDark });
    fetchPlans();
  }
}
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 20px; }
.sa-page-desc { color: #94a3b8; font-size: 13px; max-width: 640px; line-height: 1.6; }
.sa-toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sa-count { font-size: 13px; color: #64748b; }

.sa-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; overflow: hidden; }
.sa-loading, .sa-empty { padding: 48px; text-align: center; color: #64748b; font-size: 13px; }
.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sa-table th { padding: 12px 16px; font-size: 11px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #334155; text-align: left; }
.sa-table td { padding: 12px 16px; color: #cbd5e1; border-bottom: 1px solid #1e293b; vertical-align: middle; }
.sa-table tr:last-child td { border-bottom: none; }

.sa-order-arrows { display: flex; flex-direction: column; gap: 2px; }
.sa-order-btn { background: none; border: 1px solid #334155; border-radius: 4px; color: #64748b; font-size: 9px; width: 20px; height: 16px; line-height: 1; cursor: pointer; }
.sa-order-btn:hover:not(:disabled) { color: #e2e8f0; border-color: #3b82f6; }
.sa-order-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.sa-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.sa-badge--green { background: #14532d; color: #4ade80; }
.sa-badge--gray  { background: #1e293b; color: #64748b; border: 1px solid #334155; }

.sa-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
.sa-btn--sm      { padding: 4px 10px; font-size: 12px; }
.sa-btn--ghost   { background: transparent; border: 1px solid #334155; color: #94a3b8; }
.sa-btn--ghost:hover { background: #0f172a; color: #e2e8f0; }
.sa-btn--primary { background: #3b82f6; color: #fff; }
.sa-btn--primary:hover:not(:disabled) { background: #2563eb; }
.sa-btn--danger  { background: #450a0a; color: #f87171; border: 1px solid #ef4444; }
.sa-btn--danger:hover  { background: #ef4444; color: #fff; }
.sa-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Modal */
.sa-modal-backdrop { position: fixed; inset: 0; background: rgba(2, 6, 23, 0.7); backdrop-filter: blur(2px); display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 60; }
.sa-modal { background: #1e293b; border: 1px solid #334155; border-radius: 14px; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; }
.sa-modal__head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #334155; }
.sa-modal__head h3 { font-size: 15px; font-weight: 700; color: #f1f5f9; }
.sa-modal__close { background: none; border: none; color: #64748b; font-size: 16px; cursor: pointer; }
.sa-modal__close:hover { color: #e2e8f0; }
.sa-modal__body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.sa-modal__foot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px; }

.sa-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.sa-field label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px; font-weight: 600; }
.sa-input {
  width: 100%; padding: 9px 12px; background: #0f172a; border: 1px solid #334155;
  border-radius: 8px; color: #e2e8f0; font-size: 13px; outline: none; transition: border-color 0.15s;
}
.sa-input:focus { border-color: #3b82f6; }
.sa-input::placeholder { color: #475569; }
.sa-textarea { resize: vertical; font-family: inherit; line-height: 1.6; }
select.sa-input { cursor: pointer; }

.sa-checkbox-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #cbd5e1; cursor: pointer; }
.sa-form-error { font-size: 12px; color: #f87171; background: #450a0a; border: 1px solid #7f1d1d; border-radius: 8px; padding: 8px 12px; }

@media (max-width: 640px) {
  .sa-field-row { grid-template-columns: 1fr; }
}
</style>
