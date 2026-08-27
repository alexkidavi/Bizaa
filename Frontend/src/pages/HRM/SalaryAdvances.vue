<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Salary Advances</h1>
        <p class="text-slate-500 mt-1 text-sm">Request, approve and track employee salary advances.</p>
      </div>
      <router-link to="/hrm/payroll" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
        &larr; Back to Payroll
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Request Advance</h2>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Employee</label>
              <select v-model="form.employee_id" class="field-input">
                <option value="" disabled>Select employee...</option>
                <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.full_name }}</option>
              </select>
            </div>
            <div>
              <label class="field-label required">Amount</label>
              <input v-model="form.amount" type="number" step="0.01" min="1" class="field-input" placeholder="e.g. 10000" />
            </div>
            <div>
              <label class="field-label required">Repayment / Payroll Period</label>
              <input v-model="form.repayment_amount_per_period" type="number" step="0.01" min="1" class="field-input" placeholder="e.g. 2500" />
              <p class="text-[11px] text-slate-400 mt-1">Deducted from each payroll run until fully repaid.</p>
            </div>
            <div>
              <label class="field-label">Reason</label>
              <textarea v-model="form.reason" rows="2" class="field-input" placeholder="Optional reason"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Submitting...' : 'Request Advance' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap gap-2">
            <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; fetchRecords()"
              class="px-3 py-1.5 rounded-lg text-xs font-bold border transition"
              :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
              {{ f.label }}
            </button>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 5" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="records.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No salary advances found</p></div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5 text-right">Amount</th>
                  <th class="px-5 py-3.5 text-right">Per Period</th>
                  <th class="px-5 py-3.5 text-right">Repaid</th>
                  <th class="px-5 py-3.5 text-right">Outstanding</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="a in records" :key="a.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ a.employee?.full_name || '—' }}</td>
                  <td class="px-5 py-4 text-right text-sm font-bold">{{ fmtMoney(a.amount) }}</td>
                  <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(a.repayment_amount_per_period) }}</td>
                  <td class="px-5 py-4 text-right text-sm text-emerald-600">{{ fmtMoney(a.repaid_amount) }}</td>
                  <td class="px-5 py-4 text-right text-sm font-bold text-amber-600">{{ fmtMoney(a.outstanding_balance) }}</td>
                  <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="statusClass(a.status)">{{ a.status }}</span></td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <template v-if="a.status === 'pending'">
                        <button @click="approve(a.id)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">Approve</button>
                        <button @click="reject(a.id)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 transition">Reject</button>
                        <button @click="remove(a.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                      </template>
                      <button v-else-if="a.status === 'approved'" @click="cancel(a.id)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 transition">Cancel</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Swal from 'sweetalert2'
import SalaryAdvanceService from '@/services/SalaryAdvanceService'
import EmployeeProfileService from '@/services/EmployeeProfileService'

const tableLoading = ref(true)
const loading = ref(false)
const employees = ref([])
const records = ref([])
const filterStatus = ref('')

const form = reactive({ employee_id: '', amount: '', repayment_amount_per_period: '', reason: '' })

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })
const fmtMoney = (v) => `KES ${Number(v ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]
const statusClass = (s) => ({
  pending: 'bg-amber-50 text-amber-700',
  approved: 'bg-sky-50 text-sky-700',
  rejected: 'bg-red-50 text-red-700',
  completed: 'bg-emerald-50 text-emerald-700',
  cancelled: 'bg-slate-100 text-slate-500',
}[s] || 'bg-slate-100 text-slate-600')

const fetchRecords = async () => {
  tableLoading.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    const res = await SalaryAdvanceService.getAll(params)
    records.value = res?.data ?? []
  } finally { tableLoading.value = false }
}

onMounted(async () => {
  const empRes = await EmployeeProfileService.getAll({ per_page: 500, employment_status: 'active' })
  employees.value = empRes?.data ?? []
  await fetchRecords()
})

const save = async () => {
  if (!form.employee_id || !form.amount || !form.repayment_amount_per_period) {
    Swal.fire({ icon: 'warning', title: 'Employee, amount and repayment per period are required', confirmButtonColor: '#229DE7' }); return
  }
  loading.value = true
  try {
    const res = await SalaryAdvanceService.create({ ...form })
    records.value.unshift(res.data)
    toast('success', 'Advance requested')
    form.employee_id = ''; form.amount = ''; form.repayment_amount_per_period = ''; form.reason = ''
  } catch (err) { errorAlert(err, 'Failed to request advance') }
  finally { loading.value = false }
}

const approve = async (id) => {
  try {
    const res = await SalaryAdvanceService.approve(id)
    const idx = records.value.findIndex(r => r.id === id)
    if (idx !== -1) records.value[idx] = res.data
    toast('success', 'Advance approved')
  } catch (err) { errorAlert(err, 'Failed to approve') }
}

const reject = async (id) => {
  const r = await Swal.fire({ title: 'Reject this advance?', input: 'text', inputLabel: 'Notes (optional)', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Reject' })
  if (!r.isConfirmed) return
  try {
    const res = await SalaryAdvanceService.reject(id, r.value || null)
    const idx = records.value.findIndex(rec => rec.id === id)
    if (idx !== -1) records.value[idx] = res.data
    toast('success', 'Advance rejected')
  } catch (err) { errorAlert(err, 'Failed to reject') }
}

const cancel = async (id) => {
  const r = await Swal.fire({ title: 'Cancel this advance?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, cancel' })
  if (!r.isConfirmed) return
  try {
    const res = await SalaryAdvanceService.cancel(id)
    const idx = records.value.findIndex(rec => rec.id === id)
    if (idx !== -1) records.value[idx] = res.data
    toast('success', 'Advance cancelled')
  } catch (err) { errorAlert(err, 'Failed to cancel') }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this advance request?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await SalaryAdvanceService.remove(id); records.value = records.value.filter(r => r.id !== id); toast('success', 'Deleted') }
  catch (err) { errorAlert(err, 'Failed to delete') }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
