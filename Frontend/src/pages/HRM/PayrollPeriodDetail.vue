<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <router-link to="/hrm/payroll" class="text-xs font-bold text-slate-400 hover:text-[#229DE7] transition">&larr; Back to Payroll</router-link>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mt-1">{{ period?.name || 'Payroll Period' }}</h1>
        <p v-if="period" class="text-slate-500 mt-1 text-sm">{{ fmtDate(period.start_date) }} – {{ fmtDate(period.end_date) }}</p>
      </div>
      <div v-if="period" class="flex items-center gap-2 flex-wrap justify-end">
        <span class="px-3 py-1.5 rounded-full text-xs font-bold capitalize" :class="statusClass(period.status)">{{ period.status }}</span>
        <button v-if="['draft','calculated'].includes(period.status)" @click="doCalculate" :disabled="acting" class="px-4 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl transition-all disabled:opacity-60">
          {{ period.status === 'draft' ? 'Calculate' : 'Recalculate' }}
        </button>
        <button v-if="period.status === 'calculated'" @click="doApprove" :disabled="acting" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-all disabled:opacity-60">Approve</button>
        <button v-if="period.status === 'approved'" @click="doFinalize" :disabled="acting" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all disabled:opacity-60">Finalize</button>
        <button v-if="period.status === 'finalized'" @click="doMarkPaid" :disabled="acting" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all disabled:opacity-60">Mark Paid</button>
        <button v-if="['draft','calculated'].includes(period.status)" @click="doCancel" :disabled="acting" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-red-400 hover:text-red-500 text-slate-600 text-xs font-bold rounded-xl transition-all disabled:opacity-60">Cancel</button>
        <button v-if="['draft','calculated','cancelled'].includes(period.status)" @click="doDelete" :disabled="acting" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-red-400 hover:text-red-500 text-slate-600 text-xs font-bold rounded-xl transition-all disabled:opacity-60">Delete</button>
      </div>
    </div>

    <div v-if="loading" class="bg-white border border-[#D9EBF7] rounded-2xl p-6 space-y-3"><div v-for="n in 5" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>

    <template v-else-if="period">
      <!-- SUMMARY -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Employees</p>
          <p class="text-xl font-extrabold text-slate-900 mt-1">{{ payslips.length }}</p>
        </div>
        <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Gross Earnings</p>
          <p class="text-xl font-extrabold text-slate-900 mt-1">{{ fmtMoney(totals.gross) }}</p>
        </div>
        <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Deductions</p>
          <p class="text-xl font-extrabold text-slate-900 mt-1">{{ fmtMoney(totals.deductions) }}</p>
        </div>
        <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Net Pay</p>
          <p class="text-xl font-extrabold text-[#229DE7] mt-1">{{ fmtMoney(totals.net) }}</p>
        </div>
      </div>

      <!-- PAYSLIPS -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ payslips.length }} payslip(s)</span></div>

        <div v-if="payslips.length === 0" class="py-20 text-center">
          <p class="font-bold text-slate-700">No payslips yet</p>
          <p class="text-sm text-slate-400 mt-1">Click "Calculate" to generate payslips for every active employee.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                <th class="px-5 py-3.5">Employee</th>
                <th class="px-5 py-3.5 text-right">Basic</th>
                <th class="px-5 py-3.5 text-right">Gross</th>
                <th class="px-5 py-3.5 text-right">PAYE</th>
                <th class="px-5 py-3.5 text-right">NSSF</th>
                <th class="px-5 py-3.5 text-right">SHIF</th>
                <th class="px-5 py-3.5 text-right">Housing</th>
                <th class="px-5 py-3.5 text-right">Other</th>
                <th class="px-5 py-3.5 text-right">Advance</th>
                <th class="px-5 py-3.5 text-right">Net Pay</th>
                <th class="px-5 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="p in payslips" :key="p.id" class="hover:bg-[#F5FAFF] transition-colors" :class="{ 'opacity-50': p.is_excluded }">
                <td class="px-5 py-4">
                  <p class="font-bold text-sm text-slate-900">{{ p.employee?.full_name || '—' }}</p>
                  <p v-if="p.is_excluded" class="text-[10px] font-bold text-red-500 uppercase">Excluded</p>
                </td>
                <td class="px-5 py-4 text-right text-sm">{{ fmtMoney(p.basic_salary) }}</td>
                <td class="px-5 py-4 text-right text-sm font-bold">{{ fmtMoney(p.gross_earnings) }}</td>
                <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(p.paye) }}</td>
                <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(p.nssf_employee) }}</td>
                <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(p.shif) }}</td>
                <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(p.housing_levy_employee) }}</td>
                <td class="px-5 py-4 text-right text-sm text-slate-500">{{ fmtMoney(p.other_deductions) }}</td>
                <td class="px-5 py-4 text-right text-sm text-violet-600">{{ fmtMoney(p.advance_repayment) }}</td>
                <td class="px-5 py-4 text-right text-sm font-extrabold text-[#229DE7]">{{ fmtMoney(p.net_pay) }}</td>
                <td class="px-5 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="downloadPdf(p)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#EAF5FD] text-[#1682C4] hover:bg-[#D9EBF7] transition">PDF</button>
                    <button v-if="['calculated','approved'].includes(period.status)" @click="toggleExclude(p)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition" :class="p.is_excluded ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'">
                      {{ p.is_excluded ? 'Include' : 'Exclude' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Swal from 'sweetalert2'
import PayrollPeriodService from '@/services/PayrollPeriodService'
import PayslipService from '@/services/PayslipService'

const route = useRoute()
const periodId = route.params.id

const loading = ref(true)
const acting = ref(false)
const period = ref(null)
const payslips = ref([])

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || fallback, confirmButtonColor: '#229DE7' })
const fmtMoney = (v) => `KES ${Number(v ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : '—'
const statusClass = (s) => ({
  draft: 'bg-slate-100 text-slate-500',
  calculated: 'bg-amber-50 text-amber-700',
  approved: 'bg-sky-50 text-sky-700',
  finalized: 'bg-violet-50 text-violet-700',
  paid: 'bg-emerald-50 text-emerald-700',
  cancelled: 'bg-red-50 text-red-700',
}[s] || 'bg-slate-100 text-slate-600')

const totals = computed(() => {
  const included = payslips.value.filter(p => !p.is_excluded)
  return {
    gross: included.reduce((s, p) => s + Number(p.gross_earnings || 0), 0),
    deductions: included.reduce((s, p) => s + Number(p.paye || 0) + Number(p.nssf_employee || 0) + Number(p.shif || 0) + Number(p.housing_levy_employee || 0) + Number(p.other_deductions || 0) + Number(p.advance_repayment || 0), 0),
    net: included.reduce((s, p) => s + Number(p.net_pay || 0), 0),
  }
})

const load = async () => {
  loading.value = true
  try {
    const res = await PayrollPeriodService.getById(periodId)
    period.value = res.data
    payslips.value = res.data?.payslips ?? []
  } finally { loading.value = false }
}
onMounted(load)

const doCalculate = async () => {
  acting.value = true
  try {
    const res = await PayrollPeriodService.calculate(periodId)
    period.value = res.data
    payslips.value = res.data?.payslips ?? []
    toast('success', 'Payroll calculated')
  } catch (err) { errorAlert(err, 'Failed to calculate payroll') }
  finally { acting.value = false }
}

const doApprove = async () => {
  const r = await Swal.fire({ title: 'Approve this payroll?', icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Approve' })
  if (!r.isConfirmed) return
  acting.value = true
  try { const res = await PayrollPeriodService.approve(periodId); period.value = res.data; toast('success', 'Payroll approved') }
  catch (err) { errorAlert(err, 'Failed to approve') }
  finally { acting.value = false }
}

const doFinalize = async () => {
  const r = await Swal.fire({ title: 'Finalize this payroll?', text: 'No further edits or recalculation will be possible after this.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Finalize' })
  if (!r.isConfirmed) return
  acting.value = true
  try { const res = await PayrollPeriodService.finalize(periodId); period.value = res.data; toast('success', 'Payroll finalized') }
  catch (err) { errorAlert(err, 'Failed to finalize') }
  finally { acting.value = false }
}

const doMarkPaid = async () => {
  const r = await Swal.fire({ title: 'Mark this payroll as paid?', text: 'This books any salary advance repayments against employee balances.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Mark Paid' })
  if (!r.isConfirmed) return
  acting.value = true
  try { const res = await PayrollPeriodService.markPaid(periodId); period.value = res.data; toast('success', 'Payroll marked as paid') }
  catch (err) { errorAlert(err, 'Failed to mark as paid') }
  finally { acting.value = false }
}

const doCancel = async () => {
  const r = await Swal.fire({ title: 'Cancel this payroll period?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, cancel' })
  if (!r.isConfirmed) return
  acting.value = true
  try { const res = await PayrollPeriodService.cancel(periodId); period.value = res.data; toast('success', 'Payroll period cancelled') }
  catch (err) { errorAlert(err, 'Failed to cancel') }
  finally { acting.value = false }
}

const doDelete = async () => {
  const r = await Swal.fire({ title: 'Delete this payroll period?', text: 'This permanently removes it and its payslips.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try {
    await PayrollPeriodService.remove(periodId)
    toast('success', 'Deleted')
    window.location.href = '/hrm/payroll'
  } catch (err) { errorAlert(err, 'Failed to delete') }
}

const toggleExclude = async (payslip) => {
  try {
    const res = await PayrollPeriodService.toggleExclude(periodId, payslip.id, !payslip.is_excluded)
    const idx = payslips.value.findIndex(p => p.id === payslip.id)
    if (idx !== -1) payslips.value[idx] = { ...payslips.value[idx], ...res.data }
    toast('success', res.message)
  } catch (err) { errorAlert(err, 'Failed to update') }
}

const downloadPdf = async (payslip) => {
  try {
    const blob = await PayslipService.downloadPdf(payslip.id)
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `payslip-${payslip.employee?.employee_number || payslip.id}.pdf`
    link.click()
    URL.revokeObjectURL(url)
  } catch (err) {
    // A failed blob-typed request still comes back as a Blob (axios applies
    // responseType to error bodies too), so the real JSON error message
    // never surfaces on its own — read it back out explicitly.
    let message = 'Something went wrong.'
    if (err?.response?.data instanceof Blob) {
      try { message = JSON.parse(await err.response.data.text())?.message || message } catch { /* not JSON */ }
    } else {
      message = err?.response?.data?.message || message
    }
    Swal.fire({ icon: 'error', title: 'Failed to download payslip', text: message, confirmButtonColor: '#229DE7' })
  }
}
</script>
