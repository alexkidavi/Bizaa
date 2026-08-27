<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Leave Types</h1>
        <p class="text-slate-500 mt-1 text-sm">Define leave categories, accrual rules and eligibility.</p>
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
          <form @submit.prevent="save" class="p-6 space-y-4 max-h-[calc(100vh-160px)] overflow-y-auto">
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

            <div class="pt-2 border-t border-[#EEF5FB]">
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Accrual & Carry-Forward</p>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="field-label">Accrual Rule</label>
                  <select v-model="form.accrual_rule" class="field-input">
                    <option value="none">None (full grant)</option>
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                  </select>
                </div>
                <div v-if="form.accrual_rule !== 'none'">
                  <label class="field-label">Accrual Rate (days)</label>
                  <input v-model="form.accrual_rate" type="number" step="0.01" min="0" class="field-input" placeholder="e.g. 1.75" />
                </div>
              </div>
              <label class="flex items-center gap-2 mt-3 cursor-pointer select-none">
                <input v-model="form.carry_forward_allowed" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7] focus:ring-[#229DE7]" />
                <span class="text-xs font-bold text-slate-600">Allow carry-forward to next year</span>
              </label>
              <div v-if="form.carry_forward_allowed" class="mt-2">
                <label class="field-label">Max Carry-Forward Days</label>
                <input v-model="form.max_carry_forward_days" type="number" min="0" class="field-input" placeholder="e.g. 5" />
              </div>
            </div>

            <div class="pt-2 border-t border-[#EEF5FB]">
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Eligibility & Rules</p>
              <label class="field-label">Gender Eligibility</label>
              <select v-model="form.gender_eligibility" class="field-input mb-3">
                <option :value="null">Any gender</option>
                <option value="male">Male only</option>
                <option value="female">Female only</option>
              </select>
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input v-model="form.is_paid" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7] focus:ring-[#229DE7]" />
                  <span class="text-xs font-bold text-slate-600">Paid leave (tracked against balance)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input v-model="form.requires_approval" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7] focus:ring-[#229DE7]" />
                  <span class="text-xs font-bold text-slate-600">Requires approval</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input v-model="form.requires_documentation" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7] focus:ring-[#229DE7]" />
                  <span class="text-xs font-bold text-slate-600">Requires supporting documents</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7] focus:ring-[#229DE7]" />
                  <span class="text-xs font-bold text-slate-600">Active (selectable on new requests)</span>
                </label>
              </div>
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
                  <th class="px-5 py-3.5">Rules</th>
                  <th class="px-5 py-3.5 text-center">Used</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(lt, i) in leaveTypes" :key="lt.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
                  <td class="px-5 py-4">
                    <p class="font-bold text-sm text-slate-900">{{ lt.name }}</p>
                    <p v-if="lt.description" class="text-xs text-slate-400">{{ lt.description }}</p>
                    <span v-if="!lt.is_active" class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                  </td>
                  <td class="px-5 py-4 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]">{{ lt.days_allowed }}</span>
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="lt.is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{ lt.is_paid ? 'Paid' : 'Unpaid' }}</span>
                      <span v-if="lt.accrual_rule !== 'none'" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 capitalize">{{ lt.accrual_rule }} accrual</span>
                      <span v-if="lt.carry_forward_allowed" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700">Carries fwd</span>
                      <span v-if="lt.gender_eligibility" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 capitalize">{{ lt.gender_eligibility }} only</span>
                      <span v-if="lt.requires_documentation" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Docs req.</span>
                    </div>
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

const defaultForm = () => ({
  name: '', days_allowed: '', description: '',
  accrual_rule: 'none', accrual_rate: '', carry_forward_allowed: false, max_carry_forward_days: '',
  gender_eligibility: null, requires_documentation: false, requires_approval: true, is_paid: true, is_active: true,
})
const form = reactive(defaultForm())
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
    const payload = {
      name: form.name.trim(),
      days_allowed: Number(form.days_allowed),
      description: form.description || null,
      accrual_rule: form.accrual_rule,
      accrual_rate: form.accrual_rule !== 'none' && form.accrual_rate !== '' ? Number(form.accrual_rate) : null,
      carry_forward_allowed: form.carry_forward_allowed,
      max_carry_forward_days: form.carry_forward_allowed && form.max_carry_forward_days !== '' ? Number(form.max_carry_forward_days) : null,
      gender_eligibility: form.gender_eligibility || null,
      requires_documentation: form.requires_documentation,
      requires_approval: form.requires_approval,
      is_paid: form.is_paid,
      is_active: form.is_active,
    }
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

const edit = (lt) => {
  editingId.value = lt.id
  form.name = lt.name
  form.days_allowed = lt.days_allowed
  form.description = lt.description || ''
  form.accrual_rule = lt.accrual_rule || 'none'
  form.accrual_rate = lt.accrual_rate ?? ''
  form.carry_forward_allowed = !!lt.carry_forward_allowed
  form.max_carry_forward_days = lt.max_carry_forward_days ?? ''
  form.gender_eligibility = lt.gender_eligibility || null
  form.requires_documentation = !!lt.requires_documentation
  form.requires_approval = lt.requires_approval !== false
  form.is_paid = lt.is_paid !== false
  form.is_active = lt.is_active !== false
}

const remove = async (id) => {
  const r = await Swal.fire({ title: 'Delete leave type?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await LeaveTypeService.remove(id); leaveTypes.value = leaveTypes.value.filter(l => l.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete Failed', confirmButtonColor: '#229DE7' }) }
}
const resetForm = () => {
  editingId.value = null
  Object.assign(form, defaultForm())
  errors.name = false; errors.days = false
}
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
