<template>
  <div class="sa-page">
    <!-- Stat cards -->
    <div v-if="loading" class="sa-stats-grid">
      <div v-for="n in 9" :key="n" class="sa-stat-card sa-skeleton"></div>
    </div>

    <div v-else class="sa-stats-grid">
      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--blue">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Total Businesses</p>
          <p class="sa-stat-card__value">{{ stats.total_businesses?.toLocaleString() }}</p>
        </div>
      </div>

      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--green">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Total Users</p>
          <p class="sa-stat-card__value">{{ stats.total_users?.toLocaleString() }}</p>
        </div>
      </div>

      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--purple">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Active Stores</p>
          <p class="sa-stat-card__value">{{ stats.active_stores?.toLocaleString() }} <small>/ {{ stats.total_stores }}</small></p>
        </div>
      </div>

      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--green">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Active Subscriptions</p>
          <p class="sa-stat-card__value">{{ stats.active_subscriptions?.toLocaleString() }}</p>
        </div>
      </div>

      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--amber">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Trials Active</p>
          <p class="sa-stat-card__value">{{ stats.trial_subscriptions?.toLocaleString() }}</p>
        </div>
      </div>

      <div class="sa-stat-card">
        <div class="sa-stat-card__icon sa-stat-card__icon--red">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Expired Subscriptions</p>
          <p class="sa-stat-card__value">{{ stats.expired_subscriptions?.toLocaleString() }}</p>
        </div>
      </div>

      <div class="sa-stat-card sa-stat-card--wide">
        <div class="sa-stat-card__icon sa-stat-card__icon--green">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Total Revenue (All Time)</p>
          <p class="sa-stat-card__value">KES {{ stats.total_revenue?.toLocaleString(undefined, { minimumFractionDigits: 0 }) }}</p>
        </div>
      </div>

      <div class="sa-stat-card sa-stat-card--wide">
        <div class="sa-stat-card__icon sa-stat-card__icon--blue">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <div>
          <p class="sa-stat-card__label">Revenue This Month</p>
          <p class="sa-stat-card__value">KES {{ stats.revenue_this_month?.toLocaleString(undefined, { minimumFractionDigits: 0 }) }}</p>
        </div>
      </div>
    </div>

    <!-- Growth chart + tables -->
    <div class="sa-dashboard-grid">

      <!-- Growth chart (bar) -->
      <div class="sa-card sa-card--chart">
        <h2 class="sa-card__title">New Businesses (Last 6 Months)</h2>
        <div class="sa-bar-chart" v-if="growthChart.length">
          <div v-for="bar in growthChart" :key="bar.month" class="sa-bar-chart__col">
            <div class="sa-bar-chart__bar-wrap">
              <div
                class="sa-bar-chart__bar"
                :style="{ height: barHeight(bar.count) + '%' }"
                :title="`${bar.count} businesses`"
              ></div>
            </div>
            <p class="sa-bar-chart__label">{{ bar.month.split(' ')[0] }}</p>
            <p class="sa-bar-chart__val">{{ bar.count }}</p>
          </div>
        </div>
        <p v-else class="sa-empty">No data yet</p>
      </div>

      <!-- Expiring soon -->
      <div class="sa-card">
        <h2 class="sa-card__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Subscriptions Expiring in 7 Days
        </h2>
        <div v-if="expiringSoon.length" class="sa-table-wrap">
          <table class="sa-table">
            <thead><tr><th>Business</th><th>Plan</th><th>Ends</th><th>Days</th></tr></thead>
            <tbody>
              <tr v-for="s in expiringSoon" :key="s.business">
                <td>{{ s.business }}</td>
                <td><span class="sa-badge" :class="s.plan === 'trial' ? 'sa-badge--amber' : 'sa-badge--blue'">{{ s.plan }}</span></td>
                <td>{{ s.ends_at }}</td>
                <td><span class="sa-badge" :class="s.days <= 2 ? 'sa-badge--red' : 'sa-badge--amber'">{{ s.days }}d</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="sa-empty">No subscriptions expiring soon.</p>
      </div>

      <!-- Recent businesses -->
      <div class="sa-card sa-card--full">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
          <h2 class="sa-card__title" style="margin:0">Recent Businesses</h2>
          <router-link to="/superadmin/businesses" class="sa-link">View all →</router-link>
        </div>
        <div class="sa-table-wrap">
          <table class="sa-table">
            <thead><tr><th>Name</th><th>Email</th><th>Industry</th><th>Joined</th></tr></thead>
            <tbody>
              <tr v-for="b in recentBusinesses" :key="b.id" class="sa-table__row--link" @click="$router.push(`/superadmin/businesses/${b.id}`)">
                <td>{{ b.name }}</td>
                <td>{{ b.email }}</td>
                <td>{{ b.industry }}</td>
                <td>{{ b.joined }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SuperAdminService from "@/services/SuperAdminService";

const loading         = ref(true);
const stats           = ref({});
const recentBusinesses = ref([]);
const expiringSoon    = ref([]);
const growthChart     = ref([]);

const barHeight = (count) => {
  const max = Math.max(...growthChart.value.map(b => b.count), 1);
  return Math.round((count / max) * 100);
};

onMounted(async () => {
  try {
    const data = await SuperAdminService.getDashboard();
    stats.value            = data.stats;
    recentBusinesses.value = data.recent_businesses;
    expiringSoon.value     = data.expiring_soon;
    growthChart.value      = data.growth_chart;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.sa-page { display: flex; flex-direction: column; gap: 24px; }

.sa-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.sa-stat-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.sa-stat-card--wide { grid-column: span 2; }
.sa-skeleton { height: 80px; background: #1e293b; animation: sa-pulse 1.5s ease-in-out infinite; }
@keyframes sa-pulse { 0%,100%{opacity:1}50%{opacity:0.4} }

.sa-stat-card__icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.sa-stat-card__icon--blue   { background: #1e3a5f; color: #60a5fa; }
.sa-stat-card__icon--green  { background: #14532d; color: #4ade80; }
.sa-stat-card__icon--purple { background: #3b0764; color: #c084fc; }
.sa-stat-card__icon--amber  { background: #451a03; color: #fbbf24; }
.sa-stat-card__icon--red    { background: #450a0a; color: #f87171; }

.sa-stat-card__label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
.sa-stat-card__value { font-size: 24px; font-weight: 800; color: #f1f5f9; letter-spacing: -0.5px; margin-top: 2px; }
.sa-stat-card__value small { font-size: 14px; font-weight: 500; color: #64748b; }

.sa-dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.sa-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 20px;
}
.sa-card--chart { }
.sa-card--full  { grid-column: span 2; }
.sa-card__title {
  font-size: 13px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.sa-bar-chart {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  height: 140px;
  padding-top: 8px;
}
.sa-bar-chart__col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
}
.sa-bar-chart__bar-wrap {
  flex: 1;
  width: 100%;
  display: flex;
  align-items: flex-end;
}
.sa-bar-chart__bar {
  width: 100%;
  background: linear-gradient(180deg, #3b82f6 0%, #1e3a5f 100%);
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: height 0.3s;
}
.sa-bar-chart__label { font-size: 10px; color: #475569; margin-top: 6px; }
.sa-bar-chart__val   { font-size: 11px; color: #94a3b8; font-weight: 600; }

.sa-table-wrap { overflow-x: auto; }
.sa-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.sa-table th {
  text-align: left;
  padding: 8px 12px;
  font-size: 11px;
  color: #475569;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-bottom: 1px solid #334155;
}
.sa-table td {
  padding: 10px 12px;
  color: #cbd5e1;
  border-bottom: 1px solid #1e293b;
}
.sa-table__row--link { cursor: pointer; }
.sa-table__row--link:hover td { background: #0f172a; }

.sa-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.sa-badge--blue   { background: #1e3a5f; color: #60a5fa; }
.sa-badge--green  { background: #14532d; color: #4ade80; }
.sa-badge--amber  { background: #451a03; color: #fbbf24; }
.sa-badge--red    { background: #450a0a; color: #f87171; }

.sa-empty { text-align: center; color: #475569; font-size: 13px; padding: 24px; }
.sa-link  { font-size: 13px; color: #60a5fa; text-decoration: none; }
.sa-link:hover { text-decoration: underline; }
</style>
