<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- LEFT: Logo + Code -->
    <aside class="lg:col-span-4 flex flex-col gap-5">
      <!-- Logo Card -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Business Logo
          </h2>
        </div>
        <div class="p-6 flex flex-col items-center gap-4">
          <div
            class="w-28 h-28 rounded-2xl border-2 border-dashed border-[#B3DEFA] bg-[#EAF5FD] flex items-center justify-center overflow-hidden relative group cursor-pointer"
            @click="triggerLogoUpload"
          >
            <img
              v-if="logoPreview"
              :src="logoPreview"
              alt="Business Logo"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex flex-col items-center gap-2 text-[#229DE7]">
              <svg class="w-10 h-10 opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 12V6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25V12z" />
              </svg>
              <span class="text-[10px] font-bold uppercase tracking-widest opacity-60">Upload Logo</span>
            </div>
            <div class="absolute inset-0 bg-[#229DE7]/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
              <svg class="w-6 h-6 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
              </svg>
            </div>
          </div>
          <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="handleLogoChange" />
          <button
            @click="triggerLogoUpload"
            class="text-xs font-bold text-[#229DE7] hover:text-[#1682C4] transition underline underline-offset-2"
          >
            {{ logoPreview ? "Change Logo" : "Upload Logo" }}
          </button>
          <p class="text-[10.5px] text-slate-400 text-center">
            PNG, JPG or SVG. Max 2MB. Recommended: 256×256px.
          </p>
        </div>
      </div>

      <!-- Business Code Card -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Business Code
          </h2>
        </div>
        <div class="p-6">
          <div class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-4 flex items-center justify-between gap-3">
            <div>
              <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Unique Code</p>
              <p class="text-lg font-extrabold text-[#1682C4] tracking-widest font-mono">
                {{ form.business_code || "—" }}
              </p>
            </div>
            <button
              @click="copyCode"
              class="w-9 h-9 rounded-lg flex items-center justify-center border border-[#B3DEFA] bg-white text-[#229DE7] hover:bg-[#229DE7] hover:text-white transition"
              title="Copy Code"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </button>
          </div>
          <p class="text-[10.5px] text-slate-400 mt-3">
            This code uniquely identifies your business. It is read-only.
          </p>
        </div>
      </div>
    </aside>

    <!-- RIGHT: Form -->
    <main class="lg:col-span-8">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Business Information
          </h2>
          <span class="text-[10.5px] font-bold text-slate-400">* Required fields</span>
        </div>

        <form @submit.prevent="saveSettings" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="field-label required">Business Name</label>
              <input v-model="form.business_name" type="text" class="field-input" :class="{ 'border-red-400': vErrors.business_name }" placeholder="e.g. Acme Corporation" />
              <p v-if="vErrors.business_name" class="field-error">Business name is required</p>
            </div>

            <div>
              <label class="field-label required">Business Email</label>
              <input v-model="form.business_email" type="email" class="field-input" :class="{ 'border-red-400': vErrors.business_email }" placeholder="e.g. info@acme.com" />
              <p v-if="vErrors.business_email" class="field-error">{{ vErrors.business_email_msg || "Valid email is required" }}</p>
            </div>

            <div>
              <label class="field-label required">Business Phone</label>
              <input v-model="form.business_phone" type="tel" class="field-input" :class="{ 'border-red-400': vErrors.business_phone }" placeholder="e.g. +254 712 345 678" />
              <p v-if="vErrors.business_phone" class="field-error">Phone number is required</p>
            </div>

            <div>
              <label class="field-label">Website</label>
              <input v-model="form.business_website" type="url" class="field-input" placeholder="e.g. https://acme.com" />
            </div>

            <div>
              <label class="field-label">Industry</label>
              <select v-model="form.business_industry" class="field-input">
                <option value="">Select industry...</option>
                <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="field-label">Business Location</label>
              <input v-model="form.business_location" type="text" class="field-input" placeholder="e.g. Nairobi, Kenya" />
            </div>

            <div class="md:col-span-2">
              <label class="field-label">Report Brand Color</label>
              <div class="flex items-center gap-3">
                <input
                  v-model="form.report_brand_color"
                  type="color"
                  class="w-12 h-11 rounded-lg border border-[#D9EBF7] cursor-pointer bg-white"
                />
                <input
                  v-model="form.report_brand_color"
                  type="text"
                  class="field-input flex-1"
                  placeholder="#229DE7"
                  maxlength="7"
                />
              </div>
              <p class="text-[10.5px] text-slate-400 mt-1.5">
                Used as the accent color on your exported PDF/Excel reports, alongside your logo.
              </p>
            </div>
          </div>

          <div class="border-t border-[#EEF5FB] my-6"></div>

          <div class="flex items-center justify-between gap-4">
            <button
              type="button"
              @click="resetForm"
              class="px-5 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-500 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition"
            >
              Reset Changes
            </button>
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
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import Swal from "sweetalert2";
import BusinessService from "@/services/BusinessService";

const props = defineProps({ settings: { type: Object, default: null } });
const emit = defineEmits(["saved"]);

const loading = ref(false);
const logoPreview = ref(null);
const logoFile = ref(null);
const logoInput = ref(null);
const originalForm = ref({});

const form = reactive({
  business_name: "",
  business_email: "",
  business_phone: "",
  business_website: "",
  business_logo: "",
  report_brand_color: "",
  business_industry: "",
  business_location: "",
  business_code: "",
});

const vErrors = reactive({
  business_name: false,
  business_email: false,
  business_email_msg: "",
  business_phone: false,
});

const industries = [
  "Retail & E-commerce", "Food & Beverage", "Healthcare & Wellness", "Education & Training",
  "Technology & Software", "Finance & Accounting", "Real Estate & Property", "Manufacturing",
  "Logistics & Transport", "Hospitality & Tourism", "Agriculture", "Media & Entertainment",
  "Construction", "Professional Services", "Other",
];

const BASE_URL = import.meta.env.VITE_APP_URL ?? "";

const populateForm = (data) => {
  if (!data) return;
  form.business_name = data.business_name || "";
  form.business_email = data.business_email || "";
  form.business_phone = data.business_phone || "";
  form.business_website = data.business_website || "";
  form.business_logo = data.business_logo || "";
  form.report_brand_color = data.report_brand_color || "#229DE7";
  form.business_industry = data.business_industry || "";
  form.business_location = data.business_location || "";
  form.business_code = data.business_code || "";
  if (data.business_logo) {
    const isAbsolute = /^(https?:\/\/|data:)/i.test(data.business_logo);
    logoPreview.value = isAbsolute ? data.business_logo : `${BASE_URL}/storage/${data.business_logo}`;
  }
};

watch(
  () => props.settings,
  (v) => {
    populateForm(v);
    originalForm.value = { ...v };
  },
  { immediate: true }
);

const triggerLogoUpload = () => logoInput.value?.click();

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) {
    Swal.fire({ icon: "warning", title: "File too large", text: "Maximum logo size is 2MB.", confirmButtonColor: "#229DE7" });
    return;
  }
  logoFile.value = file;
  const reader = new FileReader();
  reader.onload = (ev) => (logoPreview.value = ev.target.result);
  reader.readAsDataURL(file);
};

const validate = () => {
  vErrors.business_name = !form.business_name.trim();
  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.business_email);
  vErrors.business_email = !form.business_email.trim() || !emailOk;
  vErrors.business_email_msg = !form.business_email.trim() ? "Email is required" : "Enter a valid email address";
  vErrors.business_phone = !form.business_phone.trim();
  return !vErrors.business_name && !vErrors.business_email && !vErrors.business_phone;
};

const saveSettings = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    let res;
    if (logoFile.value) {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => {
        if (k !== "business_code" && k !== "business_logo") fd.append(k, v);
      });
      fd.append("business_logo", logoFile.value);
      res = await BusinessService.update(fd);
    } else {
      const payload = { ...form };
      delete payload.business_code;
      delete payload.business_logo;
      res = await BusinessService.update(payload);
    }

    Swal.fire({ icon: "success", title: res?.data?.message || "Settings saved successfully.", confirmButtonColor: "#229DE7" });

    logoFile.value = null;
    const updated = res?.data?.data ?? res?.data ?? {};
    if (updated.business_name) emit("saved", updated);
  } catch (err) {
    handleError(err);
  } finally {
    loading.value = false;
  }
};

const handleError = (err) => {
  const res = err?.response;
  if (res?.status === 422 && res.data?.errors) {
    const errors = res.data.errors;
    if (errors.business_name) vErrors.business_name = true;
    if (errors.business_email) {
      vErrors.business_email = true;
      vErrors.business_email_msg = errors.business_email[0];
    }
    if (errors.business_phone) vErrors.business_phone = true;
    Swal.fire({ icon: "error", title: "Validation Error", text: "Please fix the highlighted fields.", confirmButtonColor: "#229DE7" });
    return;
  }
  Swal.fire({ icon: "error", title: "Error", text: res?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
};

const resetForm = () => {
  populateForm(originalForm.value);
  logoFile.value = null;
  Object.keys(vErrors).forEach((k) => (vErrors[k] = false));
  vErrors.business_email_msg = "";
};

const copyCode = async () => {
  if (!form.business_code) return;
  await navigator.clipboard.writeText(form.business_code);
  Swal.fire({ icon: "success", title: "Copied!", toast: true, position: "top-end", showConfirmButton: false, timer: 1800, timerProgressBar: true });
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
.field-label.required::after {
  content: " *";
  color: #ef4444;
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
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
</style>
