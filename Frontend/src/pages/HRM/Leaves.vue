<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Leave Requests</h1>
        <p class="text-slate-500 mt-1 text-sm">Manage employee leave applications and balances.</p>
      </div>
      <router-link to="/hrm/leave-balances" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all">
        View Leave Balances
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Submit Leave Request</h2>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Employee</label>
              <select v-model="form.employee_id" class="field-input" :class="{ 'border-red-400': errors.employee_id }" @change="onEmployeeOrTypeChange">
                <option value="" disabled>Select employee...</option>
                <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.full_name }}{{ e.employee_number ? ` (${e.employee_number})` : '' }}</option>
              </select>
              <p v-if="errors.employee_id" class="field-error">Employee is required</p>
            </div>
            <div>
              <label class="field-label required">Leave Type</label>
              <select v-model="form.leave_type_id" class="field-input" :class="{ 'border-red-400': errors.leave_type_id }" @change="onEmployeeOrTypeChange">
                <option value="" disabled>Select leave type...</option>
                <option v-for="lt in activeLeaveTypes" :key="lt.id" :value="lt.id">{{ lt.name }} ({{ lt.days_allowed }} days{{ lt.is_paid ? '' : ', unpaid' }})</option>
              </select>
              <p v-if="errors.leave_type_id" class="field-error">Leave type is required</p>
            </div>
            <div v-if="balanceHint !== null" class="px-3 py-2 bg-emerald-50 rounded-xl text-xs font-bold text-emerald-700">
              Available balance: {{ balanceHint }} day(s)
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Start Date</label>
                <input v-model="form.start_date" type="date" class="field-input" :class="{ 'border-red-400': errors.start_date }" @change="calcDays" />
                <p v-if="errors.start_date" class="field-error">Required</p>
              </div>
              <div>
                <label class="field-label required">End Date</label>
                <input v-model="form.end_date" type="date" class="field-input" :class="{ 'border-red-400': errors.end_date }" @change="calcDays" />
                <p v-if="errors.end_date" class="field-error">Required</p>
              </div>
            </div>
            <div v-if="form.working_days" class="px-3 py-2 bg-[#EAF5FD] rounded-xl text-sm font-bold text-[#1682C4]">
              {{ calendarDays }} calendar day(s) &middot; {{ form.working_days }} working day(s)
            </div>
            <div>
              <label class="field-label">Reason</label>
              <textarea v-model="form.reason" rows="2" class="field-input" placeholder="Optional reason"></textarea>
            </div>
            <div>
              <label class="field-label">Supporting Documents</label>
              <input type="file" multiple @change="onFilesSelected" class="field-input file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#EAF5FD] file:text-[#1682C4]" />
              <p v-if="selectedFiles.length" class="text-xs text-slate-400 mt-1">{{ selectedFiles.length }} file(s) selected</p>
            </div>
            <button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ loading ? 'Submitting...' : 'Submit Request' }}
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

          <div v-else-if="records.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No leave requests found</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">#</th>
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5">Type</th>
                  <th class="px-5 py-3.5">Period</th>
                  <th class="px-5 py-3.5 text-center">Days</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(rec, i) in records" :key="rec.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ rec.employee?.full_name || '—' }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ rec.leave_type?.name || '—' }}</span>
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-600 font-semibold whitespace-nowrap">{{ fmtDate(rec.start_date) }} – {{ fmtDate(rec.end_date) }}</td>
                  <td class="px-5 py-4 text-center text-sm font-bold text-slate-900">
                    {{ rec.working_days ?? rec.days }}
                    <span v-if="rec.working_days && rec.working_days !== rec.days" class="block text-[10px] font-semibold text-slate-400">of {{ rec.days }} cal.</span>
                  </td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="statusClass(rec.status)">{{ rec.status }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <template v-if="rec.status === 'submitted'">
                        <button @click="decide(rec.id, 'approved')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">Approve</button>
                        <button @click="decide(rec.id, 'rejected')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 transition">Reject</button>
                      </template>
                      <template v-else-if="rec.status === 'approved'">
                        <button @click="decide(rec.id, 'completed')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 hover:bg-sky-100 transition">Complete</button>
                        <button @click="decide(rec.id, 'cancelled')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 transition">Cancel</button>
                      </template>
                      <button @click="remove(rec.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
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
import { ref, reactive, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'
import LeaveService from '@/services/LeaveService'
import LeaveTypeService from '@/services/LeaveTypeService'
import LeaveBalanceService from '@/services/LeaveBalanceService'
import EmployeeProfileService from '@/services/EmployeeProfileService'

const tableLoading = ref(true)
const loading = ref(false)
const employees = ref([])
const leaveTypes = ref([])
const records = ref([])
const filterStatus = ref('')
const selectedFiles = ref([])
const balanceHint = ref(null)

const form = reactive({ employee_id: '', leave_type_id: '', start_date: '', end_date: '', working_days: null, reason: '' })
const errors = reactive({ employee_id: false, leave_type_id: false, start_date: false, end_date: false })

const activeLeaveTypes = computed(() => leaveTypes.value.filter(lt => lt.is_active !== false))
const calendarDays = computed(() => {
  if (!form.start_date || !form.end_date) return 0
  const diff = Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)) + 1
  return diff > 0 ? diff : 0
})

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Submitted', value: 'submitted' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
]
const statusClass = (s) => ({
  submitted: 'bg-amber-50 text-amber-700',
  approved: 'bg-emerald-50 text-emerald-700',
  rejected: 'bg-red-50 text-red-700',
  completed: 'bg-sky-50 text-sky-700',
  cancelled: 'bg-slate-100 text-slate-500',
  draft: 'bg-slate-100 text-slate-500',
}[s] || 'bg-slate-100 text-slate-600')
// The backend (app timezone Africa/Nairobi) serializes calendar dates as
// the UTC instant for Nairobi midnight. Pinning the display timezone here
// — rather than letting the viewer's own local timezone reinterpret that
// instant — keeps the date correct regardless of where it's viewed from.
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : '—'
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const calcDays = () => {
  if (form.start_date && form.end_date && calendarDays.value > 0) {
    let count = 0
    const cur = new Date(form.start_date)
    const end = new Date(form.end_date)
    while (cur <= end) {
      const day = cur.getDay()
      if (day !== 0 && day !== 6) count++
      cur.setDate(cur.getDate() + 1)
    }
    form.working_days = count || 1
  } else {
    form.working_days = null
  }
  fetchBalanceHint()
}

const fetchBalanceHint = async () => {
  balanceHint.value = null
  if (!form.employee_id || !form.leave_type_id) return
  const lt = leaveTypes.value.find(l => l.id === form.leave_type_id)
  if (!lt || !lt.is_paid) return
  try {
    const res = await LeaveBalanceService.ensureForYear(new Date(form.start_date || Date.now()).getFullYear())
    const bal = (res?.data ?? []).find(b => b.employee_id === form.employee_id && b.leave_type_id === form.leave_type_id)
    balanceHint.value = bal ? bal.closing_balance : lt.days_allowed
  } catch { balanceHint.value = null }
}
const onEmployeeOrTypeChange = () => fetchBalanceHint()
const onFilesSelected = (e) => { selectedFiles.value = Array.from(e.target.files || []) }

const fetchRecords = async () => {
  tableLoading.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    const res = await LeaveService.getAll(params)
    records.value = res?.data ?? []
  } finally { tableLoading.value = false }
}

onMounted(async () => {
  const [empRes, ltRes] = await Promise.all([
    EmployeeProfileService.getAll({ per_page: 500, employment_status: 'active' }),
    LeaveTypeService.getAll(),
  ])
  employees.value = empRes?.data ?? []
  leaveTypes.value = ltRes?.data ?? []
  await fetchRecords()
})

const save = async () => {
  errors.employee_id = !form.employee_id
  errors.leave_type_id = !form.leave_type_id
  errors.start_date = !form.start_date
  errors.end_date = !form.end_date
  if (errors.employee_id || errors.leave_type_id || errors.start_date || errors.end_date) return
  loading.value = true
  try {
    await LeaveService.create({
      employee_id: form.employee_id,
      leave_type_id: form.leave_type_id,
      start_date: form.start_date,
      end_date: form.end_date,
      working_days: form.working_days,
      reason: form.reason,
      supporting_documents: selectedFiles.value,
    })
    toast('success', 'Leave Request Submitted')
    form.employee_id = ''; form.leave_type_id = ''; form.start_date = ''; form.end_date = ''; form.working_days = null; form.reason = ''
    selectedFiles.value = []
    balanceHint.value = null
    await fetchRecords()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const decide = async (id, status) => {
  const labels = { approved: 'Approve', rejected: 'Reject', completed: 'Mark Completed', cancelled: 'Cancel' }
  const r = await Swal.fire({
    title: `${labels[status]} this leave request?`,
    input: 'text',
    inputLabel: 'Notes (optional)',
    inputPlaceholder: 'Add a note...',
    showCancelButton: true,
    confirmButtonColor: '#229DE7',
    cancelButtonColor: '#94a3b8',
    confirmButtonText: labels[status],
  })
  if (!r.isConfirmed) return
  try {
    await LeaveService.updateStatus(id, status, r.value || null)
    toast('success', `Leave ${status}`)
    await fetchRecords()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete leave request?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await LeaveService.remove(id); await fetchRecords(); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
