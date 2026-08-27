<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Contacts</h1>
        <p class="text-slate-500 mt-1 text-sm">Leads and contacts, from first touch to paying customer.</p>
      </div>
      <div class="flex items-center gap-2">
        <router-link to="/crm/companies" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">Companies</router-link>
        <router-link to="/crm/sources" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">Sources</router-link>
        <router-link to="/crm/tags" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">Tags</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? 'Edit Contact' : 'Add Contact' }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="form.name" type="text" class="field-input" placeholder="e.g. Jane Mwikali" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Phone</label>
                <input v-model="form.phone" type="text" class="field-input" placeholder="2547..." />
              </div>
              <div>
                <label class="field-label">Email</label>
                <input v-model="form.email" type="email" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Company</label>
              <select v-model="form.company_id" class="field-input">
                <option :value="null">No company</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Stage</label>
                <select v-model="form.lifecycle_stage" class="field-input">
                  <option value="lead">Lead</option>
                  <option value="qualified">Qualified</option>
                  <option value="customer">Customer</option>
                  <option value="lost">Lost</option>
                </select>
              </div>
              <div>
                <label class="field-label">Source</label>
                <select v-model="form.source_id" class="field-input">
                  <option :value="null">No source</option>
                  <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="field-label">Assigned To</label>
              <select v-model="form.assigned_to" class="field-input">
                <option :value="null">Unassigned</option>
                <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="field-label">Tags</label>
              <div class="flex flex-wrap gap-2 p-2 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl min-h-[42px]">
                <button v-for="t in tags" :key="t.id" type="button" @click="toggleTag(t.id)"
                  class="px-2.5 py-1 rounded-full text-xs font-bold border transition"
                  :style="form.tag_ids.includes(t.id) ? { backgroundColor: t.color, borderColor: t.color, color: '#fff' } : { backgroundColor: '#fff', borderColor: t.color, color: t.color }">
                  {{ t.name }}
                </button>
                <span v-if="tags.length === 0" class="text-xs text-slate-400">No tags yet — create some under Tags</span>
              </div>
            </div>
            <div>
              <label class="field-label">Notes</label>
              <textarea v-model="form.notes" rows="2" class="field-input"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Saving...' : editingId ? 'Update' : 'Add Contact' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap gap-3">
            <input v-model="search" @input="debouncedSearch" type="text" placeholder="Search name, email, phone..." class="field-input !w-auto flex-1 min-w-[180px]" />
            <div class="flex flex-wrap gap-2">
              <button v-for="f in stageFilters" :key="f.value" @click="filterStage = f.value; fetchRecords()"
                class="px-3 py-1.5 rounded-lg text-xs font-bold border transition"
                :class="filterStage === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
                {{ f.label }}
              </button>
            </div>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 5" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="records.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No contacts found</p></div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Contact</th>
                  <th class="px-5 py-3.5">Company</th>
                  <th class="px-5 py-3.5">Source</th>
                  <th class="px-5 py-3.5">Tags</th>
                  <th class="px-5 py-3.5">Stage</th>
                  <th class="px-5 py-3.5">Owner</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="c in records" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors cursor-pointer" @click="$router.push(`/crm/contacts/${c.id}`)">
                  <td class="px-5 py-4">
                    <p class="font-bold text-sm text-slate-900">{{ c.name }}</p>
                    <p class="text-xs text-slate-400">{{ c.phone || c.email || '—' }}</p>
                  </td>
                  <td class="px-5 py-4 text-sm text-slate-600">{{ c.company?.name || '—' }}</td>
                  <td class="px-5 py-4 text-sm text-slate-600">{{ c.source?.name || '—' }}</td>
                  <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="t in c.tags" :key="t.id" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :style="{ backgroundColor: t.color + '1A', color: t.color }">{{ t.name }}</span>
                    </div>
                  </td>
                  <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="stageClass(c.lifecycle_stage)">{{ c.lifecycle_stage }}</span></td>
                  <td class="px-5 py-4 text-sm text-slate-600">{{ c.assignee?.name || '—' }}</td>
                  <td class="px-5 py-4 text-right" @click.stop>
                    <div class="flex justify-end gap-2">
                      <button v-if="!c.customer_id" @click="convert(c)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">Convert</button>
                      <span v-else class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-500">Customer</span>
                      <button @click="edit(c)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                      <button @click="remove(c.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
import CrmContactService from '@/services/CrmContactService'
import CrmCompanyService from '@/services/CrmCompanyService'
import CrmTagService from '@/services/CrmTagService'
import CrmSourceService from '@/services/CrmSourceService'
import StaffService from '@/services/StaffService'

const tableLoading = ref(true)
const loading = ref(false)
const editingId = ref(null)
const records = ref([])
const companies = ref([])
const tags = ref([])
const sources = ref([])
const staff = ref([])
const search = ref('')
const filterStage = ref('')

const defaultForm = () => ({ company_id: null, name: '', email: '', phone: '', source_id: null, lifecycle_stage: 'lead', assigned_to: null, notes: '', tag_ids: [] })
const form = reactive(defaultForm())

const stageFilters = [
  { label: 'All', value: '' },
  { label: 'Lead', value: 'lead' },
  { label: 'Qualified', value: 'qualified' },
  { label: 'Customer', value: 'customer' },
  { label: 'Lost', value: 'lost' },
]
const stageClass = (s) => ({
  lead: 'bg-amber-50 text-amber-700',
  qualified: 'bg-sky-50 text-sky-700',
  customer: 'bg-emerald-50 text-emerald-700',
  lost: 'bg-slate-100 text-slate-500',
}[s] || 'bg-slate-100 text-slate-600')

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })

const toggleTag = (id) => {
  const idx = form.tag_ids.indexOf(id)
  if (idx === -1) form.tag_ids.push(id)
  else form.tag_ids.splice(idx, 1)
}

const fetchRecords = async () => {
  tableLoading.value = true
  try {
    const params = {}
    if (search.value.trim()) params.search = search.value.trim()
    if (filterStage.value) params.lifecycle_stage = filterStage.value
    const res = await CrmContactService.getAll(params)
    records.value = res?.data ?? []
  } finally { tableLoading.value = false }
}

let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchRecords, 350)
}

onMounted(async () => {
  const [compRes, tagRes, sourceRes, staffRes] = await Promise.all([
    CrmCompanyService.getAll(),
    CrmTagService.getAll(),
    CrmSourceService.getAll({ active_only: 1 }),
    StaffService.getAllStaff(),
  ])
  companies.value = compRes?.data ?? []
  tags.value = tagRes?.data ?? []
  sources.value = sourceRes?.data ?? []
  staff.value = staffRes?.data?.data ?? staffRes?.data ?? []
  await fetchRecords()
})

const save = async () => {
  if (!form.name.trim()) { Swal.fire({ icon: 'warning', title: 'Name is required', confirmButtonColor: '#229DE7' }); return }
  loading.value = true
  try {
    if (editingId.value) {
      await CrmContactService.update(editingId.value, { ...form })
      toast('success', 'Contact updated')
    } else {
      await CrmContactService.create({ ...form })
      toast('success', 'Contact added')
    }
    resetForm()
    await fetchRecords()
  } catch (err) { errorAlert(err, 'Failed to save contact') }
  finally { loading.value = false }
}

const edit = (c) => {
  editingId.value = c.id
  Object.assign(form, {
    company_id: c.company_id, name: c.name, email: c.email || '', phone: c.phone || '',
    source_id: c.source_id, lifecycle_stage: c.lifecycle_stage, assigned_to: c.assigned_to,
    notes: c.notes || '', tag_ids: (c.tags || []).map(t => t.id),
  })
}

const convert = async (c) => {
  const r = await Swal.fire({ title: `Convert ${c.name} to a customer?`, text: 'This creates a real customer record usable in Sales and Quotations.', icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Convert' })
  if (!r.isConfirmed) return
  try { await CrmContactService.convertToCustomer(c.id); toast('success', 'Converted to customer'); await fetchRecords() }
  catch (err) { errorAlert(err, 'Failed to convert') }
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this contact?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await CrmContactService.remove(id); records.value = records.value.filter(c => c.id !== id); toast('success', 'Deleted') }
  catch (err) { errorAlert(err, 'Failed to delete') }
}

const resetForm = () => { editingId.value = null; Object.assign(form, defaultForm()) }
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; width: 100%; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
