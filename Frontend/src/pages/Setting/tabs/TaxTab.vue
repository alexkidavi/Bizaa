<template>
  <div class="w-full">
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
      <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
        <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
          Tax Settings
        </h2>
        <p class="text-xs text-slate-400 mt-1">
          Used for VAT calculation and KRA integration on sales, invoices and reports.
        </p>
      </div>

      <form @submit.prevent="save" class="p-6">
        <GuidanceBox href="https://itax.kra.go.ke" link-label="Open iTax">
          <template #title>Where to get this</template>
          <ol>
            <li>
              <strong>KRA PIN</strong> — your business's Personal Identification Number,
              printed on your KRA PIN certificate or visible after logging in to
              <code>itax.kra.go.ke</code>.
            </li>
            <li>
              <strong>Tax Rate</strong> — Kenya's standard VAT rate is <strong>16%</strong>.
              Individual zero-rated or exempt products can still be marked as such on the
              product itself.
            </li>
          </ol>
        </GuidanceBox>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Tax Rate (%)</label>
            <div class="relative">
              <input
                v-model.number="form.tax_rate"
                type="number"
                min="0"
                max="100"
                step="0.01"
                class="field-input pr-9"
                placeholder="e.g. 16"
              />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">%</span>
            </div>
          </div>

          <div>
            <label class="field-label">KRA PIN</label>
            <input v-model="form.kra_pin" type="text" class="field-input" placeholder="e.g. P051234567X" />
          </div>
        </div>

        <p class="text-[11px] text-slate-400 mt-4">
          Issuing actual KRA-compliant tax invoices on sales is configured separately under the
          <strong>KRA eTIMS</strong> tab.
        </p>

        <div class="border-t border-[#EEF5FB] my-6"></div>

        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="loading"
            class="flex items-center justify-center gap-2 px-8 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60"
          >
            {{ loading ? "Saving..." : "Save Changes" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from "vue";
import Swal from "sweetalert2";
import BusinessService from "@/services/BusinessService";
import GuidanceBox from "./GuidanceBox.vue";

const props = defineProps({ settings: { type: Object, default: null } });
const emit = defineEmits(["saved"]);

const loading = ref(false);

const form = reactive({ tax_rate: null, kra_pin: "" });

watch(
  () => props.settings,
  (v) => {
    if (!v) return;
    form.tax_rate = v.tax_rate ?? null;
    form.kra_pin = v.kra_pin || "";
  },
  { immediate: true }
);

const save = async () => {
  loading.value = true;
  try {
    const res = await BusinessService.updateTax({
      tax_rate: form.tax_rate,
      kra_pin: form.kra_pin,
    });
    Swal.fire({ icon: "success", title: res?.data?.message || "Tax settings saved.", confirmButtonColor: "#229DE7" });
    emit("saved", res?.data?.data ?? res?.data ?? {});
  } catch (err) {
    Swal.fire("Error", err?.response?.data?.message || "Something went wrong.", "error");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.field-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
</style>
