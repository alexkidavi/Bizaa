<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Leave Balances</h1>
        <p class="text-slate-500 mt-1 text-sm">Track accrued, used and remaining leave per employee.</p>
      </div>
      <div class="flex items-center gap-2">
        <select v-model.number="year" @change="load" class="field-input !w-auto">
          <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
        </select>
        <button @click="ensureAll" :disabled="ensuring" class="px-4 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl transition-all disabled:opacity-60">
          {{ ensuring ? 'Initializing...' : 'Initialize Balances' }}
        </button>
      </div>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap gap-3">
        <select v-model="filterEmployee" @change="load" class="field-input !w-auto">
          <option value="">All Employees</option>
          <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.full_name }}</option>
        </select>
        <select v-model="filterLeaveType" @change="load" class="field-input !w-auto">
          <option value="">All Leave Types</option>
          <option v-for="lt in leaveTypes" :key="lt.id" :value="lt.id">{{ lt.name }}</option>
        </select>
      </div>

      <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 5" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>

      <div v-else-if="balances.length === 0" class="py-20 text-center">
        <p class="font-bold text-slate-700">No leave balances for {{ year }}</p>
        <p class="text-sm text-slate-400 mt-1">Click "Initialize Balances" to create rows for every active employee.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5">Employee</th>
              <th class="px-5 py-3.5">Leave Type</th>
              <th class="px-5 py-3.5 text-center">Opening</th>
              <th class="px-5 py-3.5 text-center">Accrued</th>
              <th class="px-5 py-3.5 text-center">Carried Fwd</th>
              <th class="px-5 py-3.5 text-center">Adjustments</th>
              <th class="px-5 py-3.5 text-center">Pending</th>
              <th class="px-5 py-3.5 text-center">Used</th>
              <th class="px-5 py-3.5 text-center">Closing</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="b in balances" :key="b.id" class="hover:bg-[#F5FAFF] transition-colors">
              <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ b.employee?.full_name || '—' }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ b.leave_type?.name || '—' }}</span>
              </td>
              <td class="px-5 py-4 text-center text-sm">{{ num(b.opening_balance) }}</td>
              <td class="px-5 py-4 text-center text-sm">{{ num(b.accrued_days) }}</td>
              <td class="px-5 py-4 text-center text-sm">{{ num(b.carried_forward_days) }}</td>
              <td class="px-5 py-4 text-center text-sm" :class="Number(b.adjustments) !== 0 ? 'font-bold text-violet-600' : ''">{{ num(b.adjustments) }}</td>
              <td class="px-5 py-4 text-center text-sm text-amber-600">{{ num(b.pending_days) }}</td>
              <td class="px-5 py-4 text-center text-sm text-slate-500">{{ num(b.used_days) }}</td>
              <td class="px-5 py-4 text-center">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">{{ num(b.closing_balance) }}</span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="openAdjust(b)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#EAF5FD] text-[#1682C4] hover:bg-[#D9EBF7] transition">Adjust</button>
                  <button @click="openHistory(b)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">History</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Adjust Modal -->
    <div v-if="adjustTarget" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="adjustTarget = null">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Adjust Balance</h2>
          <p class="text-xs text-slate-400 mt-1">{{ adjustTarget.employee?.full_name }} — {{ adjustTarget.leave_type?.name }}</p>
        </div>
        <form @submit.prevent="submitAdjust" class="p-6 space-y-4">
          <div>
            <label class="field-label required">Amount (days)</label>
            <input v-model="adjustForm.amount" type="number" step="0.01" class="field-input" placeholder="e.g. 2 or -1.5" />
            <p class="text-[11px] text-slate-400 mt-1">Positive adds days, negative deducts days.</p>
          </div>
          <div>
            <label class="field-label required">Reason</label>
            <textarea v-model="adjustForm.reason" rows="3" class="field-input" placeholder="Mandatory reason for this correction..."></textarea>
          </div>
          <div class="flex gap-3">
            <button type="button" @click="adjustTarget = null" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition">Cancel</button>
            <button type="submit" :disabled="adjusting" class="flex-1 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition disabled:opacity-60">{{ adjusting ? 'Saving...' : 'Save Adjustment' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- History Modal -->
    <div v-if="historyTarget" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="historyTarget = null">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <div>
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Adjustment History</h2>
            <p class="text-xs text-slate-400 mt-1">{{ historyTarget.employee?.full_name }} — {{ historyTarget.leave_type?.name }}</p>
          </div>
          <button @click="historyTarget = null" class="text-slate-400 hover:text-red-500 text-xs font-bold">Close</button>
        </div>
        <div class="p-6 max-h-96 overflow-y-auto">
          <div v-if="historyLoading" class="space-y-2"><div v-for="n in 3" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="history.length === 0" class="text-center py-10 text-sm text-slate-400">No manual adjustments recorded.</div>
          <div v-else class="space-y-3">
            <div v-for="h in history" :key="h.id" class="p-3 rounded-xl border border-[#EEF5FB] bg-[#F5FAFF]">
              <div class="flex items-center justify-between">
                <span class="text-sm font-bold" :class="Number(h.amount) >= 0 ? 'text-emerald-600' : 'text-red-500'">{{ Number(h.amount) >= 0 ? '+' : '' }}{{ h.amount }} days</span>
                <span class="text-[11px] text-slate-400">{{ fmtDate(h.created_at) }}</span>
              </div>
              <p class="text-xs text-slate-600 mt-1">{{ h.reason }}</p>
              <p class="text-[11px] text-slate-400 mt-1">by {{ h.adjusted_by?.name || 'system' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Swal from 'sweetalert2'
import LeaveBalanceService from '@/services/LeaveBalanceService'
import LeaveTypeService from '@/services/LeaveTypeService'
import EmployeeProfileService from '@/services/EmployeeProfileService'

const currentYear = new Date().getFullYear()
const yearOptions = [currentYear - 1, currentYear, currentYear + 1]
const year = ref(currentYear)

const tableLoading = ref(true)
const ensuring = ref(false)
const balances = ref([])
const employees = ref([])
const leaveTypes = ref([])
const filterEmployee = ref('')
const filterLeaveType = ref('')

const adjustTarget = ref(null)
const adjustForm = reactive({ amount: '', reason: '' })
const adjusting = ref(false)

const historyTarget = ref(null)
const history = ref([])
const historyLoading = ref(false)

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const num = (v) => Number(v ?? 0).toFixed(2).replace(/\.00$/, '')
// The backend (app timezone Africa/Nairobi) serializes calendar dates as
// the UTC instant for Nairobi midnight. Pinning the display timezone here
// — rather than letting the viewer's own local timezone reinterpret that
// instant — keeps the date correct regardless of where it's viewed from.
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : '—'

const load = async () => {
  tableLoading.value = true
  try {
    const params = { year: year.value }
    if (filterEmployee.value) params.employee_id = filterEmployee.value
    if (filterLeaveType.value) params.leave_type_id = filterLeaveType.value
    const res = await LeaveBalanceService.getAll(params)
    balances.value = res?.data ?? []
  } finally { tableLoading.value = false }
}

const ensureAll = async () => {
  ensuring.value = true
  try {
    await LeaveBalanceService.ensureForYear(year.value)
    toast('success', 'Balances initialized')
    await load()
  } catch {
    Swal.fire({ icon: 'error', title: 'Failed to initialize balances', confirmButtonColor: '#229DE7' })
  } finally { ensuring.value = false }
}

onMounted(async () => {
  const [empRes, ltRes] = await Promise.all([
    EmployeeProfileService.getAll({ per_page: 500, employment_status: 'active' }),
    LeaveTypeService.getAll(),
  ])
  employees.value = empRes?.data ?? []
  leaveTypes.value = ltRes?.data ?? []
  await load()
})

const openAdjust = (b) => { adjustTarget.value = b; adjustForm.amount = ''; adjustForm.reason = '' }
const submitAdjust = async () => {
  if (adjustForm.amount === '' || isNaN(Number(adjustForm.amount))) {
    Swal.fire({ icon: 'warning', title: 'Enter a valid amount', confirmButtonColor: '#229DE7' }); return
  }
  if (!adjustForm.reason.trim()) {
    Swal.fire({ icon: 'warning', title: 'A reason is required', confirmButtonColor: '#229DE7' }); return
  }
  adjusting.value = true
  try {
    const res = await LeaveBalanceService.adjust(adjustTarget.value.id, Number(adjustForm.amount), adjustForm.reason.trim())
    const idx = balances.value.findIndex(b => b.id === adjustTarget.value.id)
    if (idx !== -1) balances.value[idx] = res.data
    toast('success', 'Balance adjusted')
    adjustTarget.value = null
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { adjusting.value = false }
}

const openHistory = async (b) => {
  historyTarget.value = b
  historyLoading.value = true
  try { const res = await LeaveBalanceService.getHistory(b.id); history.value = res?.data ?? [] }
  finally { historyLoading.value = false }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; width: 100%; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
