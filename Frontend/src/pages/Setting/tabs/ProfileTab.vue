<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- LEFT: Avatar -->
    <aside class="lg:col-span-4">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="p-6 flex flex-col items-center gap-4">
          <img
            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(form.name || 'U')}&background=229DE7&color=fff&bold=true&size=128`"
            class="w-24 h-24 rounded-2xl border-4 border-[#EAF5FD] object-cover"
            alt="Avatar"
          />
          <div class="text-center">
            <p class="text-base font-extrabold text-slate-900">{{ form.name || "—" }}</p>
            <p class="text-sm text-slate-400 font-medium mt-0.5">{{ form.email || "—" }}</p>
          </div>
          <RouterLink
            to="/profile/password"
            class="w-full text-center px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition"
          >
            Change Password
          </RouterLink>
        </div>
      </div>
    </aside>

    <!-- RIGHT: Form -->
    <main class="lg:col-span-8">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Personal Information
          </h2>
        </div>

        <form @submit.prevent="save" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="field-label required">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                class="field-input"
                :class="{ 'border-red-400': vErrors.name }"
                placeholder="e.g. Jane Doe"
              />
              <p v-if="vErrors.name" class="field-error">Name is required</p>
            </div>

            <div>
              <label class="field-label required">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="field-input"
                :class="{ 'border-red-400': vErrors.email }"
                placeholder="e.g. jane@example.com"
              />
              <p v-if="vErrors.email" class="field-error">{{ vErrors.emailMsg }}</p>
            </div>

            <div>
              <label class="field-label required">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                class="field-input"
                :class="{ 'border-red-400': vErrors.phone }"
                placeholder="e.g. +254 712 345 678"
              />
              <p v-if="vErrors.phone" class="field-error">Phone number is required</p>
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
    </main>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import Swal from "sweetalert2";
import ProfileService from "@/services/ProfileService";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const loading = ref(false);
const pageLoading = ref(true);

const form = reactive({ name: "", email: "", phone: "" });
const vErrors = reactive({ name: false, email: false, emailMsg: "", phone: false });

onMounted(async () => {
  try {
    const res = await ProfileService.getProfile();
    const data = res?.data ?? {};
    form.name = data.name || "";
    form.email = data.email || "";
    form.phone = data.phone || "";
  } catch {
    Swal.fire("Error", "Could not load your profile.", "error");
  } finally {
    pageLoading.value = false;
  }
});

const validate = () => {
  vErrors.name = !form.name.trim();
  const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
  vErrors.email = !form.email.trim() || !emailOk;
  vErrors.emailMsg = !form.email.trim() ? "Email is required" : "Enter a valid email address";
  vErrors.phone = !form.phone.trim();
  return !vErrors.name && !vErrors.email && !vErrors.phone;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const res = await ProfileService.updateProfile(form);
    Swal.fire({
      icon: "success",
      title: res?.data?.message || "Profile updated successfully.",
      confirmButtonColor: "#229DE7",
    });
    await auth.refreshUser();
  } catch (err) {
    const res = err?.response;
    if (res?.status === 422 && res.data?.errors) {
      if (res.data.errors.name) vErrors.name = true;
      if (res.data.errors.email) {
        vErrors.email = true;
        vErrors.emailMsg = res.data.errors.email[0];
      }
      if (res.data.errors.phone) vErrors.phone = true;
      Swal.fire({ icon: "error", title: "Validation Error", text: "Please fix the highlighted fields.", confirmButtonColor: "#229DE7" });
    } else {
      Swal.fire("Error", res?.data?.message || "Something went wrong.", "error");
    }
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
