<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Work Shifts</h1>
        <p class="text-slate-500 mt-1 text-sm">Define work shifts and assign them to staff.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? 'Edit Shift' : 'Add Shift' }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Shift Name</label>
              <input v-model="form.name" type="text" class="field-input" :class="{ 'border-red-400': errors.name }" placeholder="e.g. Morning Shift" />
              <p v-if="errors.name" class="field-error">Name is required</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Start Time</label>
                <input v-model="form.start_time" type="time" class="field-input" :class="{ 'border-red-400': errors.start_time }" />
                <p v-if="errors.start_time" class="field-error">Required</p>
              </div>
              <div>
                <label class="field-label required">End Time</label>
                <input v-model="form.end_time" type="time" class="field-input" :class="{ 'border-red-400': errors.end_time }" />
                <p v-if="errors.end_time" class="field-error">Required</p>
              </div>
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="form.description" rows="2" class="field-input" placeholder="Optional notes"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ loading ? 'Saving...' : editingId ? 'Update Shift' : 'Add Shift' }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between">
            <span class="font-bold text-sm text-slate-700">{{ shifts.length }} shifts</span>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3">
            <div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div>
          </div>

          <div v-else-if="shifts.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No shifts defined</p>
            <p class="text-sm text-slate-400 mt-1">Add your first work shift.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5 w-10">#</th>
                  <th class="px-5 py-3.5">Shift</th>
                  <th class="px-5 py-3.5">Hours</th>
                  <th class="px-5 py-3.5 text-center">Staff</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(shift, i) in shifts" :key="shift.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4">
                    <p class="font-bold text-sm text-slate-900">{{ shift.name }}</p>
                    <p v-if="shift.description" class="text-xs text-slate-400">{{ shift.description }}</p>
                  </td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                      {{ shift.start_time }} – {{ shift.end_time }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ shift.staff_count ?? 0 }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="edit(shift)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                      </button>
                      <button @click="remove(shift.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition">
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
import ShiftService from '@/services/ShiftService'

const tableLoading = ref(true)
const loading = ref(false)
const editingId = ref(null)
const shifts = ref([])
const form = reactive({ name: '', start_time: '08:00', end_time: '17:00', description: '' })
const errors = reactive({ name: false, start_time: false, end_time: false })

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })

const load = async () => {
  tableLoading.value = true
  try { const res = await ShiftService.getAll(); shifts.value = res?.data ?? [] }
  finally { tableLoading.value = false }
}
onMounted(load)

const save = async () => {
  errors.name = !form.name.trim()
  errors.start_time = !form.start_time
  errors.end_time = !form.end_time
  if (errors.name || errors.start_time || errors.end_time) return
  loading.value = true
  try {
    const payload = { name: form.name.trim(), start_time: form.start_time, end_time: form.end_time, description: form.description }
    if (editingId.value) {
      const res = await ShiftService.update(editingId.value, payload)
      const idx = shifts.value.findIndex(s => s.id === editingId.value)
      if (idx !== -1) shifts.value[idx] = res.data
      toast('success', 'Shift Updated')
    } else {
      const res = await ShiftService.create(payload)
      shifts.value.unshift(res.data)
      toast('success', 'Shift Added')
    }
    resetForm()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Something went wrong', confirmButtonColor: '#229DE7' })
  } finally { loading.value = false }
}

const edit = (s) => { editingId.value = s.id; form.name = s.name; form.start_time = s.start_time; form.end_time = s.end_time; form.description = s.description || '' }
const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete shift?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await ShiftService.remove(id); shifts.value = shifts.value.filter(s => s.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
const resetForm = () => { editingId.value = null; form.name = ''; form.start_time = '08:00'; form.end_time = '17:00'; form.description = ''; Object.keys(errors).forEach(k => errors[k] = false) }
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
