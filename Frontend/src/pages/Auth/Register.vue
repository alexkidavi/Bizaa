<template>
  <div class="min-h-screen flex w-full font-sans bg-[#F4F7FA] overflow-hidden">
    <!-- LEFT PANEL -->
    <div
      class="hidden lg:flex flex-col justify-between flex-1 p-16 relative text-white overflow-hidden"
      style="background: linear-gradient(135deg, #229de7 0%, #1565c0 100%)"
    >
      <div class="absolute inset-0">
        <div
          class="absolute top-[-150px] left-[-100px] w-[500px] h-[500px] bg-white/10 blur-[120px] rounded-full"
        ></div>
        <div
          class="absolute bottom-[-150px] right-[-100px] w-[400px] h-[400px] bg-black/10 blur-[120px] rounded-full"
        ></div>
      </div>

      <div class="relative z-10">
        <h1 class="text-5xl font-black leading-tight mb-6">
          Set Up Your<br />
          <span class="text-blue-200">Business in Minutes.</span>
        </h1>
        <p class="text-lg text-blue-50/90 max-w-md">
          Create your account, configure your business, and start selling right away with
          Bizaa.
        </p>
      </div>

      <!-- Step indicators -->
      <div class="relative z-10 space-y-3">
        <div
          v-for="(s, i) in steps"
          :key="i"
          class="flex items-center gap-4 p-4 rounded-2xl transition-all duration-300"
          :class="
            currentStep === i + 1
              ? 'bg-white/20 border border-white/20 backdrop-blur'
              : currentStep > i + 1
              ? 'opacity-60'
              : 'opacity-40'
          "
        >
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center font-extrabold text-sm flex-shrink-0"
            :class="
              currentStep > i + 1 ? 'bg-emerald-400 text-white' : 'bg-white/20 text-white'
            "
          >
            <svg
              v-if="currentStep > i + 1"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span v-else>{{ i + 1 }}</span>
          </div>
          <div>
            <div class="font-bold text-sm">{{ s.title }}</div>
            <div class="text-xs text-blue-100/70">{{ s.desc }}</div>
          </div>
        </div>
      </div>

      <div class="relative z-10 text-xs opacity-60 tracking-widest uppercase">
        Register • Configure • Sell • Grow
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div
      class="flex flex-1 items-center justify-center px-6 bg-white overflow-y-auto py-10"
    >
      <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
          <div class="flex items-center gap-3">
            <div
              class="h-11 w-11 flex items-center justify-center rounded-xl bg-[#229DE7] text-white"
            >
              <Receipt :size="24" :stroke-width="2.5" />
            </div>
            <div class="leading-tight">
              <div class="text-xl font-extrabold tracking-tight">
                Bi<span class="text-[#229DE7]">zaa</span>
              </div>
              <div class="text-xs text-slate-400 tracking-wide">Smart Retail POS</div>
            </div>
          </div>
        </div>

        <!-- Mobile step progress bar -->
        <div class="flex items-center gap-2 mb-6 lg:hidden">
          <div
            v-for="i in 2"
            :key="i"
            class="h-1.5 flex-1 rounded-full transition-all duration-300"
            :class="currentStep >= i ? 'bg-[#229DE7]' : 'bg-slate-200'"
          ></div>
        </div>

        <!-- Step header -->
        <div class="mb-7">
          <div class="text-xs font-bold text-[#229DE7] uppercase tracking-widest mb-1">
            Step {{ currentStep }} of 2
          </div>
          <h2 class="text-2xl font-bold text-slate-900">
            {{ steps[currentStep - 1].title }}
          </h2>
          <p class="text-sm text-slate-500 mt-1">{{ steps[currentStep - 1].desc }}</p>
        </div>

        <!-- Error banner -->
        <div
          v-if="errorMessage"
          class="mb-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-semibold"
        >
          {{ errorMessage }}
        </div>

        <!-- ── STEP 1: Account Info ── -->
        <form v-if="currentStep === 1" @submit.prevent="nextStep" class="space-y-4">
          <div>
            <label class="field-label required">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Jane Doe"
              class="field-input"
              :class="{ 'border-red-400': errors.name }"
            />
            <p v-if="errors.name" class="field-error">{{ errors.name }}</p>
          </div>

          <div>
            <label class="field-label required">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="jane@example.com"
              class="field-input"
              :class="{ 'border-red-400': errors.email }"
            />
            <p v-if="errors.email" class="field-error">{{ errors.email }}</p>
          </div>

          <div>
            <label class="field-label required">Phone Number</label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="0712345678 or 254712345678"
              class="field-input"
              :class="{ 'border-red-400': errors.phone }"
              @input="form.phone = form.phone.replace(/[^\d+]/g, '')"
            />
            <p v-if="errors.phone" class="field-error">{{ errors.phone }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-semibold">
              Format: 07xxxxxxxx (10 digits) or 254xxxxxxxxx (12 digits)
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="field-label required">Password</label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Min. 8 characters"
                  class="field-input pr-11"
                  :class="{ 'border-red-400': errors.password }"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-3.5 text-slate-400 hover:text-[#229DE7]"
                >
                  <component :is="showPassword ? EyeOffIcon : EyeIcon" :size="18" />
                </button>
              </div>
              <p v-if="errors.password" class="field-error">{{ errors.password }}</p>
            </div>
            <div>
              <label class="field-label required">Confirm</label>
              <input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Repeat password"
                class="field-input"
                :class="{ 'border-red-400': errors.password_confirmation }"
              />
              <p v-if="errors.password_confirmation" class="field-error">
                {{ errors.password_confirmation }}
              </p>
            </div>
          </div>

          <button type="submit" class="submit-btn mt-2">Continue →</button>
        </form>

        <!-- ── STEP 2: Business Info ── -->
        <form v-else @submit.prevent="register" class="space-y-4">
          <div>
            <label class="field-label required">Business Name</label>
            <input
              v-model="form.business_name"
              type="text"
              placeholder="Brimsoft Ltd"
              class="field-input"
              :class="{ 'border-red-400': errors.business_name }"
            />
            <p v-if="errors.business_name" class="field-error">
              {{ errors.business_name }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="field-label required">Business Email</label>
              <input
                v-model="form.business_email"
                type="email"
                placeholder="info@biz.co.ke"
                class="field-input"
                :class="{ 'border-red-400': errors.business_email }"
              />
              <p v-if="errors.business_email" class="field-error">
                {{ errors.business_email }}
              </p>
            </div>
            <div>
              <label class="field-label required">Business Phone</label>
              <input
                v-model="form.business_phone"
                type="tel"
                placeholder="0712345678"
                class="field-input"
                :class="{ 'border-red-400': errors.business_phone }"
                @input="form.business_phone = form.business_phone.replace(/[^\d+]/g, '')"
              />
              <p v-if="errors.business_phone" class="field-error">
                {{ errors.business_phone }}
              </p>
            </div>
          </div>

          <div>
            <label class="field-label required">Industry</label>
            <select
              v-model="form.business_industry"
              class="field-input"
              :class="{ 'border-red-400': errors.business_industry }"
            >
              <option value="" disabled>Select industry...</option>
              <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
            </select>
            <p v-if="errors.business_industry" class="field-error">
              {{ errors.business_industry }}
            </p>
          </div>

          <div>
            <label class="field-label required">Location</label>
            <input
              v-model="form.business_location"
              type="text"
              placeholder="Nairobi, Kenya"
              class="field-input"
              :class="{ 'border-red-400': errors.business_location }"
            />
            <p v-if="errors.business_location" class="field-error">
              {{ errors.business_location }}
            </p>
          </div>

          <div>
            <label class="field-label"
              >Website
              <span class="text-slate-400 font-normal lowercase">(Optional)</span></label
            >
            <input
              v-model="form.business_website"
              type="url"
              placeholder="https://example.com"
              class="field-input"
              :class="{ 'border-red-400': errors.business_website }"
            />
            <p v-if="errors.business_website" class="field-error">
              {{ errors.business_website }}
            </p>
          </div>

          <div class="flex gap-3 mt-2">
            <button type="button" @click="currentStep = 1" class="back-btn">
              ← Back
            </button>
            <button type="submit" :disabled="loading" class="submit-btn flex-1">
              <svg
                v-if="loading"
                class="animate-spin h-4 w-4 text-white inline mr-2"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              {{ loading ? "Creating account..." : "Create Account" }}
            </button>
          </div>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Already have an account?
          <router-link to="/login" class="font-bold text-[#229DE7] hover:underline"
            >Sign in</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { Receipt, EyeIcon, EyeOffIcon } from "lucide-vue-next";
import RegisterService from "@/services/RegisterService";
import axios from "axios";
import Swal from "sweetalert2";
const router = useRouter();
const currentStep = ref(1);
const loading = ref(false);
const errorMessage = ref("");
const showPassword = ref(false);

const steps = [
  { title: "Your Account", desc: "Personal login credentials" },
  { title: "Business Details", desc: "Tell us about your business" },
];

const industries = [
  "Retail & General Trade",
  "Supermarket / Grocery",
  "Pharmacy & Health",
  "Electronics & Appliances",
  "Fashion & Clothing",
  "Restaurant & Food Service",
  "Hardware & Construction",
  "Automotive & Spare Parts",
  "Wholesale & Distribution",
  "Beauty & Cosmetics",
  "Other",
];

const form = reactive({
  name: "",
  email: "",
  phone: "",
  password: "",
  password_confirmation: "",
  business_name: "",
  business_email: "",
  business_phone: "",
  business_industry: "",
  business_location: "",
  business_website: "",
});

const errors = reactive({});

const clearErrors = () => Object.keys(errors).forEach((k) => delete errors[k]);

const validatePhone = (phone) => {
  const digits = phone.replace(/\D/g, "");
  if (/^(254|255|256)\d{9}$/.test(digits)) {
    return null;
  }
  if (/^0\d{9}$/.test(digits)) {
    return null;
  }

  return "Enter a valid phone number (e.g. 07XXXXXXXX or 254XXXXXXXXX)";
};

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const validateStep1 = () => {
  clearErrors();
  let valid = true;

  if (!form.name.trim()) {
    errors.name = "Full name is required";
    valid = false;
  }

  if (!emailRegex.test(form.email)) {
    errors.email = "Enter a valid email";
    valid = false;
  }

  const phoneErr = validatePhone(form.phone);
  if (phoneErr) {
    errors.phone = phoneErr;
    valid = false;
  }

  if (form.password.length < 8) {
    errors.password = "Minimum 8 characters";
    valid = false;
  }

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = "Passwords do not match";
    valid = false;
  }

  return valid;
};

const validateStep2 = () => {
  clearErrors();
  let valid = true;

  if (!form.business_name.trim()) {
    errors.business_name = "Business name is required";
    valid = false;
  }

  if (!emailRegex.test(form.business_email)) {
    errors.business_email = "Enter a valid business email";
    valid = false;
  }

  const bizPhoneErr = validatePhone(form.business_phone);
  if (bizPhoneErr) {
    errors.business_phone = bizPhoneErr;
    valid = false;
  }

  if (!form.business_industry) {
    errors.business_industry = "Select an industry";
    valid = false;
  }

  if (!form.business_location.trim()) {
    errors.business_location = "Location is required";
    valid = false;
  }

  return valid;
};

const nextStep = () => {
  errorMessage.value = "";
  if (validateStep1()) currentStep.value = 2;
};

const register = async () => {
  errorMessage.value = "";
  if (!validateStep2()) return;

  loading.value = true;
  try {
    await RegisterService.register({
      name: form.name,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.password_confirmation,
      business_name: form.business_name,
      business_email: form.business_email,
      business_phone: form.business_phone,
      business_industry: form.business_industry,
      business_location: form.business_location,
      business_website: form.business_website || null,
    });

    // 🎉 success alert with countdown
    let timer = 5;

    Swal.fire({
      title: "Account Created Successfully",
      html: `Redirecting to login in <b>${timer}</b> seconds...`,
      icon: "success",
      timer: 5000,
      timerProgressBar: true,
      showConfirmButton: false,
      allowOutsideClick: false,
      didOpen: () => {
        const interval = setInterval(() => {
          timer--;
          if (timer > 0) {
            Swal.getHtmlContainer().querySelector("b").textContent = timer;
          } else {
            clearInterval(interval);
          }
        }, 1000);
      },
    }).then(() => {
      router.push("/login");
    });
  } catch (err) {
    const data = err.response?.data;
    if (err.response?.status === 422 && data?.errors) {
      Object.entries(data.errors).forEach(([field, msgs]) => {
        errors[field] = msgs[0];
      });
      const step1Fields = ["name", "email", "phone", "password", "password_confirmation"];
      if (step1Fields.some((f) => errors[f])) {
        currentStep.value = 1;
      }
      errorMessage.value = "Please fix the highlighted fields.";
    } else {
      errorMessage.value = data?.message || "Registration failed. Please try again.";
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
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}
.required::after {
  content: " *";
  color: #ef4444;
}
.field-input {
  width: 100%;
  padding: 12px 16px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.1);
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
.submit-btn {
  width: 100%;
  padding: 14px;
  background: #229de7;
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  border-radius: 10px;
  transition: background 0.15s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(34, 157, 231, 0.2);
  cursor: pointer;
}
.submit-btn:hover {
  background: #1b8bc9;
}
.submit-btn:active {
  transform: scale(0.99);
}
.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.back-btn {
  padding: 14px 20px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  color: #64748b;
  background: white;
  cursor: pointer;
  white-space: nowrap;
  transition: border-color 0.15s, color 0.15s;
}
.back-btn:hover {
  border-color: #229de7;
  color: #229de7;
}
</style>
