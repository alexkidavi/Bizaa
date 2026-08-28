<template>
  <div class="w-full">
    <div v-if="loading" class="bg-white border border-[#D9EBF7] rounded-2xl p-10 flex justify-center">
      <div class="w-8 h-8 border-4 border-[#229DE7] border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
      <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-start justify-between gap-4">
        <div>
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Loyalty Program</h2>
          <p class="text-xs text-slate-400 mt-1">
            Reward registered customers with points on every sale, redeemable as cash at the till.
          </p>
        </div>
        <label class="toggle-switch shrink-0">
          <input type="checkbox" v-model="form.is_enabled" />
          <span class="toggle-slider"></span>
        </label>
      </div>

      <form @submit.prevent="save" class="p-6" :class="{ 'opacity-50 pointer-events-none': !form.is_enabled }">
        <GuidanceBox>
          <template #title>How this works</template>
          <ol>
            <li><strong>Earning</strong> — a registered customer earns points on the total of every sale, based on the rate below. Walk-in sales (no customer selected) never earn points.</li>
            <li><strong>Redeeming</strong> — at POS, "Pay with Points" converts a customer's points into cash value at the rate below, applied as a payment method. It's blocked if their balance is below the minimum, or if it can't cover the amount requested.</li>
          </ol>
        </GuidanceBox>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Earn Points</label>
            <div class="flex items-center gap-2">
              <input v-model.number="form.earn_points_awarded" type="number" min="1" step="1" class="field-input w-20 text-center" />
              <span class="text-xs text-slate-500 font-semibold whitespace-nowrap">point(s) per KES</span>
              <input v-model.number="form.earn_amount_spent" type="number" min="0.01" step="0.01" class="field-input" />
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">e.g. 1 point per KES 100 spent.</p>
          </div>

          <div>
            <label class="field-label">Point Value on Redeem</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">KES</span>
              <input v-model.number="form.point_value" type="number" min="0.0001" step="0.0001" class="field-input pl-12" />
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">Cash value of 1 point when a customer pays with points.</p>
          </div>

          <div>
            <label class="field-label">Minimum Points to Redeem</label>
            <input v-model.number="form.min_redeem_points" type="number" min="0" step="1" class="field-input" />
            <p class="text-[11px] text-slate-400 mt-1.5">A customer's balance must be at least this before they can redeem anything.</p>
          </div>
        </div>

        <div class="border-t border-[#EEF5FB] my-6"></div>

        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="saving"
            class="flex items-center justify-center gap-2 px-8 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60"
          >
            {{ saving ? "Saving..." : "Save Changes" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import Swal from "sweetalert2";
import LoyaltySettingService from "@/services/LoyaltySettingService";
import GuidanceBox from "./GuidanceBox.vue";

const loading = ref(true);
const saving = ref(false);

const form = reactive({
  is_enabled: false,
  earn_amount_spent: 100,
  earn_points_awarded: 1,
  point_value: 1,
  min_redeem_points: 0,
});

const applySettings = (s) => {
  form.is_enabled = !!s.is_enabled;
  form.earn_amount_spent = Number(s.earn_amount_spent ?? 100);
  form.earn_points_awarded = Number(s.earn_points_awarded ?? 1);
  form.point_value = Number(s.point_value ?? 1);
  form.min_redeem_points = Number(s.min_redeem_points ?? 0);
};

onMounted(async () => {
  try {
    const res = await LoyaltySettingService.get();
    applySettings(res?.data ?? {});
  } finally {
    loading.value = false;
  }
});

const save = async () => {
  saving.value = true;
  try {
    const res = await LoyaltySettingService.update({ ...form });
    applySettings(res?.data ?? {});
    Swal.fire({ icon: "success", title: res?.message || "Loyalty settings saved.", confirmButtonColor: "#229DE7" });
  } catch (err) {
    Swal.fire("Error", err?.response?.data?.message || "Something went wrong.", "error");
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }

.toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 999px; transition: 0.2s; }
.toggle-slider::before { content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.2s; }
.toggle-switch input:checked + .toggle-slider { background: #229de7; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
</style>
