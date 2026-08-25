<template>
  <div class="min-h-screen flex w-full">
    <!-- Left panel -->
    <div class="hidden md:flex flex-col text-white relative flex-1 bg-primary-500">
      <div class="absolute inset-0 z-0">
        <img
          src="/img/login-bg.png"
          alt="Ngumzo CRM background"
          class="w-full h-full object-cover"
        />
      </div>

      <!-- Intro Text -->
      <div class="absolute top-[30%] w-full z-10 text-center px-16">
        <p class="text-l font-semibold mb-2">Start your journey with us!</p>
        <div class="text-5xl font-semibold tracking-tight mb-4 text-primary-500">
          CREATE ACCOUNT
        </div>
        <p class="text-sm px-8 leading-relaxed">
          Sign up to manage your customers, track leads, and streamline your business
          operations — Ngumzo CRM helps you stay organized and efficient.
        </p>
      </div>
    </div>

    <!-- Right panel -->
    <div class="bg-white flex flex-col items-center flex-1 py-4 px-4 sm:px-6 lg:px-8">
      <div class="w-full max-w-2xl">
        <div class="flex flex-col items-center mb-6">
          <img src="/logo.png" alt="Ngumzo CRM Logo" class="h-20 w-auto mb-4" />
          <h2 class="text-2xl font-semibold text-gray-800">Create Account</h2>
          <p class="text-sm px-8 leading-relaxed text-center">
            Sign up to get started with NgumzoCRM
          </p>
          <p v-if="successMessage" class="text-sm text-green-600 mt-2 text-center">
            {{ successMessage }}
          </p>
          <p v-if="errorMessage" class="text-sm text-red-600 mt-2 text-center">
            {{ errorMessage }}
          </p>
        </div>

        <form @submit.prevent="handleRegister" class="mt-8 space-y-6 w-full">
          <!-- Personal Info -->
          <fieldset class="border border-gray-300 rounded-md p-6 shadow-sm w-full">
            <legend class="text-lg font-semibold text-gray-700 px-2">
              Personal Details
            </legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 w-full">
              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  First Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="firstName"
                  type="text"
                  placeholder="John"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Last Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="lastName"
                  type="text"
                  placeholder="Doe"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Email Address <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="email"
                  type="email"
                  placeholder="john.doe@example.com"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Mobile Number <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="phone"
                  type="tel"
                  placeholder="254712345678"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Password <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="password"
                  :type="passwordVisible ? 'text' : 'password'"
                  placeholder="At least 8 characters"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm pr-12"
                />
                <button
                  type="button"
                  @click="togglePasswordVisibility"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 pt-5"
                >
                  <svg
                    v-if="!passwordVisible"
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  <svg
                    v-else
                    class="h-5 w-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.487 0 2.906.331 4.25 1"
                    />
                  </svg>
                </button>
              </div>

              <div class="w-full relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Confirm Password <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="confirmPassword"
                  :type="passwordVisible ? 'text' : 'password'"
                  placeholder="Re-type your password"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm pr-12"
                />
              </div>
            </div>
          </fieldset>

          <!-- Business Info -->
          <fieldset class="border border-gray-300 rounded-md p-6 shadow-sm w-full">
            <legend class="text-lg font-semibold text-gray-700 px-2">
              Business Details
            </legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 w-full">
              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="businessName"
                  type="text"
                  placeholder="Ngumzo Solutions"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Industry <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="businessIndustry"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                >
                  <option value="">Select Industry</option>
                  <option
                    v-for="industry in industries"
                    :key="industry.id"
                    :value="industry.name"
                  >
                    {{ industry.name }}
                  </option>
                </select>
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="businessEmail"
                  type="email"
                  placeholder="contact@business.com"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Phone <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="businessPhone"
                  type="tel"
                  placeholder="254700123456"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Website <span class="text-gray-400">(Optional)</span>
                </label>
                <input
                  v-model="businessWebsite"
                  type="url"
                  placeholder="https://example.com"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Location (County) <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="businessLocation"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                >
                  <option value="">Select County</option>
                  <option
                    v-for="county in counties"
                    :key="county.id"
                    :value="county.name"
                  >
                    {{ county.name }}
                  </option>
                </select>
              </div>

              <!-- Business Logo -->
              <div class="w-full md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Business Logo <span class="text-gray-400">(Optional)</span>
                </label>
                <div class="flex items-center space-x-4">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handleLogoUpload"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                  />
                  <img
                    v-if="businessLogoPreview"
                    :src="businessLogoPreview"
                    alt="Business Logo Preview"
                    class="h-12 w-12 object-cover rounded-full border"
                  />
                </div>
              </div>
            </div>
          </fieldset>

          <div>
            <button
              :disabled="loading"
              type="submit"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow text-base font-medium text-white bg-primary-500 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <span v-if="loading">Registering...</span>
              <span v-else>Register</span>
            </button>
          </div>

          <p class="text-sm text-center text-gray-600 mt-4">
            Already have an account?
            <router-link
              to="/login"
              class="text-primary-500 hover:text-primary-700 font-medium"
            >
              Login
            </router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toast-notification";
import RegisterService from "@/services/registerService";

// Personal info
const firstName = ref("");
const lastName = ref("");
const email = ref("");
const phone = ref("");
const password = ref("");
const confirmPassword = ref("");
const passwordVisible = ref(false);

// Business info
const businessName = ref("");
const businessIndustry = ref("");
const businessEmail = ref("");
const businessPhone = ref("");
const businessWebsite = ref("");
const businessLocation = ref("");
const businessLogo = ref(null);
const businessLogoPreview = ref(null);

// Options
const industries = ref([]);
const counties = ref([]);

// Status
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const router = useRouter();
const $toast = useToast();

const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value;
};

const handleLogoUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    businessLogo.value = file;
    businessLogoPreview.value = URL.createObjectURL(file);
  }
};

const fetchBusinessOptions = async () => {
  try {
    const industriesRes = await RegisterService.getIndustries();
    const countiesRes = await RegisterService.getCounties();
    industries.value = industriesRes.data || [];
    counties.value = countiesRes.data || [];
  } catch (error) {
    console.error("Failed to fetch business options", error);
  }
};

onMounted(() => fetchBusinessOptions());

const resetForm = () => {
  firstName.value = "";
  lastName.value = "";
  email.value = "";
  phone.value = "";
  password.value = "";
  confirmPassword.value = "";
  businessName.value = "";
  businessIndustry.value = "";
  businessEmail.value = "";
  businessPhone.value = "";
  businessWebsite.value = "";
  businessLocation.value = "";
  businessLogo.value = null;
  businessLogoPreview.value = null;
};

const handleRegister = async () => {
  errorMessage.value = "";
  successMessage.value = "";

  if (
    !firstName.value ||
    !lastName.value ||
    !email.value ||
    !phone.value ||
    !password.value ||
    !confirmPassword.value ||
    !businessName.value ||
    !businessIndustry.value ||
    !businessEmail.value ||
    !businessPhone.value ||
    !businessLocation.value
  ) {
    errorMessage.value = "Please fill in all required fields.";
    return;
  }

  const phonePattern = /^(?:254\d{9}|0[17]\d{8})$/;
  if (!phonePattern.test(phone.value)) {
    errorMessage.value = "Invalid phone number format.";
    return;
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = "Passwords do not match.";
    return;
  }

  loading.value = true;

  try {
    const formData = new FormData();
    formData.append("firstName", firstName.value);
    formData.append("lastName", lastName.value);
    formData.append("email", email.value);
    formData.append("phone", phone.value);
    formData.append("password", password.value);
    formData.append("password_confirmation", confirmPassword.value);
    formData.append("businessName", businessName.value);
    formData.append("businessIndustry", businessIndustry.value);
    formData.append("businessEmail", businessEmail.value);
    formData.append("businessPhone", businessPhone.value);
    formData.append("businessWebsite", businessWebsite.value);
    formData.append("businessLocation", businessLocation.value);

    if (businessLogo.value) {
      formData.append("businessLogo", businessLogo.value);
    }

    const response = await RegisterService.post(formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.status === 201 || response.data?.status === "success") {
      successMessage.value = "Registration successful! Redirecting to login...";
      resetForm();

      // ✅ Redirect after 5 seconds
      setTimeout(() => {
        router.push("/login");
      }, 5000);
    } else {
      errorMessage.value = response.data?.message || "Registration failed.";
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || "Network error.";
  } finally {
    loading.value = false;
  }
};
</script>
