<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Pipeline Stages</h1>
        <p class="text-slate-500 mt-1 text-sm">Configure the columns of your deal pipeline.</p>
      </div>
      <router-link to="/crm/deals" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all whitespace-nowrap">&larr; Back to Deals</router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? 'Edit Stage' : 'Add Stage' }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="form.name" type="text" class="field-input" placeholder="e.g. Proposal Sent" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Order</label>
                <input v-model.number="form.display_order" type="number" min="0" class="field-input" />
              </div>
              <div>
                <label class="field-label">Default Probability %</label>
                <input v-model.number="form.default_probability" type="number" min="0" max="100" class="field-input" />
              </div>
            </div>
            <div class="space-y-2">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input v-model="form.is_won" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" @change="form.is_won && (form.is_lost = false)" />
                <span class="text-xs font-bold text-slate-600">Deals here count as Won</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input v-model="form.is_lost" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" @change="form.is_lost && (form.is_won = false)" />
                <span class="text-xs font-bold text-slate-600">Deals here count as Lost</span>
              </label>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Saving...' : editingId ? 'Update' : 'Add Stage' }}
            </button>
          </form>
        </div>
      </aside>

      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ stages.length }} stage(s)</span></div>
          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="stages.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No stages yet</p></div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">#</th>
                  <th class="px-5 py-3.5">Stage</th>
                  <th class="px-5 py-3.5 text-center">Probability</th>
                  <th class="px-5 py-3.5">Flags</th>
                  <th class="px-5 py-3.5 text-center">Deals</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="s in stages" :key="s.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ s.display_order }}</td>
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ s.name }}</td>
                  <td class="px-5 py-4 text-center text-sm">{{ s.default_probability }}%</td>
                  <td class="px-5 py-4">
                    <span v-if="s.is_won" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Won</span>
                    <span v-else-if="s.is_lost" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600">Lost</span>
                    <span v-else class="text-xs text-slate-400">—</span>
                  </td>
                  <td class="px-5 py-4 text-center text-sm font-bold text-slate-700">{{ s.deals_count ?? 0 }}</td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="edit(s)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                      <button @click="remove(s.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
import CrmPipelineStageService from '@/services/CrmPipelineStageService'

const tableLoading = ref(true)
const loading = ref(false)
const editingId = ref(null)
const stages = ref([])

const defaultForm = () => ({ name: '', display_order: 0, default_probability: 0, is_won: false, is_lost: false })
const form = reactive(defaultForm())

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })

const load = async () => {
  tableLoading.value = true
  try { const res = await CrmPipelineStageService.getAll(); stages.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}
onMounted(load)

const save = async () => {
  if (!form.name.trim()) { Swal.fire({ icon: 'warning', title: 'Name is required', confirmButtonColor: '#229DE7' }); return }
  loading.value = true
  try {
    if (editingId.value) {
      const res = await CrmPipelineStageService.update(editingId.value, { ...form })
      const idx = stages.value.findIndex(s => s.id === editingId.value)
      if (idx !== -1) stages.value[idx] = { ...stages.value[idx], ...res.data }
      toast('success', 'Stage updated')
    } else {
      const res = await CrmPipelineStageService.create({ ...form })
      stages.value.push({ ...res.data, deals_count: 0 })
      toast('success', 'Stage added')
    }
    resetForm()
    stages.value.sort((a, b) => a.display_order - b.display_order)
  } catch (err) { errorAlert(err, 'Failed to save stage') }
  finally { loading.value = false }
}

const edit = (s) => { editingId.value = s.id; Object.assign(form, { name: s.name, display_order: s.display_order, default_probability: Number(s.default_probability), is_won: !!s.is_won, is_lost: !!s.is_lost }) }

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this stage?', text: 'Deals in this stage are not deleted, but will need reassigning.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await CrmPipelineStageService.remove(id); stages.value = stages.value.filter(s => s.id !== id); toast('success', 'Deleted') }
  catch (err) { errorAlert(err, 'Failed to delete') }
}

const resetForm = () => { editingId.value = null; Object.assign(form, defaultForm()) }
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
