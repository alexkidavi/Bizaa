<template>
  <div class="w-full">
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
      <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-start justify-between gap-4">
        <div>
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            KRA eTIMS (DigiTax)
          </h2>
          <p class="text-xs text-slate-400 mt-1">
            Connect DigiTax to register products and issue KRA-compliant tax invoices on sales.
          </p>
        </div>

        <!-- ISSUE TAX COMPLIANCE INVOICE TOGGLE -->
        <button
          type="button"
          role="switch"
          :aria-checked="form.digitax_enabled"
          @click="form.digitax_enabled = !form.digitax_enabled"
          class="shrink-0 relative inline-flex h-7 w-12 items-center rounded-full transition-colors"
          :class="form.digitax_enabled ? 'bg-[#229DE7]' : 'bg-slate-300'"
        >
          <span
            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
            :class="form.digitax_enabled ? 'translate-x-6' : 'translate-x-1'"
          />
        </button>
      </div>

      <div class="px-6 pt-4">
        <p class="text-xs font-semibold" :class="form.digitax_enabled ? 'text-emerald-600' : 'text-slate-400'">
          {{ form.digitax_enabled
            ? 'ON — new products are registered with DigiTax and sales will issue eTIMS tax invoices.'
            : 'OFF — nothing is sent to DigiTax. Sales are recorded normally without a tax compliance invoice.' }}
        </p>
      </div>

      <form @submit.prevent="save" class="p-6 pt-4">
        <GuidanceBox href="https://digitax.tech" link-label="Open DigiTax">
          <template #title>Where to get an API key</template>
          DigiTax is the KRA-approved integrator that turns your sales into eTIMS tax invoices.
          <ol>
            <li>Sign up at <code>digitax.tech</code> and create a business — start with a <strong>sandbox</strong> business so nothing hits KRA while you test.</li>
            <li>In the dashboard, go to <strong>Integrations → Add API KEY</strong> and copy it — it's shown only once.</li>
            <li>Paste it below with <strong>Environment</strong> set to Sandbox, save, then create a test product/sale to confirm it works.</li>
            <li>Once verified, contact DigiTax to set up a <strong>production</strong> business with your real KRA PIN, generate a separate live key, and switch Environment to Production here.</li>
          </ol>
        </GuidanceBox>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Environment</label>
            <select v-model="form.digitax_environment" class="field-input">
              <option value="sandbox">Sandbox (testing)</option>
              <option value="production">Production (live)</option>
            </select>
            <p class="field-hint">Must match the business the API key below belongs to — a sandbox key won't work against Production.</p>
          </div>

          <div class="md:col-span-2">
            <SecretRevealField
              v-model="form.digitax_api_key"
              field-key="digitax_api_key"
              label="DigiTax API Key"
              placeholder="Enter X-API-Key from your DigiTax Dashboard"
              :is-set="!!settings?.digitax_api_key_set"
              hint="Generate this from your DigiTax Dashboard under Integrations → Add API KEY."
            />
          </div>
        </div>

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
import SecretRevealField from "./SecretRevealField.vue";

const props = defineProps({ settings: { type: Object, default: null } });
const emit = defineEmits(["saved"]);

const loading = ref(false);

const form = reactive({
  digitax_api_key: "",
  digitax_environment: "sandbox",
  digitax_enabled: false,
});

watch(
  () => props.settings,
  (v) => {
    if (!v) return;
    // Secret is never sent by the backend — this input is always for
    // entering a NEW value. Use "Show saved value" for a masked preview.
    form.digitax_api_key = "";
    form.digitax_environment = v.digitax_environment || "sandbox";
    form.digitax_enabled = !!v.digitax_enabled;
  },
  { immediate: true }
);

const save = async () => {
  loading.value = true;
  try {
    const res = await BusinessService.updateDigitax({ ...form });
    Swal.fire({ icon: "success", title: res?.data?.message || "DigiTax settings saved.", confirmButtonColor: "#229DE7" });
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
  appearance: none;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
.field-hint {
  font-size: 10.5px;
  color: #94a3b8;
  margin-top: 6px;
}
</style>
