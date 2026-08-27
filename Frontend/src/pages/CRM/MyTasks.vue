<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">My Tasks</h1>
        <p class="text-slate-500 mt-1 text-sm">Follow-ups and activities assigned to you.</p>
      </div>
    </div>

    <div class="flex items-center gap-2 mb-6">
      <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; load()"
        class="px-3 py-1.5 rounded-lg text-xs font-bold border transition"
        :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
        {{ f.label }}
      </button>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div v-if="loading" class="p-6 space-y-3"><div v-for="n in 5" :key="n" class="h-14 bg-slate-100 animate-pulse rounded-xl"></div></div>
      <div v-else-if="tasks.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">Nothing here</p></div>
      <div v-else class="divide-y divide-[#EEF5FB]">
        <div v-for="a in tasks" :key="a.id" class="px-6 py-4 flex items-start justify-between gap-4 hover:bg-[#F5FAFF] transition-colors">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide bg-[#EAF5FD] text-[#1682C4]">{{ a.type }}</span>
              <span v-if="a.completed_at" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700">Done</span>
              <span v-else-if="isOverdue(a)" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-600">Overdue</span>
            </div>
            <p class="text-sm font-bold text-slate-800 mt-1">{{ a.subject }}</p>
            <p v-if="a.description" class="text-xs text-slate-500 mt-0.5">{{ a.description }}</p>
            <router-link :to="`/crm/contacts/${a.contact_id}`" class="text-xs text-[#229DE7] hover:underline mt-1 inline-block">{{ a.contact?.name }}</router-link>
            <span v-if="a.deal" class="text-xs text-slate-400"> · {{ a.deal.title }}</span>
            <p class="text-[11px] text-slate-400 mt-1">
              <span v-if="a.due_date">Due {{ fmtDate(a.due_date) }}</span>
            </p>
          </div>
          <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <button v-if="a.completed_at" @click="reopen(a)" class="text-[11px] font-bold text-slate-400 hover:text-[#229DE7]">Reopen</button>
            <button v-else @click="complete(a)" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">Mark Done</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import CrmActivityService from '@/services/CrmActivityService'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const tasks = ref([])
const filterStatus = ref('upcoming')

const statusFilters = [
  { label: 'Upcoming', value: 'upcoming' },
  { label: 'Overdue', value: 'overdue' },
  { label: 'Completed', value: 'completed' },
  { label: 'All', value: '' },
]

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : ''
const isOverdue = (a) => !a.completed_at && a.due_date && new Date(a.due_date) < new Date(new Date().toDateString())

const load = async () => {
  loading.value = true
  try {
    const res = await CrmActivityService.getAll({ assigned_to: auth.user?.id, status: filterStatus.value })
    tasks.value = res?.data ?? []
  } finally { loading.value = false }
}
onMounted(load)

const complete = async (a) => {
  try {
    await CrmActivityService.complete(a.id)
    if (filterStatus.value) tasks.value = tasks.value.filter(t => t.id !== a.id)
    else a.completed_at = new Date().toISOString()
  } catch { Swal.fire({ icon: 'error', title: 'Failed to update', confirmButtonColor: '#229DE7' }) }
}
const reopen = async (a) => {
  try {
    await CrmActivityService.reopen(a.id)
    if (filterStatus.value) tasks.value = tasks.value.filter(t => t.id !== a.id)
    else a.completed_at = null
  } catch { Swal.fire({ icon: 'error', title: 'Failed to update', confirmButtonColor: '#229DE7' }) }
}
</script>
