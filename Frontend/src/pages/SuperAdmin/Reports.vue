<template>
  <div class="sa-page">
    <div v-if="loading" class="sa-loading-full">Loading reports…</div>

    <template v-else>

      <!-- Revenue section -->
      <div class="sa-section-grid">

        <!-- Monthly revenue chart -->
        <div class="sa-card sa-card--wide">
          <h2 class="sa-card__title">Monthly Revenue (Last 12 Months)</h2>
          <div class="sa-bar-chart" v-if="data.monthly_revenue?.length">
            <div v-for="bar in data.monthly_revenue" :key="bar.month" class="sa-bar-chart__col">
              <div class="sa-bar-chart__bar-wrap">
                <div
                  class="sa-bar-chart__bar sa-bar-chart__bar--green"
                  :style="{ height: revenueBarH(bar.revenue) + '%' }"
                  :title="`KES ${bar.revenue.toLocaleString()}`"
                ></div>
              </div>
              <p class="sa-bar-chart__label">{{ bar.month.split(' ')[0] }}</p>
              <p class="sa-bar-chart__val">{{ bar.revenue > 0 ? shortNum(bar.revenue) : '0' }}</p>
            </div>
          </div>
          <p v-else class="sa-empty">No data</p>
        </div>

        <!-- Revenue by plan -->
        <div class="sa-card">
          <h2 class="sa-card__title">Revenue by Plan</h2>
          <div class="sa-plan-list">
            <div v-for="p in data.revenue_by_plan" :key="p.plan" class="sa-plan-row">
              <div class="sa-plan-row__label">
                <span>{{ p.plan }}</span>
                <span class="sa-plan-row__count">{{ p.count }} subs</span>
              </div>
              <div class="sa-plan-row__bar-wrap">
                <div class="sa-plan-row__bar" :style="{ width: planBarW(p.revenue) + '%' }"></div>
              </div>
              <span class="sa-plan-row__rev">KES {{ Number(p.revenue).toLocaleString(undefined, { maximumFractionDigits: 0 }) }}</span>
            </div>
          </div>
          <p v-if="!data.revenue_by_plan?.length" class="sa-empty">No data</p>
        </div>

        <!-- Subscription breakdown -->
        <div class="sa-card">
          <h2 class="sa-card__title">Subscription Status</h2>
          <div class="sa-status-list">
            <div v-for="(count, status) in data.subscription_breakdown" :key="status" class="sa-status-row">
              <span class="sa-badge" :class="statusClass(status)">{{ status }}</span>
              <div class="sa-status-row__bar-wrap">
                <div class="sa-status-row__bar" :class="'sa-bar--' + status" :style="{ width: subBarW(count) + '%' }"></div>
              </div>
              <span class="sa-status-row__count">{{ count }}</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Industries + Top stores -->
      <div class="sa-two-col">

        <!-- By industry -->
        <div class="sa-card">
          <h2 class="sa-card__title">Businesses by Industry</h2>
          <div class="sa-table-wrap">
            <table class="sa-table">
              <thead><tr><th>Industry</th><th>Count</th></tr></thead>
              <tbody>
                <tr v-for="r in data.by_industry" :key="r.industry">
                  <td>{{ r.industry }}</td>
                  <td><strong style="color:#f1f5f9">{{ r.count }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="!data.by_industry?.length" class="sa-empty">No data</p>
        </div>

        <!-- Top stores -->
        <div class="sa-card">
          <h2 class="sa-card__title">Top Stores by Listed Products</h2>
          <div class="sa-table-wrap">
            <table class="sa-table">
              <thead><tr><th>#</th><th>Store</th><th>Business</th><th>Category</th><th>Products</th></tr></thead>
              <tbody>
                <tr v-for="(s, i) in data.top_stores" :key="s.store">
                  <td style="color:#64748b">{{ i + 1 }}</td>
                  <td>{{ s.store }}</td>
                  <td style="color:#64748b;font-size:12px">{{ s.business }}</td>
                  <td>{{ s.category }}</td>
                  <td><strong style="color:#60a5fa">{{ s.listed_products }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="!data.top_stores?.length" class="sa-empty">No stores yet</p>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SuperAdminService from "@/services/SuperAdminService";

const loading = ref(true);
const data    = ref({});

const maxRevenue = () => Math.max(...(data.value.monthly_revenue?.map(r => r.revenue) ?? [1]), 1);
const maxPlan    = () => Math.max(...(data.value.revenue_by_plan?.map(r => r.revenue) ?? [1]), 1);
const maxSub     = () => Math.max(...Object.values(data.value.subscription_breakdown ?? { x: 1 }), 1);

const revenueBarH = (v) => Math.max(Math.round((v / maxRevenue()) * 100), 2);
const planBarW    = (v) => Math.max(Math.round((v / maxPlan()) * 100), 2);
const subBarW     = (v) => Math.max(Math.round((v / maxSub()) * 100), 2);

const shortNum = (n) => n >= 1000 ? `${(n / 1000).toFixed(0)}k` : n;

const statusClass = (s) => ({
  active:    "sa-badge--green",
  trial:     "sa-badge--blue",
  expired:   "sa-badge--red",
  cancelled: "sa-badge--red",
}[s] ?? "sa-badge--amber");

onMounted(async () => {
  try { data.value = await SuperAdminService.getReports(); }
  finally { loading.value = false; }
});
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 24px; }
.sa-loading-full { padding: 80px; text-align: center; color: #64748b; font-size: 14px; }

.sa-section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.sa-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.sa-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
.sa-card--wide { grid-column: span 2; }
.sa-card__title {
  font-size: 11px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px;
}
.sa-empty { text-align: center; color: #475569; font-size: 13px; padding: 24px; }

/* Bar chart */
.sa-bar-chart { display: flex; align-items: flex-end; gap: 8px; height: 140px; padding-top: 8px; }
.sa-bar-chart__col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
.sa-bar-chart__bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.sa-bar-chart__bar { width: 100%; border-radius: 4px 4px 0 0; min-height: 4px; background: linear-gradient(180deg, #3b82f6 0%, #1e3a5f 100%); }
.sa-bar-chart__bar--green { background: linear-gradient(180deg, #22c55e 0%, #14532d 100%); }
.sa-bar-chart__label { font-size: 10px; color: #475569; margin-top: 6px; }
.sa-bar-chart__val { font-size: 10px; color: #64748b; font-weight: 600; }

/* Plan bars */
.sa-plan-list { display: flex; flex-direction: column; gap: 12px; }
.sa-plan-row { display: flex; align-items: center; gap: 10px; }
.sa-plan-row__label { display: flex; flex-direction: column; width: 80px; flex-shrink: 0; font-size: 12px; color: #94a3b8; }
.sa-plan-row__count { font-size: 10px; color: #475569; }
.sa-plan-row__bar-wrap { flex: 1; height: 8px; background: #334155; border-radius: 4px; overflow: hidden; }
.sa-plan-row__bar { height: 100%; background: #3b82f6; border-radius: 4px; transition: width 0.5s; }
.sa-plan-row__rev { font-size: 12px; color: #60a5fa; font-weight: 600; white-space: nowrap; width: 80px; text-align: right; flex-shrink: 0; }

/* Status bars */
.sa-status-list { display: flex; flex-direction: column; gap: 10px; }
.sa-status-row { display: flex; align-items: center; gap: 10px; }
.sa-status-row__bar-wrap { flex: 1; height: 8px; background: #334155; border-radius: 4px; overflow: hidden; }
.sa-status-row__bar { height: 100%; border-radius: 4px; transition: width 0.5s; }
.sa-bar--active    { background: #22c55e; }
.sa-bar--trial     { background: #3b82f6; }
.sa-bar--expired   { background: #ef4444; }
.sa-bar--cancelled { background: #64748b; }
.sa-status-row__count { font-size: 13px; font-weight: 700; color: #f1f5f9; width: 36px; text-align: right; flex-shrink: 0; }

.sa-table-wrap { overflow-x: auto; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sa-table th { padding: 10px 12px; font-size: 11px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #334155; text-align: left; }
.sa-table td { padding: 10px 12px; color: #cbd5e1; border-bottom: 1px solid #1e293b; }
.sa-table tr:last-child td { border-bottom: none; }

.sa-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
.sa-badge--green { background: #14532d; color: #4ade80; }
.sa-badge--blue  { background: #1e3a5f; color: #60a5fa; }
.sa-badge--amber { background: #451a03; color: #fbbf24; }
.sa-badge--red   { background: #450a0a; color: #f87171; }
</style>
