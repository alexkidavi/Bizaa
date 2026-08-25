<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Payroll</h1>
        <p class="text-slate-500 mt-1 text-sm">Generate and manage monthly staff payroll.</p>
      </div>
      <!-- Generate Payroll -->
      <div class="flex items-center gap-3">
        <select v-model="genMonth" class="text-sm bg-white border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]">
          <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
        <input v-model="genYear" type="number" min="2020" max="2100" class="w-24 text-sm bg-white border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]" />
        <button @click="generate" :disabled="generating" class="px-4 py-2 bg-[#229DE7] text-white text-sm font-bold rounded-xl hover:bg-[#1682C4] disabled:opacity-60 transition flex items-center gap-2">
          <svg v-if="generating" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
          Generate Payroll
        </button>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="filterMonth" @change="fetchPayroll" class="text-sm bg-white border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]">
        <option value="">All Months</option>
        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
      </select>
      <input v-model="filterYear" type="number" min="2020" @change="fetchPayroll" placeholder="Year" class="w-28 text-sm bg-white border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]" />
      <select v-model="filterStatus" @change="fetchPayroll" class="text-sm bg-white border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]">
        <option value="">All Statuses</option>
        <option value="draft">Draft</option>
        <option value="paid">Paid</option>
      </select>
    </div>

    <!-- TABLE -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 6" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>

      <div v-else-if="records.length === 0" class="py-20 text-center">
        <div class="w-14 h-14 rounded-full bg-[#EAF5FD] flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
        </div>
        <p class="font-bold text-slate-700">No payroll records</p>
        <p class="text-sm text-slate-400 mt-1">Generate payroll for a month to get started.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5">#</th>
              <th class="px-5 py-3.5">Staff</th>
              <th class="px-5 py-3.5">Period</th>
              <th class="px-5 py-3.5 text-right">Basic</th>
              <th class="px-5 py-3.5 text-right">OT Pay</th>
              <th class="px-5 py-3.5 text-right">Allowances</th>
              <th class="px-5 py-3.5 text-right">Deductions</th>
              <th class="px-5 py-3.5 text-right">Net</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="(rec, i) in records" :key="rec.id" class="hover:bg-[#F5FAFF] transition-colors">
              <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
              <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ rec.user?.name || '—' }}</td>
              <td class="px-5 py-4 text-xs text-slate-600 font-semibold">{{ monthName(rec.month) }} {{ rec.year }}</td>
              <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ fmt(rec.basic_salary) }}</td>
              <td class="px-5 py-4 text-right text-sm font-semibold text-emerald-700">{{ fmt(rec.overtime_pay) }}</td>
              <td class="px-5 py-4 text-right text-sm font-semibold text-violet-700">{{ fmt(rec.allowances) }}</td>
              <td class="px-5 py-4 text-right text-sm font-semibold text-red-600">{{ fmt(rec.deductions) }}</td>
              <td class="px-5 py-4 text-right font-extrabold text-sm text-slate-900">{{ fmt(rec.net_salary) }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="rec.status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                  {{ rec.status === 'paid' ? 'Paid' : 'Draft' }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button v-if="rec.status === 'draft'" @click="openEdit(rec)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#EAF5FD] text-[#1682C4] hover:bg-[#B3DEFA] transition">Edit</button>
                  <button v-if="rec.status === 'draft'" @click="markPaid(rec.id)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">Pay</button>
                  <button @click="remove(rec.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-[#F5FAFF] border-t border-[#B3DEFA]">
            <tr>
              <td colspan="7" class="px-5 py-3 text-xs font-bold uppercase tracking-widest text-slate-500">Total Net Payroll</td>
              <td class="px-5 py-3 text-right font-extrabold text-[#229DE7]">{{ fmt(totalNet) }}</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="editRecord" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm">
      <div class="bg-white rounded-2xl shadow-xl border border-[#D9EBF7] w-full max-w-md mx-4 overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] to-[#5BB8F5]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Edit Payroll – {{ editRecord.user?.name }}</h3>
          <button @click="editRecord = null" class="text-slate-400 hover:text-red-500 text-xs font-bold transition">Close</button>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="field-label">Basic Salary</label>
              <input v-model="editForm.basic_salary" type="number" min="0" step="0.01" class="field-input" />
            </div>
            <div>
              <label class="field-label">OT Pay</label>
              <input v-model="editForm.overtime_pay" type="number" min="0" step="0.01" class="field-input" />
            </div>
            <div>
              <label class="field-label">Allowances</label>
              <input v-model="editForm.allowances" type="number" min="0" step="0.01" class="field-input" />
            </div>
            <div>
              <label class="field-label">Deductions</label>
              <input v-model="editForm.deductions" type="number" min="0" step="0.01" class="field-input" />
            </div>
          </div>
          <div>
            <label class="field-label">Notes</label>
            <textarea v-model="editForm.notes" rows="2" class="field-input"></textarea>
          </div>
          <div class="px-3 py-2 bg-[#EAF5FD] rounded-xl text-sm font-bold text-[#1682C4]">
            Net: {{ fmt(Number(editForm.basic_salary || 0) + Number(editForm.overtime_pay || 0) + Number(editForm.allowances || 0) - Number(editForm.deductions || 0)) }}
          </div>
          <button @click="saveEdit" :disabled="saving" class="w-full py-3 bg-[#229DE7] text-white font-bold text-sm rounded-xl hover:bg-[#1682C4] transition disabled:opacity-60">
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import Swal from 'sweetalert2'
import PayrollService from '@/services/PayrollService'

const now = new Date()
const tableLoading = ref(true)
const generating = ref(false)
const saving = ref(false)
const records = ref([])
const editRecord = ref(null)
const editForm = reactive({ basic_salary: 0, overtime_pay: 0, allowances: 0, deductions: 0, notes: '' })

const genMonth = ref(now.getMonth() + 1)
const genYear = ref(now.getFullYear())
const filterMonth = ref('')
const filterYear = ref('')
const filterStatus = ref('')

const months = [
  { value: 1, label: 'January' }, { value: 2, label: 'February' }, { value: 3, label: 'March' },
  { value: 4, label: 'April' }, { value: 5, label: 'May' }, { value: 6, label: 'June' },
  { value: 7, label: 'July' }, { value: 8, label: 'August' }, { value: 9, label: 'September' },
  { value: 10, label: 'October' }, { value: 11, label: 'November' }, { value: 12, label: 'December' },
]

const totalNet = computed(() => records.value.reduce((sum, r) => sum + Number(r.net_salary || 0), 0))
const fmt = (v) => `KES ${Number(v || 0).toLocaleString('en-KE', { minimumFractionDigits: 2 })}`
const monthName = (m) => months.find(x => x.value === m)?.label ?? m
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const fetchPayroll = async () => {
  tableLoading.value = true
  try {
    const params = {}
    if (filterMonth.value) params.month = filterMonth.value
    if (filterYear.value) params.year = filterYear.value
    if (filterStatus.value) params.status = filterStatus.value
    const res = await PayrollService.getAll(params)
    records.value = res?.data ?? []
  } finally { tableLoading.value = false }
}

onMounted(fetchPayroll)

const generate = async () => {
  generating.value = true
  try {
    const res = await PayrollService.generate(genMonth.value, genYear.value)
    toast('success', res?.message || 'Payroll Generated')
    await fetchPayroll()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { generating.value = false }
}

const openEdit = (rec) => {
  editRecord.value = rec
  editForm.basic_salary = rec.basic_salary
  editForm.overtime_pay = rec.overtime_pay
  editForm.allowances = rec.allowances
  editForm.deductions = rec.deductions
  editForm.notes = rec.notes || ''
}

const saveEdit = async () => {
  saving.value = true
  try {
    const res = await PayrollService.update(editRecord.value.id, { ...editForm })
    const idx = records.value.findIndex(r => r.id === editRecord.value.id)
    if (idx !== -1) records.value[idx] = { ...records.value[idx], ...res.data }
    editRecord.value = null
    toast('success', 'Payroll Updated')
  } catch { Swal.fire({ icon: 'error', title: 'Error', confirmButtonColor: '#229DE7' }) }
  finally { saving.value = false }
}

const markPaid = async (id) => {
  const r = await Swal.fire({ title: 'Mark as Paid?', text: 'This will record payment for this staff member.', icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, mark paid' })
  if (!r.isConfirmed) return
  try {
    const res = await PayrollService.markPaid(id)
    const idx = records.value.findIndex(r => r.id === id)
    if (idx !== -1) records.value[idx] = { ...records.value[idx], ...res.data }
    toast('success', 'Marked as Paid')
  } catch { Swal.fire({ icon: 'error', title: 'Error', confirmButtonColor: '#229DE7' }) }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete payroll record?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await PayrollService.remove(id); records.value = records.value.filter(r => r.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
