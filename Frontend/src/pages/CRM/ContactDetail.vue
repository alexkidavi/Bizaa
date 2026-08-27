<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <router-link to="/crm/contacts" class="text-xs font-bold text-slate-400 hover:text-[#229DE7] transition">&larr; Back to Contacts</router-link>

    <div v-if="loading" class="mt-4 space-y-3"><div v-for="n in 4" :key="n" class="h-16 bg-white/60 border border-[#D9EBF7] rounded-2xl animate-pulse"></div></div>

    <template v-else-if="contact">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mt-2 mb-6">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ contact.name }}</h1>
          <p class="text-slate-500 mt-1 text-sm">{{ contact.company?.name || 'No company' }}{{ contact.source ? ' · ' + contact.source.name : '' }}</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1.5 rounded-full text-xs font-bold capitalize" :class="stageClass(contact.lifecycle_stage)">{{ contact.lifecycle_stage }}</span>
          <button v-if="!contact.customer_id" @click="convert" class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition-all whitespace-nowrap">Convert to Customer</button>
          <span v-else class="px-4 py-2.5 bg-slate-100 text-slate-500 text-xs font-bold rounded-xl whitespace-nowrap">Customer</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT: INFO + DEALS -->
        <div class="lg:col-span-5 space-y-6">
          <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-6">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Contact Info</p>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between"><dt class="text-slate-400">Phone</dt><dd class="font-semibold text-slate-700">{{ contact.phone || '—' }}</dd></div>
              <div class="flex justify-between"><dt class="text-slate-400">Email</dt><dd class="font-semibold text-slate-700">{{ contact.email || '—' }}</dd></div>
              <div class="flex justify-between"><dt class="text-slate-400">Owner</dt><dd class="font-semibold text-slate-700">{{ contact.assignee?.name || '—' }}</dd></div>
            </dl>
            <div v-if="contact.tags?.length" class="flex flex-wrap gap-1 mt-4">
              <span v-for="t in contact.tags" :key="t.id" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :style="{ backgroundColor: t.color + '1A', color: t.color }">{{ t.name }}</span>
            </div>
            <p v-if="contact.notes" class="text-xs text-slate-500 mt-4 pt-4 border-t border-[#EEF5FB]">{{ contact.notes }}</p>
          </div>

          <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between">
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Deals ({{ contact.deals?.length ?? 0 }})</p>
              <router-link to="/crm/deals" class="text-[11px] font-bold text-[#229DE7] hover:underline">View pipeline &rarr;</router-link>
            </div>
            <div v-if="!contact.deals?.length" class="py-10 text-center text-xs text-slate-400">No deals yet</div>
            <div v-else class="divide-y divide-[#EEF5FB]">
              <div v-for="d in contact.deals" :key="d.id" class="px-6 py-3">
                <p class="text-sm font-bold text-slate-800">{{ d.title }}</p>
                <div class="flex items-center justify-between mt-1">
                  <span class="text-xs text-slate-500">{{ d.pipeline_stage?.name }}</span>
                  <span class="text-xs font-bold text-[#229DE7]">{{ fmtMoney(d.value) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: TIMELINE -->
        <div class="lg:col-span-7">
          <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-6">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">Activity Timeline</p>
            <ActivityTimeline :contact-id="contact.id" />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Swal from 'sweetalert2'
import CrmContactService from '@/services/CrmContactService'
import ActivityTimeline from '@/components/CRM/ActivityTimeline.vue'

const route = useRoute()
const contactId = route.params.id

const loading = ref(true)
const contact = ref(null)

const fmtMoney = (v) => `KES ${Number(v ?? 0).toLocaleString('en-KE', { maximumFractionDigits: 0 })}`
const stageClass = (s) => ({
  lead: 'bg-amber-50 text-amber-700',
  qualified: 'bg-sky-50 text-sky-700',
  customer: 'bg-emerald-50 text-emerald-700',
  lost: 'bg-slate-100 text-slate-500',
}[s] || 'bg-slate-100 text-slate-600')

const load = async () => {
  loading.value = true
  try { const res = await CrmContactService.getById(contactId); contact.value = res.data }
  finally { loading.value = false }
}
onMounted(load)

const convert = async () => {
  const r = await Swal.fire({ title: `Convert ${contact.value.name} to a customer?`, text: 'This creates a real customer record usable in Sales and Quotations.', icon: 'question', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#94a3b8', confirmButtonText: 'Convert' })
  if (!r.isConfirmed) return
  try {
    await CrmContactService.convertToCustomer(contact.value.id)
    Swal.fire({ icon: 'success', title: 'Converted to customer', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
    await load()
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || 'Failed to convert', confirmButtonColor: '#229DE7' })
  }
}
</script>
