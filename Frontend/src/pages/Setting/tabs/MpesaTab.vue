<template>
  <div class="w-full">
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
      <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
        <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
          M-Pesa Payments
        </h2>
        <p class="text-xs text-slate-400 mt-1">
          Your Safaricom Daraja credentials for receiving M-Pesa payments.
        </p>
      </div>

      <form @submit.prevent="save" class="p-6">
        <GuidanceBox href="https://developer.safaricom.co.ke" link-label="Open Safaricom Daraja Portal">
          <template #title>Where to get these</template>
          All four values come from your <strong>Safaricom Daraja</strong> account, not from
          M-Pesa itself:
          <ol>
            <li>Sign in at <code>developer.safaricom.co.ke</code> and create an app under <strong>My Apps</strong>.</li>
            <li>The app page shows your <strong>Consumer Key</strong> and <strong>Consumer Secret</strong> immediately.</li>
            <li>
              For testing, use Daraja's sandbox <strong>Paybill (174379)</strong> and its published
              <strong>Passkey</strong> — no need to wait for a real till.
            </li>
            <li>
              For real payments, use your business's own registered Paybill/Till number. The
              production <strong>Passkey</strong> is issued by Safaricom directly once your
              shortcode goes live (not shown on the Daraja portal).
            </li>
          </ol>
        </GuidanceBox>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Paybill / Till Number</label>
            <input v-model="form.mpesa_shortcode" type="text" class="field-input" placeholder="e.g. 400200" />
            <p class="field-hint">Your business's registered M-Pesa shortcode.</p>
          </div>

          <div>
            <label class="field-label">Type</label>
            <select v-model="form.mpesa_shortcode_type" class="field-input">
              <option value="paybill">Paybill</option>
              <option value="till">Till Number</option>
            </select>
            <p class="field-hint">Must match the shortcode above — this changes how STK push requests are sent.</p>
          </div>

          <!-- M-Pesa Key (Passkey) -->
          <div class="md:col-span-2">
            <SecretRevealField
              v-model="form.mpesa_passkey"
              field-key="mpesa_passkey"
              label="M-Pesa Key (Passkey)"
              placeholder="Enter passkey"
              :is-set="!!settings?.mpesa_passkey_set"
              hint="Used for Lipa Na M-Pesa Online (STK Push). Sandbox key is published on the Daraja site; production key comes from Safaricom after go-live."
            />
          </div>

          <!-- Consumer Key -->
          <SecretRevealField
            v-model="form.mpesa_consumer_key"
            field-key="mpesa_consumer_key"
            label="Consumer Key"
            placeholder="Enter consumer key"
            :is-set="!!settings?.mpesa_consumer_key_set"
            hint="From your Daraja app page, under My Apps."
          />

          <!-- Consumer Secret -->
          <SecretRevealField
            v-model="form.mpesa_consumer_secret"
            field-key="mpesa_consumer_secret"
            label="Consumer Secret"
            placeholder="Enter consumer secret"
            :is-set="!!settings?.mpesa_consumer_secret_set"
            hint="Shown right next to the Consumer Key on the same app page."
          />
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
  mpesa_shortcode: "",
  mpesa_shortcode_type: "paybill",
  mpesa_passkey: "",
  mpesa_consumer_key: "",
  mpesa_consumer_secret: "",
});

watch(
  () => props.settings,
  (v) => {
    if (!v) return;
    form.mpesa_shortcode = v.mpesa_shortcode || "";
    form.mpesa_shortcode_type = v.mpesa_shortcode_type || "paybill";
    // Secrets are never sent by the backend — these inputs are always for
    // entering a NEW value. Use "Show saved value" to see a masked preview
    // of what's currently stored.
    form.mpesa_passkey = "";
    form.mpesa_consumer_key = "";
    form.mpesa_consumer_secret = "";
  },
  { immediate: true }
);

const save = async () => {
  loading.value = true;
  try {
    const res = await BusinessService.updateMpesa({ ...form });
    Swal.fire({ icon: "success", title: res?.data?.message || "M-Pesa settings saved.", confirmButtonColor: "#229DE7" });
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
