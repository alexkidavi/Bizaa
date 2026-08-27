<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Campaigns</h1>
        <p class="text-slate-500 mt-1 text-sm">Bulk SMS, WhatsApp and Email to a segment of your contacts.</p>
      </div>
    </div>

    <div v-if="!isConfigured" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800">
      <b>Heads up:</b> no SMS or WhatsApp provider is connected yet — sends will run in dry-run mode (logged, not actually delivered) until credentials are configured. Email already sends for real once your mail settings are set to a live mailer.
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">New Campaign</h2>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="form.name" type="text" class="field-input" placeholder="e.g. August Promo" />
            </div>
            <div>
              <label class="field-label required">Channel</label>
              <div class="grid grid-cols-3 gap-2">
                <button v-for="ch in channels" :key="ch.value" type="button" @click="form.channel = ch.value; runPreview()"
                  class="py-2 rounded-xl text-xs font-bold border transition"
                  :class="form.channel === ch.value ? 'bg-[#229DE7] border-[#229DE7] text-white' : 'bg-white border-[#D9EBF7] text-slate-600 hover:border-[#229DE7]'">
                  {{ ch.label }}
                </button>
              </div>
            </div>

            <div class="border-t border-[#EEF5FB] pt-4">
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Segment</p>
              <div class="space-y-2">
                <select v-model="form.filters.lifecycle_stage" class="field-input" @change="runPreview">
                  <option value="">Any stage</option>
                  <option value="lead">Lead</option>
                  <option value="qualified">Qualified</option>
                  <option value="customer">Customer</option>
                  <option value="lost">Lost</option>
                </select>
                <select v-model="form.filters.tag_id" class="field-input" @change="runPreview">
                  <option value="">Any tag</option>
                  <option v-for="t in tags" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <select v-model="form.filters.company_id" class="field-input" @change="runPreview">
                  <option value="">Any company</option>
                  <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model="form.filters.source_id" class="field-input" @change="runPreview">
                  <option value="">Any source</option>
                  <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <div v-if="preview" class="mt-3 px-3 py-2 rounded-xl text-xs font-bold" :class="preview.eligible_count > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                Reaches {{ preview.eligible_count }} contact(s)<span v-if="preview.skipped_count"> · {{ preview.skipped_count }} skipped (missing {{ form.channel === 'email' ? 'email' : 'phone' }})</span>
              </div>
            </div>

            <div v-if="form.channel === 'email'">
              <label class="field-label required">Subject</label>
              <input v-model="form.subject" type="text" class="field-input" placeholder="e.g. Special Offer" />
            </div>
            <div>
              <label class="field-label required">Message</label>
              <textarea v-model="form.message" rows="4" class="field-input" :placeholder="'Hi ' + placeholderTag + ', ...'"></textarea>
              <p class="text-[11px] text-slate-400 mt-1">Use <code>{{ placeholderTag }}</code> to personalize with each contact's name.</p>
            </div>

            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Saving...' : 'Save as Draft' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ campaigns.length }} campaign(s)</span></div>
          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="campaigns.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No campaigns yet</p></div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Campaign</th>
                  <th class="px-5 py-3.5">Channel</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-center">Sent / Total</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="c in campaigns" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors cursor-pointer" @click="openCampaign(c)">
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ c.name }}</td>
                  <td class="px-5 py-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#EAF5FD] text-[#1682C4]">{{ c.channel }}</span></td>
                  <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="statusClass(c.status)">{{ c.status }}</span></td>
                  <td class="px-5 py-4 text-center text-sm font-bold text-slate-700">{{ c.sent_count }} / {{ c.recipients_count }}</td>
                  <td class="px-5 py-4 text-right" @click.stop>
                    <button v-if="c.status === 'draft'" @click="doSend(c)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#229DE7] text-white hover:bg-[#1682C4] transition">Send Now</button>
                    <button v-if="c.status === 'draft'" @click="remove(c.id)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <!-- DETAIL MODAL -->
    <div v-if="activeCampaign" class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50 p-4" @click.self="activeCampaign = null">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-start justify-between">
          <div>
            <h2 class="font-extrabold text-lg text-slate-900">{{ activeCampaign.name }}</h2>
            <p class="text-xs text-slate-400 mt-0.5 uppercase">{{ activeCampaign.channel }}</p>
          </div>
          <button @click="activeCampaign = null" class="text-slate-400 hover:text-red-500 text-xs font-bold">Close</button>
        </div>
        <div class="p-6 space-y-4 overflow-y-auto">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="statusClass(activeCampaign.status)">{{ activeCampaign.status }}</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">{{ activeCampaign.sent_count }} sent / {{ activeCampaign.failed_count }} failed / {{ activeCampaign.recipients_count }} total</span>
          </div>
          <div class="p-3 bg-[#F5FAFF] rounded-xl text-xs text-slate-600 whitespace-pre-wrap">{{ activeCampaign.message }}</div>
          <button v-if="activeCampaign.status === 'draft'" @click="doSend(activeCampaign)" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">Send Now</button>

          <div v-if="activeCampaign.recipients?.length" class="border-t border-[#EEF5FB] pt-4">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Recipients</p>
            <div class="space-y-1 max-h-64 overflow-y-auto">
              <div v-for="r in activeCampaign.recipients" :key="r.id" class="flex items-center justify-between px-2 py-1.5 rounded-lg hover:bg-slate-50 text-xs">
                <span class="text-slate-700">{{ r.contact?.name }}</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="r.status === 'sent' ? 'bg-emerald-50 text-emerald-700' : r.status === 'failed' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500'">{{ r.status }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import Swal from 'sweetalert2'
import CrmCampaignService from '@/services/CrmCampaignService'
import CrmTagService from '@/services/CrmTagService'
import CrmCompanyService from '@/services/CrmCompanyService'
import CrmSourceService from '@/services/CrmSourceService'

const tableLoading = ref(true)
const loading = ref(false)
const campaigns = ref([])
const tags = ref([])
const companies = ref([])
const sources = ref([])
const preview = ref(null)
const isConfigured = ref(true) // informational banner only; real gating happens server-side via dry-run providers
const placeholderTag = '{{name}}'

const channels = [
  { label: 'SMS', value: 'sms' },
  { label: 'WhatsApp', value: 'whatsapp' },
  { label: 'Email', value: 'email' },
]

const defaultForm = () => ({ name: '', channel: 'sms', subject: '', message: '', filters: { lifecycle_stage: '', tag_id: '', company_id: '', source_id: '' } })
const form = reactive(defaultForm())

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })
const statusClass = (s) => ({ draft: 'bg-slate-100 text-slate-500', sending: 'bg-amber-50 text-amber-700', sent: 'bg-emerald-50 text-emerald-700', failed: 'bg-red-50 text-red-600' }[s] || 'bg-slate-100 text-slate-600')

const cleanFilters = () => {
  const f = {}
  Object.entries(form.filters).forEach(([k, v]) => { if (v !== '' && v !== null) f[k] = v })
  return f
}

const runPreview = async () => {
  try { preview.value = await CrmCampaignService.previewRecipients(form.channel, cleanFilters()) }
  catch { preview.value = null }
}

const load = async () => {
  tableLoading.value = true
  try { const res = await CrmCampaignService.getAll(); campaigns.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}

onMounted(async () => {
  const [tagRes, compRes, sourceRes] = await Promise.all([
    CrmTagService.getAll(),
    CrmCompanyService.getAll(),
    CrmSourceService.getAll({ active_only: 1 }),
  ])
  tags.value = tagRes?.data ?? []
  companies.value = compRes?.data ?? []
  sources.value = sourceRes?.data ?? []
  await load()
  await runPreview()

  // Resume polling for any campaign still mid-send from before this page load.
  const stillSending = campaigns.value.find(c => c.status === 'sending')
  if (stillSending) pollUntilSettled(stillSending.id)
})

const save = async () => {
  if (!form.name.trim() || !form.message.trim()) { Swal.fire({ icon: 'warning', title: 'Name and message are required', confirmButtonColor: '#229DE7' }); return }
  if (form.channel === 'email' && !form.subject.trim()) { Swal.fire({ icon: 'warning', title: 'Subject is required for email campaigns', confirmButtonColor: '#229DE7' }); return }
  loading.value = true
  try {
    const res = await CrmCampaignService.create({ ...form, filters: cleanFilters() })
    campaigns.value.unshift({ ...res.data, recipients_count: 0, sent_count: 0, failed_count: 0 })
    toast('success', 'Campaign saved as draft')
    Object.assign(form, defaultForm())
    preview.value = null
  } catch (err) { errorAlert(err, 'Failed to save campaign') }
  finally { loading.value = false }
}

const openCampaign = async (c) => {
  const res = await CrmCampaignService.getById(c.id)
  activeCampaign.value = res.data
}
const activeCampaign = ref(null)

// Sending is async now (queued jobs) — the send() response comes back
// immediately with status='sending', so poll until the batch's finally()
// callback settles it to sent/failed rather than leaving the row looking
// stuck until the user manually reloads the page.
let pollTimer = null
const stopPolling = () => { if (pollTimer) { clearInterval(pollTimer); pollTimer = null } }
const pollUntilSettled = (campaignId) => {
  stopPolling()
  pollTimer = setInterval(async () => {
    try {
      const res = await CrmCampaignService.getById(campaignId)
      const idx = campaigns.value.findIndex(x => x.id === campaignId)
      if (idx !== -1) campaigns.value[idx] = { ...campaigns.value[idx], ...res.data }
      if (activeCampaign.value?.id === campaignId) activeCampaign.value = res.data
      if (res.data.status !== 'sending') stopPolling()
    } catch { stopPolling() }
  }, 3000)
}
onUnmounted(stopPolling)

const doSend = async (c) => {
  const r = await Swal.fire({ title: `Send "${c.name}" now?`, text: `This will send to ${c.recipients_count || 'the matching'} contact(s) — delivery happens in the background.`, icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Send Now' })
  if (!r.isConfirmed) return
  try {
    const res = await CrmCampaignService.send(c.id)
    const idx = campaigns.value.findIndex(x => x.id === c.id)
    if (idx !== -1) campaigns.value[idx] = { ...campaigns.value[idx], ...res.data }
    if (activeCampaign.value?.id === c.id) activeCampaign.value = res.data
    toast('success', res.data.status === 'sending' ? 'Campaign is sending…' : `Campaign ${res.data.status}`)
    if (res.data.status === 'sending') pollUntilSettled(c.id)
  } catch (err) { errorAlert(err, 'Failed to send') }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this campaign?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await CrmCampaignService.remove(id); campaigns.value = campaigns.value.filter(c => c.id !== id); toast('success', 'Deleted') }
  catch (err) { errorAlert(err, 'Failed to delete') }
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
