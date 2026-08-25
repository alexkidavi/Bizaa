<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Attendance</h1>
        <p class="text-slate-500 mt-1 text-sm">Record and monitor staff attendance.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Record Attendance</h2>
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
              <label class="field-label required">Date</label>
              <input v-model="form.date" type="date" class="field-input" :class="{ 'border-red-400': errors.date }" />
              <p v-if="errors.date" class="field-error">Date is required</p>
            </div>
            <div>
              <label class="field-label required">Status</label>
              <select v-model="form.status" class="field-input">
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
                <option value="half_day">Half Day</option>
                <option value="leave">On Leave</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Check In</label>
                <input v-model="form.check_in" type="time" class="field-input" />
              </div>
              <div>
                <label class="field-label">Check Out</label>
                <input v-model="form.check_out" type="time" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Shift</label>
              <select v-model="form.shift_id" class="field-input">
                <option value="">No shift</option>
                <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="field-label">Notes</label>
              <textarea v-model="form.notes" rows="2" class="field-input" placeholder="Optional notes"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ loading ? 'Saving...' : 'Record Attendance' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-3">
            <input v-model="filterDate" type="date" class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]" />
            <select v-model="filterStatus" class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]">
              <option value="">All Statuses</option>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
              <option value="half_day">Half Day</option>
              <option value="leave">On Leave</option>
            </select>
            <button @click="fetchAttendance" class="px-4 py-2 bg-[#229DE7] text-white text-sm font-bold rounded-xl hover:bg-[#1682C4] transition">Filter</button>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 6" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>

          <div v-else-if="records.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No attendance records</p>
            <p class="text-sm text-slate-400 mt-1">Record attendance using the form.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">#</th>
                  <th class="px-5 py-3.5">Staff</th>
                  <th class="px-5 py-3.5">Date</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5">Check In / Out</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(rec, i) in records" :key="rec.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ rec.user?.name || '—' }}</td>
                  <td class="px-5 py-4 text-xs text-slate-600 font-semibold">{{ fmtDate(rec.date) }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="statusClass(rec.status)">
                      {{ statusLabel(rec.status) }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-500 font-semibold">
                    {{ rec.check_in || '—' }} / {{ rec.check_out || '—' }}
                  </td>
                  <td class="px-5 py-4 text-right">
                    <button @click="removeRecord(rec.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition ml-auto">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="totalPages > 1" class="px-6 py-4 border-t border-[#EEF5FB] flex items-center justify-between">
            <span class="text-sm text-slate-500 font-semibold">Page {{ currentPage }} of {{ totalPages }}</span>
            <div class="flex gap-2">
              <button :disabled="currentPage === 1" @click="currentPage--; fetchAttendance()" class="px-4 py-2 border border-[#D9EBF7] rounded-xl text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition">← Prev</button>
              <button :disabled="currentPage >= totalPages" @click="currentPage++; fetchAttendance()" class="px-4 py-2 border border-[#D9EBF7] rounded-xl text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition">Next →</button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Swal from 'sweetalert2'
import AttendanceService from '@/services/AttendanceService'
import ShiftService from '@/services/ShiftService'
import StaffService from '@/services/StaffService'

const tableLoading = ref(true)
const loading = ref(false)
const staff = ref([])
const shifts = ref([])
const records = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const filterDate = ref(new Date().toISOString().slice(0, 10))
const filterStatus = ref('')

const form = reactive({ user_id: '', date: new Date().toISOString().slice(0, 10), status: 'present', check_in: '', check_out: '', shift_id: '', notes: '' })
const errors = reactive({ user_id: false, date: false })

const statusClass = (s) => ({ present: 'bg-emerald-50 text-emerald-700', absent: 'bg-red-50 text-red-700', late: 'bg-amber-50 text-amber-700', half_day: 'bg-violet-50 text-violet-700', leave: 'bg-slate-100 text-slate-600' }[s] || 'bg-slate-100 text-slate-600')
const statusLabel = (s) => ({ present: 'Present', absent: 'Absent', late: 'Late', half_day: 'Half Day', leave: 'On Leave' }[s] || s)
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const fetchAttendance = async () => {
  tableLoading.value = true
  try {
    const params = { page: currentPage.value }
    if (filterDate.value) params.date = filterDate.value
    if (filterStatus.value) params.status = filterStatus.value
    const res = await AttendanceService.getAll(params)
    records.value = res?.data ?? []
    totalPages.value = res?.last_page ?? 1
  } finally { tableLoading.value = false }
}

onMounted(async () => {
  const [staffRes, shiftsRes] = await Promise.all([StaffService.getAllStaff(), ShiftService.getAll()])
  staff.value = staffRes?.data?.data ?? staffRes?.data ?? []
  shifts.value = shiftsRes?.data ?? []
  await fetchAttendance()
})

const save = async () => {
  errors.user_id = !form.user_id
  errors.date = !form.date
  if (errors.user_id || errors.date) return
  loading.value = true
  try {
    await AttendanceService.record({ ...form, shift_id: form.shift_id || null })
    toast('success', 'Attendance Recorded')
    await fetchAttendance()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const removeRecord = async (id) => {
  const r = await Swal.fire({ title: 'Delete this record?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await AttendanceService.remove(id); await fetchAttendance(); toast('success', 'Deleted') }
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
