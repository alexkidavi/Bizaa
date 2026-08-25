<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Change Password</h1>
        <p class="text-slate-500 mt-1 text-sm">Update your account password. Use a strong, unique password.</p>
      </div>
      <RouterLink
        to="/profile"
        class="inline-flex items-center gap-2 px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition"
      >
        <UserIcon class="w-4 h-4" />
        Back to Profile
      </RouterLink>
    </div>

    <div class="max-w-xl">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Update Password</h2>
        </div>

        <!-- Info banner -->
        <div class="mx-6 mt-5 flex items-start gap-3 bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-4">
          <svg class="w-4 h-4 text-[#229DE7] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-xs font-semibold text-slate-600">
            Your new password must be at least <strong>8 characters</strong> long.
            Choose something that's hard to guess but easy for you to remember.
          </p>
        </div>

        <form @submit.prevent="submit" class="p-6 flex flex-col gap-5">
          <!-- Current Password -->
          <div>
            <label class="field-label required">Current Password</label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input
                v-model="form.current_password"
                :type="show.current ? 'text' : 'password'"
                class="field-input pl-10 pr-10"
                :class="{ 'border-red-400 focus:border-red-400': errors.current_password }"
                placeholder="Enter your current password"
                autocomplete="current-password"
              />
              <button
                type="button"
                @click="show.current = !show.current"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
              >
                <EyeSlashIcon v-if="show.current" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="errors.current_password" class="field-error">{{ errors.current_password }}</p>
          </div>

          <!-- New Password -->
          <div>
            <label class="field-label required">New Password</label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input
                v-model="form.new_password"
                :type="show.new ? 'text' : 'password'"
                class="field-input pl-10 pr-10"
                :class="{ 'border-red-400 focus:border-red-400': errors.new_password }"
                placeholder="Enter new password"
                autocomplete="new-password"
              />
              <button
                type="button"
                @click="show.new = !show.new"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
              >
                <EyeSlashIcon v-if="show.new" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="errors.new_password" class="field-error">{{ errors.new_password }}</p>
            <!-- Strength indicator -->
            <div v-if="form.new_password" class="mt-2 flex items-center gap-2">
              <div class="flex gap-1 flex-1">
                <div
                  v-for="i in 4"
                  :key="i"
                  class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                  :class="i <= strength.score ? strength.color : 'bg-slate-200'"
                ></div>
              </div>
              <span class="text-[10.5px] font-bold" :class="strength.textColor">{{ strength.label }}</span>
            </div>
          </div>

          <!-- Confirm New Password -->
          <div>
            <label class="field-label required">Confirm New Password</label>
            <div class="relative">
              <LockClosedIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input
                v-model="form.new_password_confirmation"
                :type="show.confirm ? 'text' : 'password'"
                class="field-input pl-10 pr-10"
                :class="{ 'border-red-400 focus:border-red-400': errors.new_password_confirmation }"
                placeholder="Repeat new password"
                autocomplete="new-password"
              />
              <button
                type="button"
                @click="show.confirm = !show.confirm"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
              >
                <EyeSlashIcon v-if="show.confirm" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="errors.new_password_confirmation" class="field-error">{{ errors.new_password_confirmation }}</p>
            <!-- Match indicator -->
            <p
              v-if="form.new_password_confirmation && form.new_password"
              class="mt-1.5 text-[10.5px] font-bold flex items-center gap-1"
              :class="passwordsMatch ? 'text-emerald-600' : 'text-red-500'"
            >
              <CheckCircleIcon v-if="passwordsMatch" class="w-3.5 h-3.5" />
              <XCircleIcon v-else class="w-3.5 h-3.5" />
              {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
            </p>
          </div>

          <!-- Divider -->
          <div class="border-t border-[#EEF5FB]"></div>

          <!-- Actions -->
          <div class="flex items-center justify-between gap-4">
            <button
              type="button"
              @click="resetForm"
              class="px-5 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-500 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition"
            >
              Clear
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="flex items-center justify-center gap-2 px-8 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              <LockClosedIcon v-else class="w-4 h-4" />
              {{ loading ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import {
  LockClosedIcon,
  UserIcon,
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2'
import ProfileService from '@/services/ProfileService'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const errors = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const show = reactive({ current: false, new: false, confirm: false })

const passwordsMatch = computed(
  () => form.new_password && form.new_password_confirmation && form.new_password === form.new_password_confirmation
)

const strength = computed(() => {
  const p = form.new_password
  if (!p) return { score: 0, label: '', color: '', textColor: '' }
  let score = 0
  if (p.length >= 8)  score++
  if (p.length >= 12) score++
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++
  if (/[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)) score++
  const map = [
    { label: 'Weak',   color: 'bg-red-400',    textColor: 'text-red-500' },
    { label: 'Fair',   color: 'bg-orange-400',  textColor: 'text-orange-500' },
    { label: 'Good',   color: 'bg-yellow-400',  textColor: 'text-yellow-600' },
    { label: 'Strong', color: 'bg-emerald-500', textColor: 'text-emerald-600' },
  ]
  return { score, ...map[Math.min(score, 3)] }
})

const validate = () => {
  let ok = true
  errors.current_password = ''
  errors.new_password = ''
  errors.new_password_confirmation = ''

  if (!form.current_password) {
    errors.current_password = 'Current password is required'
    ok = false
  }
  if (!form.new_password) {
    errors.new_password = 'New password is required'
    ok = false
  } else if (form.new_password.length < 8) {
    errors.new_password = 'Password must be at least 8 characters'
    ok = false
  }
  if (!form.new_password_confirmation) {
    errors.new_password_confirmation = 'Please confirm your new password'
    ok = false
  } else if (form.new_password !== form.new_password_confirmation) {
    errors.new_password_confirmation = 'Passwords do not match'
    ok = false
  }
  return ok
}

const submit = async () => {
  if (!validate()) return
  loading.value = true
  try {
    await ProfileService.changePassword({
      current_password: form.current_password,
      new_password: form.new_password,
      new_password_confirmation: form.new_password_confirmation,
    })
    await Swal.fire({
      icon: 'success',
      title: 'Password updated!',
      text: 'Your password has been changed successfully.',
      confirmButtonColor: '#229DE7',
    })
    router.push('/profile')
  } catch (err) {
    const status = err?.response?.status
    const data   = err?.response?.data

    if (status === 422) {
      const serverErrors = data?.errors ?? {}
      if (serverErrors.current_password) errors.current_password = serverErrors.current_password[0]
      if (serverErrors.new_password)     errors.new_password     = serverErrors.new_password[0]
      // Backend returns 422 with message for wrong current password
      if (data?.message && !serverErrors.current_password) {
        errors.current_password = data.message
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data?.message || 'Something went wrong.',
        confirmButtonColor: '#229DE7',
      })
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.current_password = ''
  form.new_password = ''
  form.new_password_confirmation = ''
  Object.keys(errors).forEach(k => (errors[k] = ''))
}
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
