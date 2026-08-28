<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Customer Loyalty</h1>
        <p class="text-slate-500 mt-1 text-sm">Points balances, redemption history, and manual adjustments.</p>
      </div>
      <router-link to="/settings?tab=loyalty" class="text-xs font-bold text-[#229DE7] hover:text-[#1682C4] transition">
        Loyalty Settings →
      </router-link>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between gap-4">
        <span class="font-bold text-sm text-slate-700">{{ total }} customer(s)</span>
        <input
          v-model="search"
          @input="debouncedSearch"
          type="text"
          placeholder="Search by name or phone..."
          class="field-input !w-64"
        />
      </div>

      <div v-if="loading" class="p-6 space-y-3">
        <div v-for="n in 5" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
      </div>
      <div v-else-if="customers.length === 0" class="py-20 text-center">
        <p class="font-bold text-slate-700">No customers found</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5">Customer</th>
              <th class="px-5 py-3.5">Phone</th>
              <th class="px-5 py-3.5 text-center">Points Balance</th>
              <th class="px-5 py-3.5 text-right">Cash Value</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="c in customers" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors cursor-pointer" @click="openCustomer(c)">
              <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ c.name }}</td>
              <td class="px-5 py-4 text-sm text-slate-600">{{ c.phone || '—' }}</td>
              <td class="px-5 py-4 text-center">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="c.loyalty_points_balance > 0 ? 'bg-[#EAF5FD] text-[#1682C4]' : 'bg-slate-100 text-slate-500'">
                  {{ c.loyalty_points_balance }} pts
                </span>
              </td>
              <td class="px-5 py-4 text-right text-sm font-bold text-slate-700">{{ money(c.loyalty_points_balance * pointValue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="lastPage > 1" class="px-6 py-4 border-t border-[#EEF5FB] flex items-center justify-between text-xs">
        <span class="text-slate-400">Page {{ page }} of {{ lastPage }}</span>
        <div class="flex gap-2">
          <button :disabled="page <= 1" @click="page--; load()" class="px-3 py-1.5 rounded-lg font-bold border border-[#D9EBF7] disabled:opacity-40">Prev</button>
          <button :disabled="page >= lastPage" @click="page++; load()" class="px-3 py-1.5 rounded-lg font-bold border border-[#D9EBF7] disabled:opacity-40">Next</button>
        </div>
      </div>
    </div>

    <!-- DETAIL MODAL -->
    <div v-if="activeCustomer" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="closeCustomer">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-start justify-between">
          <div>
            <h2 class="font-extrabold text-lg text-slate-900">{{ activeCustomer.name }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ activeCustomer.phone }}</p>
          </div>
          <button @click="closeCustomer" class="text-slate-400 hover:text-red-500 text-xs font-bold">Close</button>
        </div>

        <div class="p-6 space-y-5 overflow-y-auto">
          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-[#F5FAFF] rounded-xl">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Current Balance</p>
              <p class="text-2xl font-extrabold text-[#1682C4] mt-1">{{ activeCustomer.loyalty_points_balance }} pts</p>
              <p class="text-xs text-slate-500 mt-0.5">≈ {{ money(activeCustomer.loyalty_points_balance * pointValue) }}</p>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Adjust Points</p>
              <form @submit.prevent="submitAdjust" class="space-y-2">
                <input v-model.number="adjustForm.points" type="number" step="1" placeholder="e.g. 10 or -5" class="field-input !py-1.5 !text-xs" required />
                <input v-model="adjustForm.note" type="text" placeholder="Reason (required)" class="field-input !py-1.5 !text-xs" required maxlength="255" />
                <button type="submit" :disabled="adjusting" class="w-full py-1.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-lg transition disabled:opacity-60">
                  {{ adjusting ? 'Saving...' : 'Apply Adjustment' }}
                </button>
              </form>
            </div>
          </div>

          <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">History</p>
            <div v-if="historyLoading" class="py-8 text-center text-slate-400 text-xs">Loading…</div>
            <div v-else-if="history.length === 0" class="py-8 text-center text-slate-400 text-xs">No transactions yet</div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="text-slate-400 uppercase text-[10px] font-bold border-b border-[#EEF5FB]">
                    <th class="py-2 pr-3">Date</th>
                    <th class="py-2 pr-3">Type</th>
                    <th class="py-2 pr-3 text-right">Points</th>
                    <th class="py-2 pr-3 text-right">Balance</th>
                    <th class="py-2">Note</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF5FB]">
                  <tr v-for="h in history" :key="h.id">
                    <td class="py-2 pr-3 text-slate-500 whitespace-nowrap">{{ formatDate(h.created_at) }}</td>
                    <td class="py-2 pr-3">
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" :class="typeClass(h.type)">{{ h.type }}</span>
                    </td>
                    <td class="py-2 pr-3 text-right font-bold" :class="h.points >= 0 ? 'text-emerald-600' : 'text-red-500'">{{ h.points >= 0 ? '+' : '' }}{{ h.points }}</td>
                    <td class="py-2 pr-3 text-right text-slate-600">{{ h.balance_after }}</td>
                    <td class="py-2 text-slate-500">{{ h.note }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import LoyaltyService from '@/services/LoyaltyService'
import LoyaltySettingService from '@/services/LoyaltySettingService'

const customers = ref([])
const loading = ref(true)
const search = ref('')
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const pointValue = ref(1)

const money = (v) => 'KES ' + Number(v ?? 0).toLocaleString(undefined, { maximumFractionDigits: 2 })
const formatDate = (d) => d ? new Date(d).toLocaleString('en-KE', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: 'Africa/Nairobi' }) : ''
const typeClass = (t) => ({ earn: 'bg-emerald-50 text-emerald-700', redeem: 'bg-amber-50 text-amber-700', adjustment: 'bg-[#EAF5FD] text-[#1682C4]' }[t] || 'bg-slate-100 text-slate-500')

let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

const load = async () => {
  loading.value = true
  try {
    const res = await LoyaltyService.getCustomers({ search: search.value, page: page.value })
    customers.value = res?.data ?? []
    total.value = res?.total ?? 0
    lastPage.value = res?.last_page ?? 1
  } finally {
    loading.value = false
  }
}

const activeCustomer = ref(null)
const history = ref([])
const historyLoading = ref(false)
const adjusting = ref(false)
const adjustForm = ref({ points: null, note: '' })

const openCustomer = async (c) => {
  activeCustomer.value = c
  adjustForm.value = { points: null, note: '' }
  historyLoading.value = true
  try {
    const res = await LoyaltyService.getHistory(c.id)
    activeCustomer.value = res?.customer ?? c
    history.value = res?.history?.data ?? []
  } finally {
    historyLoading.value = false
  }
}

const closeCustomer = () => { activeCustomer.value = null; history.value = [] }

const submitAdjust = async () => {
  if (!adjustForm.value.points) return
  adjusting.value = true
  try {
    const res = await LoyaltyService.adjust(activeCustomer.value.id, adjustForm.value)
    activeCustomer.value.loyalty_points_balance = res.balance
    const idx = customers.value.findIndex(c => c.id === activeCustomer.value.id)
    if (idx !== -1) customers.value[idx].loyalty_points_balance = res.balance
    adjustForm.value = { points: null, note: '' }
    await openCustomer(activeCustomer.value)
    Swal.fire({ icon: 'success', title: 'Points adjusted', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || 'Could not adjust points.', confirmButtonColor: '#229DE7' })
  } finally {
    adjusting.value = false
  }
}

onMounted(async () => {
  const settingsRes = await LoyaltySettingService.get()
  pointValue.value = Number(settingsRes?.data?.point_value ?? 1)
  await load()
})
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
