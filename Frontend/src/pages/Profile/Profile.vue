<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">My Profile</h1>
        <p class="text-slate-500 mt-1 text-sm">View and manage your account information.</p>
      </div>
      <RouterLink
        to="/profile/password"
        class="inline-flex items-center gap-2 px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition"
      >
        <LockClosedIcon class="w-4 h-4" />
        Change Password
      </RouterLink>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="w-8 h-8 border-4 border-[#229DE7] border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- LEFT: Avatar Card -->
      <aside class="lg:col-span-4 flex flex-col gap-5">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="p-6 flex flex-col items-center gap-4">
            <!-- Avatar -->
            <div class="relative">
              <img
                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'U')}&background=229DE7&color=fff&bold=true&size=128`"
                class="w-24 h-24 rounded-2xl border-4 border-[#EAF5FD] object-cover"
                alt="Avatar"
              />
              <span
                class="absolute -bottom-1.5 -right-1.5 w-5 h-5 rounded-full border-2 border-white"
                :class="'bg-emerald-500'"
              ></span>
            </div>

            <div class="text-center">
              <p class="text-base font-extrabold text-slate-900">{{ user?.name || '—' }}</p>
              <p class="text-sm text-slate-400 font-medium mt-0.5">{{ user?.email || '—' }}</p>
            </div>

            <!-- Business badge -->
            <div
              v-if="user?.business"
              class="w-full bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl px-4 py-3 text-center"
            >
              <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Business</p>
              <p class="text-sm font-extrabold text-[#1682C4]">{{ user.business.name }}</p>
            </div>
          </div>
        </div>

        <!-- Permissions Card -->
        <div
          v-if="user?.permissions?.length"
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Permissions</h2>
          </div>
          <div class="p-4 flex flex-wrap gap-1.5">
            <span
              v-for="perm in user.permissions.slice(0, 24)"
              :key="perm"
              class="inline-flex items-center px-2.5 py-1 bg-[#EAF5FD] text-[#1682C4] text-[10.5px] font-bold rounded-lg"
            >
              {{ perm }}
            </span>
            <span
              v-if="user.permissions.length > 24"
              class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-400 text-[10.5px] font-bold rounded-lg"
            >
              +{{ user.permissions.length - 24 }} more
            </span>
          </div>
        </div>
      </aside>

      <!-- RIGHT: Account Details -->
      <main class="lg:col-span-8 flex flex-col gap-5">
        <!-- Account Info Card -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Account Details</h2>
          </div>
          <div class="divide-y divide-[#EEF5FB]">
            <InfoRow icon="user" label="Full Name" :value="user?.name" />
            <InfoRow icon="email" label="Email Address" :value="user?.email" />
            <InfoRow icon="phone" label="Phone Number" :value="user?.phone || 'Not set'" />
          </div>
        </div>

        <!-- Business Info Card -->
        <div
          v-if="user?.business"
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Business Information</h2>
            <RouterLink
              to="/settings/business"
              class="text-xs font-bold text-[#229DE7] hover:text-[#1682C4] transition underline underline-offset-2"
            >
              Edit Settings
            </RouterLink>
          </div>
          <div class="divide-y divide-[#EEF5FB]">
            <InfoRow icon="business" label="Business Name" :value="user.business.name" />
            <InfoRow icon="email" label="Business Email" :value="user.business.email || 'Not set'" />
            <InfoRow icon="phone" label="Business Phone" :value="user.business.phone || 'Not set'" />
            <InfoRow icon="location" label="Location" :value="user.business.location || 'Not set'" />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { LockClosedIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import Swal from 'sweetalert2'

const auth = useAuthStore()
const loading = ref(false)
const user = computed(() => auth.user)

onMounted(async () => {
  if (!auth.user) {
    loading.value = true
    try {
      await auth.refreshUser()
    } catch {
      Swal.fire('Error', 'Could not load profile.', 'error')
    } finally {
      loading.value = false
    }
  }
})
</script>

<!-- Inline sub-component to avoid a separate file -->
<script>
import { defineComponent, h } from 'vue'

const icons = {
  user: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
  email: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
  phone: 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z',
  business: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
  location: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
}

export const InfoRow = defineComponent({
  name: 'InfoRow',
  props: {
    icon: { type: String, default: 'user' },
    label: { type: String, required: true },
    value: { type: String, default: '—' },
  },
  render() {
    const path = icons[this.icon] || icons.user
    return h('div', { class: 'flex items-center gap-4 px-6 py-4' }, [
      h('div', { class: 'w-9 h-9 rounded-xl bg-[#EAF5FD] flex items-center justify-center flex-shrink-0' }, [
        h('svg', { class: 'w-4 h-4 text-[#229DE7]', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.8', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: path }),
        ]),
      ]),
      h('div', {}, [
        h('p', { class: 'text-[10.5px] font-bold uppercase tracking-widest text-slate-400' }, this.label),
        h('p', { class: 'text-sm font-bold text-slate-800 mt-0.5' }, this.value || '—'),
      ]),
    ])
  },
})
</script>
