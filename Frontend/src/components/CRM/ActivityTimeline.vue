<template>
  <div>
    <!-- LOG NEW ACTIVITY -->
    <form @submit.prevent="save" class="p-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl space-y-2 mb-4">
      <div class="grid grid-cols-3 gap-2">
        <select v-model="form.type" class="field-input !text-xs">
          <option value="note">Note</option>
          <option value="call">Call</option>
          <option value="meeting">Meeting</option>
          <option value="email">Email</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="sms">SMS</option>
          <option value="task">Task</option>
        </select>
        <input v-model="form.subject" type="text" placeholder="What happened / what's next?" class="field-input !text-xs col-span-2" />
      </div>
      <div class="grid grid-cols-2 gap-2">
        <input v-model="form.due_date" type="date" class="field-input !text-xs" placeholder="Due date (optional)" />
        <select v-model="form.assigned_to" class="field-input !text-xs">
          <option :value="null">Unassigned</option>
          <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <textarea v-model="form.description" rows="2" placeholder="Notes (optional)" class="field-input !text-xs"></textarea>
      <button type="submit" :disabled="saving" class="w-full py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-lg transition disabled:opacity-60">
        {{ saving ? 'Logging...' : 'Log Activity' }}
      </button>
    </form>

    <!-- TIMELINE -->
    <div v-if="loading" class="space-y-2"><div v-for="n in 3" :key="n" class="h-14 bg-slate-100 animate-pulse rounded-xl"></div></div>
    <div v-else-if="activities.length === 0" class="text-center py-8 text-xs text-slate-400">No activity yet</div>
    <div v-else class="space-y-2">
      <div v-for="a in activities" :key="a.id" class="p-3 rounded-xl border" :class="a.completed_at ? 'bg-slate-50 border-slate-100' : isOverdue(a) ? 'bg-red-50 border-red-100' : 'bg-white border-[#EEF5FB]'">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide bg-[#EAF5FD] text-[#1682C4]">{{ a.type }}</span>
              <span v-if="a.completed_at" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700">Done</span>
              <span v-else-if="isOverdue(a)" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-600">Overdue</span>
            </div>
            <p class="text-sm font-bold text-slate-800 mt-1">{{ a.subject }}</p>
            <p v-if="a.description" class="text-xs text-slate-500 mt-0.5">{{ a.description }}</p>
            <p class="text-[11px] text-slate-400 mt-1">
              <span v-if="a.due_date">Due {{ fmtDate(a.due_date) }} · </span>
              <span v-if="a.assignee">{{ a.assignee.name }} · </span>
              {{ fmtDate(a.created_at) }}
            </p>
          </div>
          <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <button v-if="a.completed_at" @click="reopen(a)" class="text-[10px] font-bold text-slate-400 hover:text-[#229DE7]">Reopen</button>
            <button v-else @click="complete(a)" class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700">Mark Done</button>
            <button @click="remove(a)" class="text-[10px] font-bold text-red-400 hover:text-red-500">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import Swal from 'sweetalert2'
import CrmActivityService from '@/services/CrmActivityService'
import StaffService from '@/services/StaffService'

const props = defineProps({
  contactId: { type: [Number, String], default: null },
  dealId: { type: [Number, String], default: null },
})

const loading = ref(true)
const saving = ref(false)
const activities = ref([])
const staff = ref([])

const defaultForm = () => ({ type: 'note', subject: '', description: '', due_date: '', assigned_to: null })
const form = reactive(defaultForm())

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : ''
const isOverdue = (a) => !a.completed_at && a.due_date && new Date(a.due_date) < new Date(new Date().toDateString())

const load = async () => {
  if (!props.contactId && !props.dealId) { activities.value = []; loading.value = false; return }
  loading.value = true
  try {
    const params = props.dealId ? { deal_id: props.dealId } : { contact_id: props.contactId }
    const res = await CrmActivityService.getAll(params)
    activities.value = res?.data ?? []
  } finally { loading.value = false }
}

onMounted(async () => {
  const res = await StaffService.getAllStaff()
  staff.value = res?.data?.data ?? res?.data ?? []
  await load()
})

watch(() => [props.contactId, props.dealId], load)

const save = async () => {
  if (!form.subject.trim()) { Swal.fire({ icon: 'warning', title: 'Subject is required', confirmButtonColor: '#229DE7' }); return }
  saving.value = true
  try {
    const payload = { ...form, contact_id: props.contactId, deal_id: props.dealId }
    const res = await CrmActivityService.create(payload)
    activities.value.unshift(res.data)
    Object.assign(form, defaultForm())
    toast('success', 'Logged')
  } catch (err) { errorAlert(err, 'Failed to log activity') }
  finally { saving.value = false }
}

const complete = async (a) => {
  try { const res = await CrmActivityService.complete(a.id); Object.assign(a, res.data) }
  catch (err) { errorAlert(err, 'Failed to update') }
}
const reopen = async (a) => {
  try { const res = await CrmActivityService.reopen(a.id); Object.assign(a, res.data) }
  catch (err) { errorAlert(err, 'Failed to update') }
}
const remove = async (a) => {
  const r = await Swal.fire({ title: 'Delete this activity?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await CrmActivityService.remove(a.id); activities.value = activities.value.filter(x => x.id !== a.id); toast('success', 'Deleted') }
  catch (err) { errorAlert(err, 'Failed to delete') }
}
</script>

<style scoped>
.field-input { width: 100%; padding: 8px 10px; background: #fff; border: 1.5px solid #d9ebf7; border-radius: 10px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); }
</style>
