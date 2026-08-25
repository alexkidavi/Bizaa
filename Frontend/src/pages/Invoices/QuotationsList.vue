<!-- ================================================================
     QuotationsList.vue
     ================================================================ -->
<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Quotations</h1>
        <p class="text-slate-500 mt-1 text-sm">Create and manage customer quotations.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative w-full md:w-64">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input v-model="searchQuery" type="text" placeholder="Search quotations..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-sm text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-[#229DE7]/20 focus:border-[#229DE7] transition"/>
        </div>
        <router-link :to="{ name: 'NewQuotation' }" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] whitespace-nowrap">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          New Quotation
        </router-link>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total</p>
        <p class="text-xl font-extrabold text-slate-800">{{ quotations.length }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Accepted</p>
        <p class="text-xl font-extrabold text-emerald-600">{{ quotations.filter(q => q.status === 'accepted').length }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Pending</p>
        <p class="text-xl font-extrabold text-amber-500">{{ quotations.filter(q => ['draft','sent'].includes(q.status)).length }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">Converted</p>
        <p class="text-xl font-extrabold text-[#229DE7]">{{ quotations.filter(q => q.status === 'converted').length }}</p>
      </div>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <!-- Filter -->
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-2">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mr-1">Status:</span>
        <button v-for="f in statusFilters" :key="f.value" @click="activeFilter = f.value"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition"
          :class="activeFilter === f.value ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
          {{ f.label }}
        </button>
      </div>

      <div v-if="tableLoading" class="p-6 space-y-3">
        <div v-for="n in 5" :key="n" class="h-14 bg-slate-50 animate-pulse rounded-xl"></div>
      </div>

      <div v-else-if="filteredQuotations.length === 0" class="py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-[#229DE7]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <h3 class="font-bold text-base text-slate-900">No quotations found</h3>
        <p class="text-slate-500 text-sm mt-1">Create a quotation to send to a customer.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5 w-10">#</th>
              <th class="px-5 py-3.5">Reference</th>
              <th class="px-5 py-3.5">Customer</th>
              <th class="px-5 py-3.5">Issue Date</th>
              <th class="px-5 py-3.5">Expiry Date</th>
              <th class="px-5 py-3.5">Total</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="(q, index) in paginatedQuotations" :key="q.id" class="group hover:bg-[#F5FAFF] transition-colors">
              <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
              <td class="px-5 py-4 font-extrabold text-sm text-slate-800">{{ q.quotation_number }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-[10px] flex-shrink-0">
                    {{ (q.customer?.name || 'C').substring(0,2).toUpperCase() }}
                  </div>
                  <span class="font-bold text-sm text-slate-800">{{ q.customer?.name || '—' }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-xs text-slate-500 font-semibold whitespace-nowrap">{{ formatDate(q.issue_date) }}</td>
              <td class="px-5 py-4 text-xs font-semibold whitespace-nowrap" :class="isExpired(q) && q.status !== 'accepted' ? 'text-red-500 font-bold' : 'text-slate-500'">
                {{ formatDate(q.expiry_date) }}
                <span v-if="isExpired(q) && q.status !== 'accepted'" class="ml-1 text-[9px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-bold">EXPIRED</span>
              </td>
              <td class="px-5 py-4 font-extrabold text-sm text-slate-800">{{ formatCurrency(q.grand_total) }}</td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border" :class="quotationStatusClass(q.status)">
                  {{ quotationStatusLabel(q.status) }}
                </span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <!-- Convert to Invoice -->
                  <button v-if="q.status === 'accepted' && !q.invoice_id" @click="convertToInvoice(q)"
                    class="px-3 py-1.5 rounded-lg text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition whitespace-nowrap" title="Convert to Invoice">
                    → Invoice
                  </button>
                  <router-link :to="{ name: 'EditQuotation', params: { id: q.id } }" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] border border-transparent hover:bg-[#EAF5FD] hover:border-[#B3DEFA] transition" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </router-link>
                  <button @click="updateStatus(q)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 border border-transparent hover:bg-slate-100 hover:border-slate-200 transition" title="Update Status">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  </button>
                  <button @click="deleteQuotation(q.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 border border-transparent hover:bg-red-50 hover:border-red-200 transition" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="filteredQuotations.length > 0" class="px-6 py-4 border-t border-[#EEF5FB] flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-sm text-slate-500 font-semibold">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }}–{{ Math.min(currentPage * itemsPerPage, filteredQuotations.length) }} of {{ filteredQuotations.length }} quotations
        </div>
        <div class="flex items-center gap-2">
          <button :disabled="currentPage === 1" @click="currentPage--" class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95">← Previous</button>
          <div class="flex items-center px-4 py-2 bg-[#229DE7] rounded-xl text-sm font-bold text-white shadow-md shadow-[#229DE7]/30">{{ currentPage }} / {{ totalPages }}</div>
          <button :disabled="currentPage >= totalPages" @click="currentPage++" class="px-5 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] disabled:opacity-40 transition shadow-sm active:scale-95">Next →</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import QuotationService from '@/services/QuotationService'

const router = useRouter()
const pageLoading = ref(true)
const tableLoading = ref(false)
const quotations = ref([])
const searchQuery = ref('')
const activeFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = 10

const statusFilters = [
  { label: 'All', value: 'all' },
  { label: 'Draft', value: 'draft' },
  { label: 'Sent', value: 'sent' },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Expired', value: 'expired' },
  { label: 'Converted', value: 'converted' },
]

const filteredQuotations = computed(() =>
  quotations.value.filter(q => {
    const matchSearch = (q.quotation_number || '').toLowerCase().includes(searchQuery.value.toLowerCase()) || (q.customer?.name || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchFilter = activeFilter.value === 'all' || q.status === activeFilter.value
    return matchSearch && matchFilter
  })
)

const totalPages = computed(() => Math.max(1, Math.ceil(filteredQuotations.value.length / itemsPerPage)))
const paginatedQuotations = computed(() => filteredQuotations.value.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage))

watch([searchQuery, activeFilter], () => { currentPage.value = 1 })

const formatDate = val => val ? new Date(val).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatCurrency = val => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', minimumFractionDigits: 2 }).format(Number(val ?? 0))
const isExpired = q => q.expiry_date && new Date(q.expiry_date) < new Date()

const quotationStatusClass = status => ({
  draft:     'bg-slate-100 text-slate-500 border-slate-200',
  sent:      'bg-blue-50 text-blue-700 border-blue-200',
  accepted:  'bg-emerald-50 text-emerald-700 border-emerald-200',
  rejected:  'bg-red-50 text-red-600 border-red-200',
  expired:   'bg-orange-50 text-orange-600 border-orange-200',
  converted: 'bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]',
}[status] || 'bg-slate-100 text-slate-500 border-slate-200')

const quotationStatusLabel = status => ({
  draft: 'Draft', sent: 'Sent', accepted: 'Accepted',
  rejected: 'Rejected', expired: 'Expired', converted: 'Converted to Invoice',
}[status] || status)

const notify = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

onMounted(async () => {
  pageLoading.value = true; tableLoading.value = true
  try {
    const res = await QuotationService.getAll()
    quotations.value = res?.data?.data ?? res?.data ?? []
  } catch { Swal.fire('Error', 'Could not load quotations.', 'error') }
  finally { tableLoading.value = false; setTimeout(() => { pageLoading.value = false }, 500) }
})

const convertToInvoice = async q => {
  const result = await Swal.fire({
    title: 'Convert to Invoice?',
    text: `This will create an invoice from quotation ${q.quotation_number}.`,
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#229DE7', confirmButtonText: 'Yes, convert',
  })
  if (!result.isConfirmed) return
  try {
    const res = await QuotationService.convertToInvoice(q.id)
    const invoice = res?.data?.data ?? res?.data
    notify('success', 'Converted to Invoice!')
    router.push({ name: 'ViewInvoice', params: { id: invoice.id } })
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Conversion Failed', text: err?.response?.data?.message || 'Error.', confirmButtonColor: '#229DE7' })
  }
}

const updateStatus = async q => {
  const statuses = ['draft', 'sent', 'accepted', 'rejected']
  const { value: newStatus } = await Swal.fire({
    title: 'Update Status',
    input: 'select',
    inputOptions: Object.fromEntries(statuses.map(s => [s, s.charAt(0).toUpperCase() + s.slice(1)])),
    inputValue: q.status,
    showCancelButton: true,
    confirmButtonColor: '#229DE7',
    confirmButtonText: 'Update',
  })
  if (!newStatus || newStatus === q.status) return
  try {
    await QuotationService.updateStatus(q.id, newStatus)
    q.status = newStatus
    notify('success', 'Status Updated')
  } catch { Swal.fire({ icon: 'error', title: 'Update Failed', confirmButtonColor: '#229DE7' }) }
}

const deleteQuotation = async id => {
  const result = await Swal.fire({ title: 'Delete this quotation?', text: 'Cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!result.isConfirmed) return
  try {
    await QuotationService.delete(id)
    quotations.value = quotations.value.filter(q => q.id !== id)
    notify('success', 'Quotation Deleted')
  } catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
