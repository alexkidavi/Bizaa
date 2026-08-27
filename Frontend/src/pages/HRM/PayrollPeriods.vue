<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Payroll</h1>
        <p class="text-slate-500 mt-1 text-sm">Create, calculate and run payroll periods.</p>
      </div>
      <div class="flex items-center gap-3">
        <router-link to="/hrm/salary-advances" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
          Salary Advances
        </router-link>
        <router-link to="/hrm/payroll/config" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
          Configure
        </router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">New Payroll Period</h2>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Period Name</label>
              <input v-model="form.name" type="text" class="field-input" placeholder="e.g. August 2026" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Start Date</label>
                <input v-model="form.start_date" type="date" class="field-input" />
              </div>
              <div>
                <label class="field-label required">End Date</label>
                <input v-model="form.end_date" type="date" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Pay Date</label>
              <input v-model="form.pay_date" type="date" class="field-input" />
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Creating...' : 'Create Period' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ periods.length }} payroll period(s)</span></div>
          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="periods.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No payroll periods yet</p></div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Period</th>
                  <th class="px-5 py-3.5">Dates</th>
                  <th class="px-5 py-3.5 text-center">Employees</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="p in periods" :key="p.id" class="hover:bg-[#F5FAFF] transition-colors cursor-pointer" @click="$router.push(`/hrm/payroll/${p.id}`)">
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ p.name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-600 font-semibold whitespace-nowrap">{{ fmtDate(p.start_date) }} – {{ fmtDate(p.end_date) }}</td>
                  <td class="px-5 py-4 text-center text-sm font-bold text-slate-700">{{ p.payslips_count ?? 0 }}</td>
                  <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="statusClass(p.status)">{{ p.status }}</span></td>
                  <td class="px-5 py-4 text-right" @click.stop>
                    <button v-if="['draft','calculated','cancelled'].includes(p.status)" @click="remove(p.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
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
import PayrollPeriodService from '@/services/PayrollPeriodService'

const tableLoading = ref(true)
const loading = ref(false)
const periods = ref([])
const form = reactive({ name: '', start_date: '', end_date: '', pay_date: '' })

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : '—'
const statusClass = (s) => ({
  draft: 'bg-slate-100 text-slate-500',
  calculated: 'bg-amber-50 text-amber-700',
  approved: 'bg-sky-50 text-sky-700',
  finalized: 'bg-violet-50 text-violet-700',
  paid: 'bg-emerald-50 text-emerald-700',
  cancelled: 'bg-red-50 text-red-700',
}[s] || 'bg-slate-100 text-slate-600')

const load = async () => {
  tableLoading.value = true
  try { const res = await PayrollPeriodService.getAll(); periods.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}
onMounted(load)

const save = async () => {
  if (!form.name.trim() || !form.start_date || !form.end_date) {
    Swal.fire({ icon: 'warning', title: 'Name, start and end date are required', confirmButtonColor: '#229DE7' }); return
  }
  loading.value = true
  try {
    const res = await PayrollPeriodService.create({ ...form, pay_date: form.pay_date || null })
    periods.value.unshift(res.data)
    toast('success', 'Payroll period created')
    form.name = ''; form.start_date = ''; form.end_date = ''; form.pay_date = ''
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this payroll period?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await PayrollPeriodService.remove(id); periods.value = periods.value.filter(p => p.id !== id); toast('success', 'Deleted') }
  catch (err) { Swal.fire({ icon: 'error', title: 'Delete Failed', text: err?.response?.data?.message || '', confirmButtonColor: '#229DE7' }) }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
