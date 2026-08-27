<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Deals</h1>
        <p class="text-slate-500 mt-1 text-sm">Drag a card to move it through your pipeline.</p>
      </div>
      <div class="flex items-center gap-2">
        <router-link to="/crm/pipeline-stages" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">Stages</router-link>
        <button @click="openNewDeal" class="px-4 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all whitespace-nowrap">+ New Deal</button>
      </div>
    </div>

    <div class="flex items-center gap-2 mb-6">
      <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; loadDeals()"
        class="px-3 py-1.5 rounded-lg text-xs font-bold border transition"
        :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
        {{ f.label }}
      </button>
    </div>

    <div v-if="loading" class="flex gap-4 overflow-x-auto pb-4">
      <div v-for="n in 5" :key="n" class="w-72 flex-shrink-0 h-64 bg-white/60 border border-[#D9EBF7] rounded-2xl animate-pulse"></div>
    </div>

    <div v-else class="flex gap-4 overflow-x-auto pb-4 board">
      <div v-for="stage in stages" :key="stage.id" class="w-72 flex-shrink-0 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm flex flex-col max-h-[calc(100vh-220px)]"
        @dragover.prevent @drop="onDrop(stage.id)">
        <div class="px-4 py-3 border-b border-[#EEF5FB] rounded-t-2xl" :class="stage.is_won ? 'bg-emerald-50' : stage.is_lost ? 'bg-red-50' : 'bg-[#EAF5FD]'">
          <div class="flex items-center justify-between">
            <span class="font-extrabold text-xs uppercase tracking-widest" :class="stage.is_won ? 'text-emerald-700' : stage.is_lost ? 'text-red-600' : 'text-[#1682C4]'">{{ stage.name }}</span>
            <span class="text-[10px] font-bold text-slate-400">{{ dealsFor(stage.id).length }}</span>
          </div>
          <p class="text-xs font-bold text-slate-600 mt-0.5">{{ fmtMoney(stageTotal(stage.id)) }}</p>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
          <div v-for="d in dealsFor(stage.id)" :key="d.id" draggable="true" @dragstart="onDragStart(d.id)" @click="openDeal(d)"
            class="bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl p-3 cursor-pointer hover:border-[#229DE7] hover:shadow-sm transition group">
            <p class="font-bold text-sm text-slate-900 group-hover:text-[#1682C4] transition">{{ d.title }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ d.contact?.name }}</p>
            <div class="flex items-center justify-between mt-2">
              <span class="text-xs font-extrabold text-[#229DE7]">{{ fmtMoney(d.value) }}</span>
              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-white border border-[#D9EBF7] text-slate-500">{{ Number(d.probability) }}%</span>
            </div>
          </div>
          <div v-if="dealsFor(stage.id).length === 0" class="text-center text-xs text-slate-300 py-6">No deals</div>
        </div>
      </div>
    </div>

    <!-- NEW DEAL MODAL -->
    <div v-if="showNewDeal" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="showNewDeal = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">New Deal</h2>
        </div>
        <form @submit.prevent="createDeal" class="p-6 space-y-4">
          <div>
            <label class="field-label required">Contact</label>
            <select v-model="newDealForm.contact_id" class="field-input">
              <option value="" disabled>Select contact...</option>
              <option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="field-label required">Title</label>
            <input v-model="newDealForm.title" type="text" class="field-input" placeholder="e.g. Bulk stationery order" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="field-label">Value (KES)</label>
              <input v-model.number="newDealForm.value" type="number" step="0.01" min="0" class="field-input" />
            </div>
            <div>
              <label class="field-label">Expected Close</label>
              <input v-model="newDealForm.expected_close_date" type="date" class="field-input" />
            </div>
          </div>
          <div>
            <label class="field-label">Assigned To</label>
            <select v-model="newDealForm.assigned_to" class="field-input">
              <option :value="null">Unassigned</option>
              <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="flex gap-3">
            <button type="button" @click="showNewDeal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition">Cancel</button>
            <button type="submit" :disabled="creating" class="flex-1 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition disabled:opacity-60">{{ creating ? 'Creating...' : 'Create Deal' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- DEAL DETAIL MODAL -->
    <div v-if="activeDeal" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="closeDeal">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-start justify-between">
          <div>
            <h2 class="font-extrabold text-lg text-slate-900">{{ activeDeal.title }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ activeDeal.contact?.name }} · {{ activeDeal.contact?.phone || activeDeal.contact?.email || '—' }}</p>
          </div>
          <button @click="closeDeal" class="text-slate-400 hover:text-red-500 text-xs font-bold">Close</button>
        </div>
        <div class="p-6 space-y-4 overflow-y-auto">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="statusClass(activeDeal.status)">{{ activeDeal.status }}</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ activeDeal.pipeline_stage?.name }}</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">{{ fmtMoney(activeDeal.value) }} · {{ Number(activeDeal.probability) }}%</span>
          </div>

          <div v-if="activeDeal.status === 'lost' && activeDeal.lost_reason" class="p-3 bg-red-50 rounded-xl text-xs text-red-700">
            <span class="font-bold">Lost reason:</span> {{ activeDeal.lost_reason }}
          </div>

          <div v-if="activeDeal.status === 'open'" class="flex gap-2">
            <button @click="doMarkWon" class="flex-1 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition">Mark Won</button>
            <button @click="doMarkLost" class="flex-1 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl transition">Mark Lost</button>
          </div>

          <div class="border-t border-[#EEF5FB] pt-4">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Linked Documents</p>
            <div v-if="!activeDeal.contact?.customer_id" class="text-xs text-slate-400 bg-slate-50 rounded-xl p-3">
              This contact hasn't been converted to a customer yet — convert it under Contacts to link a quotation or sale.
            </div>
            <template v-else>
              <div class="flex items-center justify-between p-2.5 bg-[#F5FAFF] rounded-xl mb-2">
                <span class="text-xs text-slate-600">Quotation: <b>{{ activeDeal.quotation?.quotation_number || 'none' }}</b></span>
                <button @click="openLinkPicker('quotation')" class="text-[11px] font-bold text-[#229DE7] hover:underline">{{ activeDeal.quotation ? 'Change' : 'Link' }}</button>
              </div>
              <div class="flex items-center justify-between p-2.5 bg-[#F5FAFF] rounded-xl">
                <span class="text-xs text-slate-600">Sale: <b>{{ activeDeal.sale?.reference || 'none' }}</b></span>
                <button @click="openLinkPicker('sale')" class="text-[11px] font-bold text-[#229DE7] hover:underline">{{ activeDeal.sale ? 'Change' : 'Link' }}</button>
              </div>
            </template>
          </div>

          <div v-if="linkPickerType" class="border border-[#D9EBF7] rounded-xl p-3 space-y-2">
            <p class="text-[11px] font-bold text-slate-500 uppercase">Select {{ linkPickerType }}</p>
            <select v-model="linkPickerValue" class="field-input">
              <option value="" disabled>Choose...</option>
              <option v-for="opt in linkPickerOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
            <div class="flex gap-2">
              <button @click="linkPickerType = null" class="flex-1 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg">Cancel</button>
              <button @click="confirmLink" class="flex-1 py-2 bg-[#229DE7] text-white text-xs font-bold rounded-lg">Confirm</button>
            </div>
          </div>

          <div v-if="activeDeal.notes" class="border-t border-[#EEF5FB] pt-4">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Notes</p>
            <p class="text-sm text-slate-600">{{ activeDeal.notes }}</p>
          </div>

          <div class="border-t border-[#EEF5FB] pt-4">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Activity</p>
            <ActivityTimeline :deal-id="activeDeal.id" />
          </div>
        </div>
        <div class="px-6 py-4 border-t border-[#EEF5FB] flex justify-end">
          <button @click="doDeleteDeal" class="text-xs font-bold text-red-500 hover:text-red-600">Delete Deal</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'
import CrmDealService from '@/services/CrmDealService'
import CrmPipelineStageService from '@/services/CrmPipelineStageService'
import CrmContactService from '@/services/CrmContactService'
import QuotationService from '@/services/QuotationService'
import SaleService from '@/services/SaleService'
import StaffService from '@/services/StaffService'
import ActivityTimeline from '@/components/CRM/ActivityTimeline.vue'

const loading = ref(true)
const creating = ref(false)
const stages = ref([])
const deals = ref([])
const contacts = ref([])
const staff = ref([])
const filterStatus = ref('open')
const draggingId = ref(null)

const statusFilters = [
  { label: 'Open', value: 'open' },
  { label: 'Won', value: 'won' },
  { label: 'Lost', value: 'lost' },
  { label: 'All', value: '' },
]

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })
const fmtMoney = (v) => `KES ${Number(v ?? 0).toLocaleString('en-KE', { maximumFractionDigits: 0 })}`
const statusClass = (s) => ({ open: 'bg-sky-50 text-sky-700', won: 'bg-emerald-50 text-emerald-700', lost: 'bg-red-50 text-red-600' }[s] || 'bg-slate-100 text-slate-500')

const dealsFor = (stageId) => deals.value.filter(d => d.pipeline_stage_id === stageId)
const stageTotal = (stageId) => dealsFor(stageId).reduce((s, d) => s + Number(d.value || 0), 0)

const loadDeals = async () => {
  const res = await CrmDealService.getAll({ status: filterStatus.value })
  deals.value = res?.data ?? []
}

onMounted(async () => {
  loading.value = true
  try {
    const [stageRes, contactRes, staffRes] = await Promise.all([
      CrmPipelineStageService.getAll(),
      CrmContactService.getAll({ per_page: 500 }),
      StaffService.getAllStaff(),
    ])
    stages.value = stageRes?.data ?? []
    contacts.value = contactRes?.data ?? []
    staff.value = staffRes?.data?.data ?? staffRes?.data ?? []
    await loadDeals()
  } finally { loading.value = false }
})

/* ---------------- Drag & drop ---------------- */
const onDragStart = (id) => { draggingId.value = id }
const onDrop = async (stageId) => {
  if (!draggingId.value) return
  const dealId = draggingId.value
  draggingId.value = null
  const deal = deals.value.find(d => d.id === dealId)
  if (!deal || deal.pipeline_stage_id === stageId) return
  try {
    const res = await CrmDealService.moveStage(dealId, stageId)
    const idx = deals.value.findIndex(d => d.id === dealId)
    if (idx !== -1) deals.value[idx] = res.data
    if (filterStatus.value && res.data.status !== filterStatus.value) {
      deals.value = deals.value.filter(d => d.id !== dealId)
    }
  } catch (err) { errorAlert(err, 'Failed to move deal') }
}

/* ---------------- New deal ---------------- */
const showNewDeal = ref(false)
const defaultNewDealForm = () => ({ contact_id: '', title: '', value: 0, expected_close_date: '', assigned_to: null })
const newDealForm = reactive(defaultNewDealForm())

const openNewDeal = () => { Object.assign(newDealForm, defaultNewDealForm()); showNewDeal.value = true }
const createDeal = async () => {
  if (!newDealForm.contact_id || !newDealForm.title.trim()) { Swal.fire({ icon: 'warning', title: 'Contact and title are required', confirmButtonColor: '#229DE7' }); return }
  creating.value = true
  try {
    const res = await CrmDealService.create({ ...newDealForm })
    deals.value.unshift(res.data)
    toast('success', 'Deal created')
    showNewDeal.value = false
  } catch (err) { errorAlert(err, 'Failed to create deal') }
  finally { creating.value = false }
}

/* ---------------- Deal detail ---------------- */
const activeDeal = ref(null)
const linkPickerType = ref(null)
const linkPickerValue = ref('')
const linkPickerOptions = ref([])

const openDeal = (d) => { activeDeal.value = d; linkPickerType.value = null }
const closeDeal = () => { activeDeal.value = null; linkPickerType.value = null }

const refreshActiveDeal = (updated) => {
  activeDeal.value = updated
  const idx = deals.value.findIndex(d => d.id === updated.id)
  if (idx !== -1) deals.value[idx] = updated
}

const doMarkWon = async () => {
  const r = await Swal.fire({ title: 'Mark this deal as won?', icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Mark Won' })
  if (!r.isConfirmed) return
  try { const res = await CrmDealService.markWon(activeDeal.value.id); refreshActiveDeal(res.data); toast('success', 'Deal marked as won') }
  catch (err) { errorAlert(err, 'Failed to mark won') }
}

const doMarkLost = async () => {
  const r = await Swal.fire({ title: 'Mark this deal as lost', input: 'text', inputLabel: 'Reason', inputPlaceholder: 'e.g. Went with a competitor', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Mark Lost', inputValidator: (v) => !v ? 'A reason is required' : null })
  if (!r.isConfirmed) return
  try { const res = await CrmDealService.markLost(activeDeal.value.id, r.value); refreshActiveDeal(res.data); toast('success', 'Deal marked as lost') }
  catch (err) { errorAlert(err, 'Failed to mark lost') }
}

const openLinkPicker = async (type) => {
  linkPickerType.value = type
  linkPickerValue.value = ''
  const customerId = activeDeal.value.contact?.customer_id
  if (type === 'quotation') {
    const res = await QuotationService.getAll()
    const list = res?.data?.data ?? res?.data ?? []
    linkPickerOptions.value = list.filter(q => q.customer_id === customerId).map(q => ({ id: q.id, label: `${q.quotation_number} — KES ${Number(q.grand_total).toLocaleString()}` }))
  } else {
    const res = await SaleService.getAll()
    const list = res?.data?.data ?? res?.data ?? []
    linkPickerOptions.value = list.filter(s => s.customer_id === customerId).map(s => ({ id: s.id, label: `${s.reference} — KES ${Number(s.total).toLocaleString()}` }))
  }
}

const confirmLink = async () => {
  if (!linkPickerValue.value) return
  try {
    const res = linkPickerType.value === 'quotation'
      ? await CrmDealService.linkQuotation(activeDeal.value.id, linkPickerValue.value)
      : await CrmDealService.linkSale(activeDeal.value.id, linkPickerValue.value)
    refreshActiveDeal(res.data)
    linkPickerType.value = null
    toast('success', `${linkPickerType.value === 'quotation' ? 'Quotation' : 'Sale'} linked`)
  } catch (err) { errorAlert(err, 'Failed to link') }
}

const doDeleteDeal = async () => {
  const r = await Swal.fire({ title: 'Delete this deal?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try {
    await CrmDealService.remove(activeDeal.value.id)
    deals.value = deals.value.filter(d => d.id !== activeDeal.value.id)
    toast('success', 'Deal deleted')
    closeDeal()
  } catch (err) { errorAlert(err, 'Failed to delete') }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
.board::-webkit-scrollbar { height: 8px; }
.board::-webkit-scrollbar-thumb { background: #D9EBF7; border-radius: 4px; }
</style>
