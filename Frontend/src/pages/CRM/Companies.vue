<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Companies</h1>
        <p class="text-slate-500 mt-1 text-sm">Organizations your contacts belong to.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? 'Edit Company' : 'Add Company' }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="form.name" type="text" class="field-input" placeholder="e.g. Acme Traders Ltd" />
            </div>
            <div>
              <label class="field-label">Industry</label>
              <input v-model="form.industry" type="text" class="field-input" placeholder="e.g. Retail" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Phone</label>
                <input v-model="form.phone" type="text" class="field-input" />
              </div>
              <div>
                <label class="field-label">Email</label>
                <input v-model="form.email" type="email" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Website</label>
              <input v-model="form.website" type="text" class="field-input" placeholder="https://" />
            </div>
            <div>
              <label class="field-label">Address</label>
              <textarea v-model="form.address" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Notes</label>
              <textarea v-model="form.notes" rows="2" class="field-input"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? 'Saving...' : editingId ? 'Update' : 'Add Company' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ companies.length }} compan{{ companies.length === 1 ? 'y' : 'ies' }}</span></div>
          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="companies.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No companies yet</p></div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Company</th>
                  <th class="px-5 py-3.5">Industry</th>
                  <th class="px-5 py-3.5">Contact Info</th>
                  <th class="px-5 py-3.5 text-center">Contacts</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="c in companies" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 font-bold text-sm text-slate-900">{{ c.name }}</td>
                  <td class="px-5 py-4 text-sm text-slate-600">{{ c.industry || '—' }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500">
                    <p v-if="c.phone">{{ c.phone }}</p>
                    <p v-if="c.email">{{ c.email }}</p>
                    <p v-if="!c.phone && !c.email">—</p>
                  </td>
                  <td class="px-5 py-4 text-center"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ c.contacts_count ?? 0 }}</span></td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
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
import CrmCompanyService from '@/services/CrmCompanyService'

const tableLoading = ref(true)
const loading = ref(false)
const editingId = ref(null)
const companies = ref([])

const defaultForm = () => ({ name: '', industry: '', phone: '', email: '', website: '', address: '', notes: '' })
const form = reactive(defaultForm())

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })

const load = async () => {
  tableLoading.value = true
  try { const res = await CrmCompanyService.getAll(); companies.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}
onMounted(load)

const save = async () => {
  if (!form.name.trim()) { Swal.fire({ icon: 'warning', title: 'Name is required', confirmButtonColor: '#229DE7' }); return }
  loading.value = true
  try {
    if (editingId.value) {
      const res = await CrmCompanyService.update(editingId.value, { ...form })
      const idx = companies.value.findIndex(c => c.id === editingId.value)
      if (idx !== -1) companies.value[idx] = { ...companies.value[idx], ...res.data }
      toast('success', 'Company updated')
    } else {
      const res = await CrmCompanyService.create({ ...form })
      companies.value.unshift({ ...res.data, contacts_count: 0 })
      toast('success', 'Company added')
    }
    resetForm()
  } catch (err) { errorAlert(err, 'Failed to save company') }
  finally { loading.value = false }
}

const edit = (c) => {
  editingId.value = c.id
  Object.assign(form, { name: c.name, industry: c.industry || '', phone: c.phone || '', email: c.email || '', website: c.website || '', address: c.address || '', notes: c.notes || '' })
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete this company?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await CrmCompanyService.remove(id); companies.value = companies.value.filter(c => c.id !== id); toast('success', 'Deleted') }
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
