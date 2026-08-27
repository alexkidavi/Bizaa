<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">CRM Dashboard</h1>
        <p class="text-slate-500 mt-1 text-sm">Pipeline, conversion, activity and campaign performance at a glance.</p>
      </div>
      <div class="flex items-center gap-2 text-xs">
        <span class="text-slate-400 font-semibold">Leaderboard range:</span>
        <input v-model="from" type="date" class="field-input !w-auto !py-1.5" @change="load" />
        <span class="text-slate-400">to</span>
        <input v-model="to" type="date" class="field-input !w-auto !py-1.5" @change="load" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div v-for="n in 4" :key="n" class="h-24 bg-white border border-[#D9EBF7] rounded-2xl animate-pulse"></div>
    </div>

    <template v-else>
      <!-- OVERVIEW CARDS -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div v-for="card in overviewCards" :key="card.label" class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ card.label }}</p>
          <p class="text-2xl font-extrabold text-slate-900 mt-1.5">{{ card.value }}</p>
          <p v-if="card.sub" class="text-xs text-slate-400 mt-0.5">{{ card.sub }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- PIPELINE BY STAGE -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-6">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-4">Pipeline by Stage</h2>
          <div v-if="!pipeline.length" class="py-10 text-center text-slate-400 text-sm">No pipeline stages configured</div>
          <div v-else class="h-64"><Bar :data="pipelineChartData" :options="pipelineChartOptions" /></div>
        </div>

        <!-- CONVERSION FUNNEL -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-6">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-4">Conversion Funnel</h2>
          <div class="space-y-3">
            <div v-for="(f, i) in funnel" :key="f.stage">
              <div class="flex items-center justify-between text-xs font-bold mb-1">
                <span class="capitalize text-slate-600">{{ f.stage }}</span>
                <span class="text-slate-900">{{ f.count }}</span>
              </div>
              <div class="h-3 bg-[#F0F6FB] rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" :class="funnelColor(f.stage)" :style="{ width: funnelWidth(f.count) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- ACTIVITY LEADERBOARD -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Activity Leaderboard</h2></div>
          <div v-if="!leaderboard.length" class="py-10 text-center text-slate-400 text-sm">No activity in this range</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3">Staff</th>
                  <th class="px-5 py-3 text-center">Completed</th>
                  <th class="px-5 py-3 text-center">Open</th>
                  <th class="px-5 py-3 text-center">Overdue</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="row in leaderboard" :key="row.user_id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-3.5 font-bold text-sm text-slate-900">{{ row.user_name }}</td>
                  <td class="px-5 py-3.5 text-center font-bold text-emerald-600">{{ row.completed_count }}</td>
                  <td class="px-5 py-3.5 text-center text-slate-500">{{ row.open_count }}</td>
                  <td class="px-5 py-3.5 text-center" :class="row.overdue_count > 0 ? 'font-bold text-red-500' : 'text-slate-400'">{{ row.overdue_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- CAMPAIGN PERFORMANCE -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Recent Campaigns</h2></div>
          <div v-if="!campaigns.length" class="py-10 text-center text-slate-400 text-sm">No campaigns sent yet</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3">Campaign</th>
                  <th class="px-5 py-3 text-center">Sent / Total</th>
                  <th class="px-5 py-3 text-right">Delivery</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="c in campaigns" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-3.5">
                    <p class="font-bold text-sm text-slate-900">{{ c.name }}</p>
                    <p class="text-[10px] uppercase font-bold text-slate-400">{{ c.channel }} · {{ c.status }}</p>
                  </td>
                  <td class="px-5 py-3.5 text-center font-bold text-slate-700">{{ c.sent_count }} / {{ c.recipients_count }}</td>
                  <td class="px-5 py-3.5 text-right">
                    <span v-if="c.delivery_rate !== null" class="px-2 py-0.5 rounded-full text-xs font-bold" :class="c.delivery_rate >= 80 ? 'bg-emerald-50 text-emerald-700' : c.delivery_rate >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600'">{{ c.delivery_rate }}%</span>
                    <span v-else class="text-slate-300 text-xs">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LinearScale, CategoryScale } from 'chart.js'
import { Bar } from 'vue-chartjs'
import CrmDashboardService from '@/services/CrmDashboardService'

ChartJS.register(Title, Tooltip, Legend, BarElement, LinearScale, CategoryScale)

const loading = ref(true)
const overview = ref({})
const pipeline = ref([])
const funnel = ref([])
const leaderboard = ref([])
const campaigns = ref([])

// Built from local Y/M/D parts rather than toISOString() — the latter
// converts through UTC, which shifts a Nairobi midnight back a day and
// silently mis-sets the month boundary (see math-meteor-style bug class).
const pad = (n) => String(n).padStart(2, '0')
const ymd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
const today = new Date()
const from = ref(ymd(new Date(today.getFullYear(), today.getMonth(), 1)))
const to = ref(ymd(new Date(today.getFullYear(), today.getMonth() + 1, 0)))

const money = (v) => 'KES ' + Number(v ?? 0).toLocaleString(undefined, { maximumFractionDigits: 0 })

const overviewCards = computed(() => [
  { label: 'Total Contacts', value: overview.value.total_contacts ?? 0 },
  { label: 'Open Deals', value: overview.value.open_deals_count ?? 0, sub: money(overview.value.open_deals_value) },
  { label: 'Won This Month', value: overview.value.won_this_month_count ?? 0, sub: money(overview.value.won_this_month_value) },
  { label: 'Overdue Activities', value: overview.value.overdue_activities ?? 0, sub: (overview.value.due_today_activities ?? 0) + ' due today' },
])

const pipelineChartData = computed(() => ({
  labels: pipeline.value.map(s => s.name),
  datasets: [{
    label: 'Open Deal Value',
    data: pipeline.value.map(s => s.deal_value),
    backgroundColor: pipeline.value.map(s => s.is_won ? '#10B981' : s.is_lost ? '#EF4444' : '#229DE7'),
    borderRadius: 6,
    maxBarThickness: 36,
  }],
}))

const pipelineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1e293b',
      callbacks: {
        label: (ctx) => ` KES ${Number(ctx.raw).toLocaleString()}`,
        afterLabel: (ctx) => `${pipeline.value[ctx.dataIndex]?.deal_count ?? 0} deal(s)`,
      },
    },
  },
  scales: {
    x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' } },
    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: (v) => 'KES ' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) } },
  },
}

const funnelColors = { lead: 'bg-[#229DE7]', qualified: 'bg-amber-400', customer: 'bg-emerald-500', lost: 'bg-red-400' }
const funnelColor = (stage) => funnelColors[stage] || 'bg-slate-400'
const funnelWidth = (count) => {
  const max = Math.max(...funnel.value.map(f => f.count), 1)
  return Math.max((count / max) * 100, count > 0 ? 4 : 0)
}

const load = async () => {
  loading.value = true
  try {
    const data = await CrmDashboardService.get({ from: from.value, to: to.value })
    overview.value = data.overview
    pipeline.value = data.pipeline_by_stage
    funnel.value = data.conversion_funnel
    leaderboard.value = data.activity_leaderboard
    campaigns.value = data.campaign_performance
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.field-input { padding: 6px 10px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 10px; font-size: 12.5px; color: #1e293b; outline: none; }
</style>
