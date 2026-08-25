<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <main class="p-8 max-w-7xl mx-auto space-y-10">
      <!-- Header Tabs -->
      <section class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">  CRM Analytics</h1>
        <div class="flex rounded-xl overflow-hidden shadow-sm">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="activeTab = tab"
            :class="[
              'px-4 py-2 font-medium transition-all text-sm',
              activeTab === tab
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-600 hover:bg-gray-100',
            ]"
          >
            {{ tab }}
          </button>
        </div>
      </section>

      <!-- KPI Cards -->
      <section class="grid md:grid-cols-4 gap-6">
        <div
          v-for="stat in filteredStats"
          :key="stat.title"
          class="p-6 rounded-2xl shadow-md transform hover:scale-105 transition-all flex items-center gap-4"
          :class="stat.bgColor"
        >
          <!-- Icon -->
          <div
            class="p-4 rounded-full flex items-center justify-center"
            :class="stat.iconBg"
          >
            <component :is="stat.icon" class="w-7 h-7" :class="stat.iconColor" />
          </div>

          <!-- Text -->
          <div class="flex-1">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-white">
              {{ stat.title }}
            </h2>
            <p class="text-2xl font-bold text-white mt-1 flex items-center gap-2">
              {{ stat.value }}
              <span v-if="stat.trend" :class="stat.trend > 0 ? 'text-green-200' : 'text-red-200'">
                <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="stat.trend>0" d="M5 10l5-5 5 5H5z"/>
                  <path v-else d="M5 10l5 5 5-5H5z"/>
                </svg>
                {{ Math.abs(stat.trend) }}%
              </span>
            </p>
            <p class="text-xs text-white/70 mt-1">{{ stat.subtitle }}</p>
          </div>
        </div>
      </section>

      <!-- Charts -->
      <section class="grid lg:grid-cols-3 gap-6">
        <!-- Line Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-all col-span-2">
          <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            📈 Client Engagement Trends
          </h3>
          <canvas id="engagementChart" height="100"></canvas>
        </div>

        <!-- Doughnut Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-all">
          <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            🎯 Today’s Follow-up Rate
          </h3>
          <div class="flex justify-center">
            <canvas id="followupChart" width="180" height="180"></canvas>
          </div>
          <p class="text-center mt-3 text-gray-500 text-sm">
            82% of scheduled follow-ups completed
          </p>
        </div>
      </section>

      <!-- Upcoming Section -->
      <section class="grid md:grid-cols-2 gap-6">
        <!-- Meetings -->
        <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-all">
          <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            📅 Upcoming Meetings
          </h3>
          <ul class="space-y-2">
            <li
              v-for="meeting in meetings"
              :key="meeting.name"
              class="flex justify-between items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all"
            >
              <div class="flex items-center gap-3">
                <img :src="meeting.avatar" class="w-10 h-10 rounded-full border border-gray-200" />
                <div>
                  <p class="font-semibold text-gray-800">{{ meeting.name }}</p>
                  <p class="text-xs text-gray-500">{{ meeting.role }}</p>
                </div>
              </div>
              <span class="text-xs text-gray-500 px-2 py-1 bg-gray-200 rounded-full">{{ meeting.time }}</span>
            </li>
          </ul>
        </div>

        <!-- Deadlines -->
        <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-lg transition-all">
          <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            ⏰ Upcoming Deadlines
          </h3>
          <ul class="space-y-2">
            <li
              v-for="task in deadlines"
              :key="task.task"
              class="flex justify-between items-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-all"
            >
              <div>
                <p class="font-medium text-gray-800">{{ task.task }}</p>
                <p class="text-xs text-gray-500">{{ task.date }}</p>
              </div>
              <span
                :class="{
                  'bg-green-100 text-green-800': task.status === 'On Track',
                  'bg-yellow-100 text-yellow-800': task.status === 'Pending',
                  'bg-red-100 text-red-800': task.status === 'Overdue',
                }"
                class="px-2 py-1 rounded-full text-xs font-semibold"
              >
                {{ task.status }}
              </span>
            </li>
          </ul>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Chart from "chart.js/auto";
import { Users, UserPlus, TrendingUp, DollarSign } from "lucide-vue-next";

const tabs = ["Today", "This Week", "This Month", "This Year"];
const activeTab = ref("Today");
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
// Solid background KPI cards with trends
const allStats = {
  Today: [
    { title: "Active Clients", value: "312", subtitle: "Today", icon: Users, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-blue-600", trend: 5 },
    { title: "New Leads", value: "48", subtitle: "Leads captured", icon: UserPlus, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-green-600", trend: 12 },
    { title: "Conversions", value: "12", subtitle: "Deals closed", icon: TrendingUp, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-purple-600", trend: -3 },
    { title: "Revenue", value: "$1.2k", subtitle: "Today’s sales", icon: DollarSign, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-yellow-600", trend: 8 },
  ],
  "This Week": [
    { title: "Active Clients", value: "1,248", subtitle: "Weekly total", icon: Users, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-blue-600", trend: 10 },
    { title: "New Leads", value: "312", subtitle: "This week", icon: UserPlus, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-green-600", trend: 15 },
    { title: "Conversions", value: "86", subtitle: "Deals closed", icon: TrendingUp, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-purple-600", trend: 5 },
    { title: "Revenue", value: "$24.3k", subtitle: "Weekly revenue", icon: DollarSign, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-yellow-600", trend: 7 },
  ],
  "This Month": [
    { title: "Active Clients", value: "5,213", subtitle: "Monthly total", icon: Users, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-blue-600", trend: 20 },
    { title: "New Leads", value: "1,145", subtitle: "New contacts", icon: UserPlus, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-green-600", trend: 18 },
    { title: "Conversions", value: "320", subtitle: "Successful deals", icon: TrendingUp, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-purple-600", trend: 12 },
    { title: "Revenue", value: "$94.5k", subtitle: "This month’s sales", icon: DollarSign, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-yellow-600", trend: 15 },
  ],
  "This Year": [
    { title: "Active Clients", value: "12,840", subtitle: "Annual total", icon: Users, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-blue-600", trend: 25 },
    { title: "New Leads", value: "8,220", subtitle: "Generated this year", icon: UserPlus, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-green-600", trend: 22 },
    { title: "Conversions", value: "2,410", subtitle: "Yearly conversions", icon: TrendingUp, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-purple-600", trend: 18 },
    { title: "Revenue", value: "$1.3M", subtitle: "Annual sales", icon: DollarSign, iconBg: "bg-white/20", iconColor: "text-white", bgColor: "bg-yellow-600", trend: 20 },
  ],
};


const filteredStats = computed(() => allStats[activeTab.value]);

const meetings = ref([
  { name: "Mary Nthenya", role: "Marketing", time: "10:30 AM", avatar: "https://i.pravatar.cc/40?img=2" },
  { name: "John Doe", role: "Client Lead", time: "01:00 PM", avatar: "https://i.pravatar.cc/40?img=3" },
  { name: "Linda James", role: "Sales", time: "03:15 PM", avatar: "https://i.pravatar.cc/40?img=5" },
]);

const deadlines = ref([
  { date: "Oct 28", task: "Follow-up on ABC Ltd", status: "On Track" },
  { date: "Oct 30", task: "Email Campaign Setup", status: "Pending" },
  { date: "Nov 2", task: "Renew Contract – XYZ", status: "Overdue" },
]);

onMounted(() => {
  // Line Chart
  const ctx1 = document.getElementById("engagementChart").getContext("2d");
  const gradient = ctx1.createLinearGradient(0, 0, 0, 200);
  gradient.addColorStop(0, "rgba(59,130,246,0.3)");
  gradient.addColorStop(1, "rgba(59,130,246,0)");
  new Chart(ctx1, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"],
      datasets: [{ label: "Engagement", data: [25,45,35,50,70,55,65,80,95], borderColor: "#3B82F6", backgroundColor: gradient, fill: true, tension: 0.4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // Doughnut Chart
  const ctx2 = document.getElementById("followupChart").getContext("2d");
  new Chart(ctx2, {
    type: "doughnut",
    data: { labels: ["Completed", "Pending"], datasets: [{ data: [82,18], backgroundColor: ["#10B981","#E5E7EB"], borderWidth: 0 }] },
    options: { cutout: "75%", plugins: { legend: { display: false } } }
  });
});
</script>
