<template>
  <div class="sa-page">
    <div v-if="loading" class="sa-loading-full">Loading settings…</div>

    <template v-else>
      <p class="sa-page-desc">
        Branding and contact info shown on the public landing page, the Privacy page, and outgoing emails —
        editing this takes effect immediately, no deploy required.
      </p>

      <form @submit.prevent="save" class="sa-section-grid">
        <!-- Logo -->
        <div class="sa-card">
          <h2 class="sa-card__title">Logo</h2>
          <div class="sa-logo-row">
            <div class="sa-logo-preview">
              <img v-if="logoPreview" :src="logoPreview" alt="Logo" />
              <span v-else>No logo</span>
            </div>
            <div>
              <label class="sa-upload-btn">
                Choose File
                <input type="file" accept="image/*" class="sa-hidden-input" @change="onLogoChange" />
              </label>
              <p class="sa-hint">PNG or SVG, transparent background recommended.</p>
            </div>
          </div>
        </div>

        <!-- Identity -->
        <div class="sa-card">
          <h2 class="sa-card__title">Identity</h2>
          <div class="sa-field">
            <label>Company Name</label>
            <input v-model="form.company_name" type="text" class="sa-input" placeholder="Bizaa" />
          </div>
          <div class="sa-field">
            <label>Tagline</label>
            <input v-model="form.tagline" type="text" class="sa-input" placeholder="Stock. Sale. Grow." />
          </div>
        </div>

        <!-- Contact -->
        <div class="sa-card">
          <h2 class="sa-card__title">Contact</h2>
          <div class="sa-field">
            <label>Support Email</label>
            <input v-model="form.support_email" type="email" class="sa-input" placeholder="support@example.com" />
          </div>
          <div class="sa-field">
            <label>Support Phone</label>
            <input v-model="form.support_phone" type="text" class="sa-input" placeholder="+254700000000" />
          </div>
          <div class="sa-field">
            <label>WhatsApp Number</label>
            <input v-model="form.whatsapp_number" type="text" class="sa-input" placeholder="254700000000" />
          </div>
          <div class="sa-field">
            <label>Address</label>
            <input v-model="form.address" type="text" class="sa-input" placeholder="Nairobi, Kenya" />
          </div>
        </div>

        <!-- Socials -->
        <div class="sa-card">
          <h2 class="sa-card__title">Social Links</h2>
          <div class="sa-field">
            <label>Facebook</label>
            <input v-model="form.facebook_url" type="url" class="sa-input" placeholder="https://facebook.com/…" />
          </div>
          <div class="sa-field">
            <label>Twitter / X</label>
            <input v-model="form.twitter_url" type="url" class="sa-input" placeholder="https://x.com/…" />
          </div>
          <div class="sa-field">
            <label>LinkedIn</label>
            <input v-model="form.linkedin_url" type="url" class="sa-input" placeholder="https://linkedin.com/…" />
          </div>
          <div class="sa-field">
            <label>Instagram</label>
            <input v-model="form.instagram_url" type="url" class="sa-input" placeholder="https://instagram.com/…" />
          </div>
        </div>

        <div class="sa-card sa-card--wide sa-save-row">
          <button type="submit" :disabled="saving" class="sa-save-btn">
            {{ saving ? "Saving…" : "Save Settings" }}
          </button>
        </div>
      </form>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import superAdminService from "@/services/SuperAdminService";
import { ASSET_BASE_URL } from "@/config";

const loading = ref(true);
const saving = ref(false);
const logoFile = ref(null);
const logoPreview = ref(null);
const logoPath = ref(null);
const BASE_URL = ASSET_BASE_URL;

const form = reactive({
  company_name: "",
  tagline: "",
  support_email: "",
  support_phone: "",
  whatsapp_number: "",
  address: "",
  facebook_url: "",
  twitter_url: "",
  linkedin_url: "",
  instagram_url: "",
});

const applySettings = (s) => {
  form.company_name = s.company_name || "";
  form.tagline = s.tagline || "";
  form.support_email = s.support_email || "";
  form.support_phone = s.support_phone || "";
  form.whatsapp_number = s.whatsapp_number || "";
  form.address = s.address || "";
  form.facebook_url = s.facebook_url || "";
  form.twitter_url = s.twitter_url || "";
  form.linkedin_url = s.linkedin_url || "";
  form.instagram_url = s.instagram_url || "";
  logoPath.value = s.logo || null;
  logoPreview.value = s.logo ? `${BASE_URL}/storage/${s.logo}` : null;
};

onMounted(async () => {
  try {
    const res = await superAdminService.getSettings();
    applySettings(res?.data ?? {});
  } finally {
    loading.value = false;
  }
});

const onLogoChange = (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  logoFile.value = file;
  logoPreview.value = URL.createObjectURL(file);
};

const save = async () => {
  saving.value = true;
  try {
    const fd = new FormData();
    Object.entries(form).forEach(([k, v]) => fd.append(k, v ?? ""));
    if (logoFile.value) fd.append("logo", logoFile.value);
    const res = await superAdminService.updateSettings(fd);
    applySettings(res?.data ?? {});
    logoFile.value = null;
    Swal.fire({ icon: "success", title: "Settings saved", toast: true, position: "top-end", showConfirmButton: false, timer: 2000, background: "#1e293b", color: "#e2e8f0" });
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save settings.", background: "#1e293b", color: "#e2e8f0", confirmButtonColor: "#3b82f6" });
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.sa-page-desc { color: #94a3b8; font-size: 13px; margin-bottom: 20px; max-width: 640px; line-height: 1.6; }
.sa-loading-full { text-align: center; color: #64748b; padding: 60px; }

.sa-section-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.sa-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
.sa-card--wide { grid-column: span 2; }
.sa-card__title {
  font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;
}

.sa-field { margin-bottom: 14px; }
.sa-field:last-child { margin-bottom: 0; }
.sa-field label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px; font-weight: 600; }
.sa-input {
  width: 100%; padding: 9px 12px; background: #0f172a; border: 1px solid #334155;
  border-radius: 8px; color: #e2e8f0; font-size: 13px; outline: none; transition: border-color 0.15s;
}
.sa-input:focus { border-color: #3b82f6; }
.sa-input::placeholder { color: #475569; }

.sa-logo-row { display: flex; align-items: center; gap: 16px; }
.sa-logo-preview {
  width: 88px; height: 88px; border-radius: 10px; background: #0f172a; border: 1px dashed #334155;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;
}
.sa-logo-preview img { width: 100%; height: 100%; object-fit: contain; }
.sa-logo-preview span { font-size: 11px; color: #475569; }
.sa-upload-btn {
  display: inline-block; padding: 8px 16px; background: #0f172a; border: 1px solid #334155;
  border-radius: 8px; color: #e2e8f0; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s;
}
.sa-upload-btn:hover { border-color: #3b82f6; color: #60a5fa; }
.sa-hidden-input { display: none; }
.sa-hint { font-size: 11px; color: #64748b; margin-top: 8px; }

.sa-save-row { display: flex; justify-content: flex-end; }
.sa-save-btn {
  padding: 10px 28px; background: #3b82f6; color: #fff; border: none; border-radius: 8px;
  font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.15s;
}
.sa-save-btn:hover:not(:disabled) { background: #2563eb; }
.sa-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 900px) {
  .sa-section-grid { grid-template-columns: 1fr; }
  .sa-card--wide { grid-column: span 1; }
}
</style>
