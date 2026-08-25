<template>
  <div
    class="min-h-screen bg-white dark:bg-slate-950 p-6 md:p-12 relative overflow-hidden"
  >
    <!-- BACKGROUND -->
    <div class="fixed inset-0 pointer-events-none">
      <div
        class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-[#229DE7]/10 blur-3xl rounded-full"
      ></div>
      <div
        class="absolute bottom-0 -left-40 w-[400px] h-[400px] bg-blue-500/10 blur-3xl rounded-full"
      ></div>
    </div>

    <!-- HEADER -->
    <div
      class="max-w-7xl mx-auto mb-14 relative z-10 flex flex-col xl:flex-row xl:items-end justify-between gap-8"
    >
      <div>
        <div class="flex items-center gap-3 mb-3">
          <span class="h-px w-8 bg-[#229DE7]"></span>
          <span class="text-[10px] font-black uppercase tracking-[0.4em] text-[#229DE7]">
            Bizaa SYSTEM
          </span>
        </div>

        <h1 class="text-4xl font-black text-slate-900 dark:text-white">
          POS <span class="text-[#229DE7]">Dashboard</span>
        </h1>

        <p v-if="adminInfo" class="text-sm text-slate-500 mt-2">
          Cashier:
          <span class="text-[#229DE7] font-bold">{{ adminInfo.full_name }}</span>
          <span class="mx-3">|</span>
          Terminal:
          <span class="font-mono font-bold">{{ adminInfo.memberID }}</span>
        </p>
      </div>

      <!-- ACTIONS -->
      <div class="flex items-center gap-4">
        <div
          class="flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800"
        >
          <button
            v-for="filter in timeFilters"
            :key="filter.id"
            @click="updateFilter(filter.id)"
            class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg"
            :class="
              activeFilter === filter.id
                ? 'bg-white dark:bg-slate-800 text-[#229DE7]'
                : 'text-slate-500'
            "
          >
            {{ filter.label }}
          </button>
        </div>

        <button
          @click="fetchDashboardData"
          class="flex items-center gap-2 px-5 py-2 rounded-full bg-white dark:bg-slate-900 border border-[#229DE7]/20"
        >
          <ArrowPathIcon
            class="w-4 h-4"
            :class="{ 'animate-spin text-[#229DE7]': loading }"
          />
          <span class="text-xs font-bold uppercase">Refresh</span>
        </button>
      </div>
    </div>

    <!-- STATS -->
    <!-- STATS (FIXED VISIBILITY) -->
    <section
      class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 relative z-10"
    >
      <router-link
        v-for="card in statsCards"
        :key="card.id"
        :to="card.path"
        class="group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
      >
        <!-- TOP LABEL -->
        <p
          class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400"
        >
          {{ card.label }}
        </p>

        <!-- VALUE + ICON -->
        <div class="flex justify-between items-center mt-4">
          <h2 class="text-2xl font-black text-slate-900 dark:text-white">
            <span
              v-if="card.type === 'currency'"
              class="text-[#229DE7] text-xs mr-1 font-bold"
            >
              KES
            </span>

            <!-- FORCE VISIBILITY EVEN IF ZERO -->
            <span v-if="!loading">
              {{ formatValue(card.value) }}
            </span>
            <span v-else class="animate-pulse text-slate-300">---</span>
          </h2>

          <!-- ICON FIXED VISIBILITY -->
          <div
            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
          >
            <component
              :is="card.icon"
              class="w-5 h-5 text-slate-600 dark:text-slate-300 group-hover:text-[#229DE7] transition"
            />
          </div>
        </div>

        <!-- PROGRESS BAR (ALWAYS VISIBLE) -->
        <div class="h-1 mt-5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
          <div
            class="h-full bg-[#229DE7] w-[40%] group-hover:w-full transition-all duration-500"
          ></div>
        </div>
      </router-link>
    </section>
    <!-- CHARTS -->
    <section
      class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 relative z-10"
    >
      <!-- SALES -->
      <div
        class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800"
      >
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-6">
          Sales Overview
        </h3>

        <div class="h-80">
          <LineChart
            v-if="!loading"
            :chart-data="lineChartData"
            :chart-options="chartOptions"
          />
        </div>
      </div>

      <!-- PAYMENTS -->
      <div
        class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800"
      >
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-6">
          Payments
        </h3>

        <div class="h-80 flex items-center justify-center">
          <PieChart
            v-if="!loading"
            :chart-data="pieChartData"
            :chart-options="chartOptions"
          />
        </div>
      </div>
    </section>

    <!-- TRANSACTIONS -->
    <section class="max-w-7xl mx-auto mt-14 relative z-10">
      <div class="flex items-center gap-4 mb-6">
        <h2 class="text-xs font-black uppercase tracking-widest text-slate-600">
          Recent Sales
        </h2>
        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-[10px] uppercase text-slate-400">
              <th class="text-left py-3">Item</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Total</th>
              <th class="text-center">Payment</th>
              <th class="text-right">Time</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="tx in transactions"
              :key="tx.id"
              class="border-t border-slate-100 dark:border-slate-800"
            >
              <td class="py-4 font-medium">{{ tx.message }}</td>
              <td class="text-center">1</td>
              <td class="text-center font-bold text-[#229DE7]">KES {{ tx.amount }}</td>
              <td class="text-center text-xs">{{ tx.category }}</td>
              <td class="text-right text-xs text-slate-400">{{ tx.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import api from "@/services/api";
import {
  CurrencyDollarIcon,
  CreditCardIcon,
  ArrowPathIcon,
  ChartBarIcon,
  UserGroupIcon,
  GiftIcon,
  IdentificationIcon,
} from "@heroicons/vue/24/outline";

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  ArcElement,
  Filler,
} from "chart.js";

import { Line, Doughnut } from "vue-chartjs";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  ArcElement,
  Filler
);

// STATE
const loading = ref(false);
const activeFilter = ref("all");
const adminInfo = ref(null);
const transactions = ref([]);

// FILTERS
const timeFilters = [
  { id: "today", label: "Today" },
  { id: "week", label: "Week" },
  { id: "month", label: "Month" },
  { id: "all", label: "All" },
];

// POS STATS (FIXED)
const statsCards = reactive([
  {
    id: "total_sales",
    label: "Total Revenue",
    value: 0,
    icon: CurrencyDollarIcon,
    path: "/pos/sales",
    type: "currency",
  },
  {
    id: "today_sales",
    label: "Today's Revenue",
    value: 0,
    icon: ChartBarIcon,
    path: "/pos/sales/today",
    type: "currency",
  },
  {
    id: "Customers",
    label: "Customers",
    value: 0,
    icon: CreditCardIcon,
    path: "/pos/transactions",
    type: "count",
  },
 
  {
    id: "low_stock",
    label: "Total Profit",
    value: 0,
    icon: GiftIcon,
    path: "/pos/inventory/low",
    type: "count",
  },
  {
    id: "products",
    label: "Products",
    value: 0,
    icon: IdentificationIcon,
    path: "/pos/products",
    type: "count",
  },
   {
    id: "products",
    label: "Products",
    value: 0,
    icon: IdentificationIcon,
    path: "/pos/products",
    type: "count",
  },
]);

// CHARTS
const lineChartData = reactive({
  labels: [],
  datasets: [
    { label: "Sales", data: [], borderColor: "#229DE7", fill: true, tension: 0.4 },
  ],
});

const pieChartData = reactive({
  labels: ["Cash", "Card", "M-Pesa"],
  datasets: [{ data: [], backgroundColor: ["#229DE7", "#3B82F6", "#94a3b8"] }],
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: "bottom" } },
};

// CHART WRAPPERS
const LineChart = {
  props: ["chartData", "chartOptions"],
  components: { Line },
  template: `<Line :data="chartData" :options="chartOptions" />`,
};
const PieChart = {
  props: ["chartData", "chartOptions"],
  components: { Doughnut },
  template: `<Doughnut :data="chartData" :options="chartOptions" />`,
};

// FORMAT
const formatValue = (val) => (isNaN(Number(val)) ? 0 : Number(val).toLocaleString());

// FETCH DATA (POS STYLE)
const fetchDashboardData = async () => {
  loading.value = true;

  try {
    const res = await api.get("/pos/dashboard?filter=" + activeFilter.value);

    const s = res.data.stats;

    statsCards.forEach((c) => {
      if (s[c.id] !== undefined) c.value = s[c.id];
    });

    transactions.value = res.data.transactions;

    lineChartData.labels = res.data.chart.labels;
    lineChartData.datasets[0].data = res.data.chart.sales;

    pieChartData.datasets[0].data = res.data.chart.payments;

    adminInfo.value = res.data.user;
  } catch (e) {
    console.error("POS sync failed:", e);
  } finally {
    loading.value = false;
  }
};

const updateFilter = (id) => {
  activeFilter.value = id;
  fetchDashboardData();
};

onMounted(fetchDashboardData);
</script>
