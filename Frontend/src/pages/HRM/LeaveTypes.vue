<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Leave Types</h1>
        <p class="text-slate-500 mt-1 text-sm">Define leave categories and allowances.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? 'Edit Leave Type' : 'Add Leave Type' }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="form.name" type="text" class="field-input" :class="{ 'border-red-400': errors.name }" placeholder="e.g. Annual Leave" />
              <p v-if="errors.name" class="field-error">Name is required</p>
            </div>
            <div>
              <label class="field-label required">Days Allowed per Year</label>
              <input v-model="form.days_allowed" type="number" min="0" class="field-input" :class="{ 'border-red-400': errors.days }" placeholder="e.g. 21" />
              <p v-if="errors.days" class="field-error">Days allowed is required</p>
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="form.description" rows="2" class="field-input" placeholder="Optional description"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ loading ? 'Saving...' : editingId ? 'Update' : 'Add Leave Type' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <span class="font-bold text-sm text-slate-700">{{ leaveTypes.length }} leave types</span>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>

          <div v-else-if="leaveTypes.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No leave types defined</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5 w-10">#</th>
                  <th class="px-5 py-3.5">Leave Type</th>
                  <th class="px-5 py-3.5 text-center">Days/Year</th>
                  <th class="px-5 py-3.5 text-center">Total Used</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(lt, i) in leaveTypes" :key="lt.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4">
                    <p class="font-bold text-sm text-slate-900">{{ lt.name }}</p>
                    <p v-if="lt.description" class="text-xs text-slate-400">{{ lt.description }}</p>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ lt.days_allowed }}</span>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">{{ lt.leaves_count ?? 0 }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="edit(lt)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                      </button>
                      <button @click="remove(lt.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition">
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
import LeaveTypeService from '@/services/LeaveTypeService'

const tableLoading = ref(true)
const loading = ref(false)
const editingId = ref(null)
const leaveTypes = ref([])
const form = reactive({ name: '', days_allowed: '', description: '' })
const errors = reactive({ name: false, days: false })

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const load = async () => {
  tableLoading.value = true
  try { const res = await LeaveTypeService.getAll(); leaveTypes.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}
onMounted(load)

const save = async () => {
  errors.name = !form.name.trim()
  errors.days = form.days_allowed === '' || form.days_allowed === null
  if (errors.name || errors.days) return
  loading.value = true
  try {
    const payload = { name: form.name.trim(), days_allowed: Number(form.days_allowed), description: form.description }
    if (editingId.value) {
      const res = await LeaveTypeService.update(editingId.value, payload)
      const idx = leaveTypes.value.findIndex(l => l.id === editingId.value)
      if (idx !== -1) leaveTypes.value[idx] = res.data
      toast('success', 'Leave Type Updated')
    } else {
      const res = await LeaveTypeService.create(payload)
      leaveTypes.value.unshift(res.data)
      toast('success', 'Leave Type Added')
    }
    resetForm()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const edit = (lt) => { editingId.value = lt.id; form.name = lt.name; form.days_allowed = lt.days_allowed; form.description = lt.description || '' }
const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete leave type?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await LeaveTypeService.remove(id); leaveTypes.value = leaveTypes.value.filter(l => l.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
const resetForm = () => { editingId.value = null; form.name = ''; form.days_allowed = ''; form.description = ''; errors.name = false; errors.days = false }
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
