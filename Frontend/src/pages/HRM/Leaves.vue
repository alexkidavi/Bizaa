<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Leave Requests</h1>
        <p class="text-slate-500 mt-1 text-sm">Manage staff leave applications.</p>
      </div>
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
              <label class="field-label required">Staff Member</label>
              <select v-model="form.user_id" class="field-input" :class="{ 'border-red-400': errors.user_id }">
                <option value="" disabled>Select staff...</option>
                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <p v-if="errors.user_id" class="field-error">Staff is required</p>
            </div>
            <div>
              <label class="field-label required">Leave Type</label>
              <select v-model="form.leave_type_id" class="field-input" :class="{ 'border-red-400': errors.leave_type_id }">
                <option value="" disabled>Select leave type...</option>
                <option v-for="lt in leaveTypes" :key="lt.id" :value="lt.id">{{ lt.name }} ({{ lt.days_allowed }} days)</option>
              </select>
              <p v-if="errors.leave_type_id" class="field-error">Leave type is required</p>
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
            <div v-if="form.days" class="px-3 py-2 bg-[#EAF5FD] rounded-xl text-sm font-bold text-[#1682C4]">
              Duration: {{ form.days }} day(s)
            </div>
            <div>
              <label class="field-label">Reason</label>
              <textarea v-model="form.reason" rows="2" class="field-input" placeholder="Optional reason"></textarea>
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
                  <th class="px-5 py-3.5">Staff</th>
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
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ rec.user?.name || '—' }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ rec.leave_type?.name || '—' }}</span>
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-600 font-semibold whitespace-nowrap">{{ fmtDate(rec.start_date) }} – {{ fmtDate(rec.end_date) }}</td>
                  <td class="px-5 py-4 text-center text-sm font-bold text-slate-900">{{ rec.days }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="statusClass(rec.status)">{{ rec.status }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <template v-if="rec.status === 'pending'">
                        <button @click="updateStatus(rec.id, 'approved')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">Approve</button>
                        <button @click="updateStatus(rec.id, 'rejected')" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 transition">Reject</button>
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
import { ref, reactive, onMounted } from 'vue'
import Swal from 'sweetalert2'
import LeaveService from '@/services/LeaveService'
import LeaveTypeService from '@/services/LeaveTypeService'
import StaffService from '@/services/StaffService'

const tableLoading = ref(true)
const loading = ref(false)
const staff = ref([])
const leaveTypes = ref([])
const records = ref([])
const filterStatus = ref('')

const form = reactive({ user_id: '', leave_type_id: '', start_date: '', end_date: '', days: null, reason: '' })
const errors = reactive({ user_id: false, leave_type_id: false, start_date: false, end_date: false })

const statusFilters = [{ label: 'All', value: '' }, { label: 'Pending', value: 'pending' }, { label: 'Approved', value: 'approved' }, { label: 'Rejected', value: 'rejected' }]
const statusClass = (s) => ({ pending: 'bg-amber-50 text-amber-700', approved: 'bg-emerald-50 text-emerald-700', rejected: 'bg-red-50 text-red-700' }[s] || 'bg-slate-100 text-slate-600')
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const calcDays = () => {
  if (form.start_date && form.end_date) {
    const diff = Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)) + 1
    form.days = diff > 0 ? diff : null
  }
}

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
  const [staffRes, ltRes] = await Promise.all([StaffService.getAllStaff(), LeaveTypeService.getAll()])
  staff.value = staffRes?.data?.data ?? staffRes?.data ?? []
  leaveTypes.value = ltRes?.data ?? []
  await fetchRecords()
})

const save = async () => {
  errors.user_id = !form.user_id
  errors.leave_type_id = !form.leave_type_id
  errors.start_date = !form.start_date
  errors.end_date = !form.end_date
  if (errors.user_id || errors.leave_type_id || errors.start_date || errors.end_date) return
  loading.value = true
  try {
    await LeaveService.create({ ...form })
    toast('success', 'Leave Request Submitted')
    form.user_id = ''; form.leave_type_id = ''; form.start_date = ''; form.end_date = ''; form.days = null; form.reason = ''
    await fetchRecords()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const updateStatus = async (id, status) => {
  try { await LeaveService.updateStatus(id, status); toast('success', `Leave ${status}`); await fetchRecords() }
  catch { Swal.fire({ icon: 'error', title: 'Error', confirmButtonColor: '#229DE7' }) }
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
