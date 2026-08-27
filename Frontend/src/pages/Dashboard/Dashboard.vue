<template>
  <div class="p-4 md:p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl md:text-2xl font-extrabold tracking-tight text-slate-900">
          Bizaa <span class="text-[#229DE7]">Dashboard</span>
        </h1>
        <p class="text-slate-500 mt-0.5 text-xs md:text-sm">
          {{ greeting }},
          <span class="font-bold text-slate-700">{{ user?.name || "Admin" }}</span> —
          {{ todayDate }}
        </p>
      </div>

      <!-- QUICK ACTIONS -->
      <div class="flex flex-wrap gap-2">
        <router-link
          to="/sales/pos"
          class="flex items-center gap-1.5 px-3 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95"
        >
          <ShoppingCart class="w-3.5 h-3.5 flex-shrink-0" /><span class="hidden sm:inline"
            >New Sale</span
          >
        </router-link>
        <router-link
          to="/products/add"
          class="flex items-center gap-1.5 px-3 py-2 bg-white border border-[#D9EBF7] text-slate-600 text-xs font-bold rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <Package class="w-3.5 h-3.5 flex-shrink-0" /><span class="hidden sm:inline"
            >Add Product</span
          >
        </router-link>
        <router-link
          to="/customers/list"
          class="flex items-center gap-1.5 px-3 py-2 bg-white border border-[#D9EBF7] text-slate-600 text-xs font-bold rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <Users class="w-3.5 h-3.5 flex-shrink-0" /><span class="hidden sm:inline"
            >Add Customer</span
          >
        </router-link>
        <router-link
          to="#"
          class="flex items-center gap-1.5 px-3 py-2 bg-white border border-[#D9EBF7] text-slate-600 text-xs font-bold rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <Receipt class="w-3.5 h-3.5 flex-shrink-0" /><span class="hidden sm:inline"
            >Expense</span
          >
        </router-link>
        <router-link
          to="/stock/take"
          class="flex items-center gap-1.5 px-3 py-2 bg-white border border-[#D9EBF7] text-slate-600 text-xs font-bold rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
        >
          <Boxes class="w-3.5 h-3.5 flex-shrink-0" /><span class="hidden sm:inline"
            >Stock Take</span
          >
        </router-link>
      </div>
    </div>

    <!-- FILTERS ROW: Period + Branch -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-5 flex-wrap"
    >
      <!-- Period filter -->
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"
          >Period:</span
        >
        <button
          v-for="f in filterOptions"
          :key="f.value"
          @click="selectPeriod(f.value)"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
          :class="
            activeFilter === f.value
              ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
              : 'bg-white border border-[#D9EBF7] text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7]'
          "
        >
          {{ f.label }}
        </button>
      </div>

      <!-- Custom date range -->
      <div
        v-if="activeFilter === 'custom'"
        class="flex items-center gap-2 flex-wrap"
      >
        <input
          type="date"
          v-model="customStartDate"
          :max="customEndDate || todayISO"
          class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-[#D9EBF7] text-slate-600 focus:border-[#229DE7] focus:outline-none focus:ring-2 focus:ring-[#229DE7]/20 transition"
        />
        <span class="text-xs font-bold text-slate-400">to</span>
        <input
          type="date"
          v-model="customEndDate"
          :min="customStartDate"
          :max="todayISO"
          class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-[#D9EBF7] text-slate-600 focus:border-[#229DE7] focus:outline-none focus:ring-2 focus:ring-[#229DE7]/20 transition"
        />
        <button
          @click="fetchDashboardData()"
          :disabled="!customStartDate || !customEndDate"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
          :class="
            customStartDate && customEndDate
              ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30 hover:bg-[#1682C4]'
              : 'bg-slate-100 text-slate-400 cursor-not-allowed'
          "
        >
          Apply
        </button>
      </div>

      <div class="w-px h-5 bg-[#D9EBF7] hidden sm:block"></div>

      <!-- Branch filter -->
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"
          >Branch:</span
        >
        <button
          @click="
            activeBranch = '';
            fetchDashboardData();
          "
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
          :class="
            activeBranch === ''
              ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
              : 'bg-white border border-[#D9EBF7] text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7]'
          "
        >
          All
        </button>
        <button
          v-for="b in branches"
          :key="b.id"
          @click="
            activeBranch = b.id;
            fetchDashboardData();
          "
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all"
          :class="
            activeBranch === b.id
              ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
              : 'bg-white border border-[#D9EBF7] text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7]'
          "
        >
          {{ b.name }}
        </button>
      </div>
    </div>

    <!-- ══════════ NO DATA STATE ══════════ -->
    <transition name="fade">
      <div
        v-if="!loading && isEmpty"
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-10 md:p-20 text-center mb-6"
      >
        <div
          class="w-16 h-16 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-5"
        >
          <svg
            class="w-8 h-8 text-[#229DE7]"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            />
          </svg>
        </div>
        <h3 class="font-extrabold text-base text-slate-900 mb-1">
          No data for this period
        </h3>
        <p class="text-slate-400 text-sm max-w-sm mx-auto">
          No sales recorded for
          <span class="font-bold text-slate-600">{{ activeFilterLabel }}</span>
          <template v-if="activeBranch">
            at
            <span class="font-bold text-slate-600">{{
              branches.find((b) => b.id === activeBranch)?.name
            }}</span></template
          >. Try selecting a different period or branch.
        </p>
        <router-link
          to="/sales/pos"
          class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all"
        >
          <ShoppingCart class="w-3.5 h-3.5" /> Create First Sale
        </router-link>
      </div>
    </transition>

    <!-- ══════════ MAIN CONTENT ══════════ -->
    <template v-if="loading || !isEmpty">
      <!-- STATS ROW -->
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4 mb-5">
        <div
          v-for="card in statsCards"
          :key="card.id"
          class="bg-white border border-[#D9EBF7] rounded-2xl p-4 md:p-5 shadow-sm relative overflow-hidden group hover:border-[#B3DEFA] hover:shadow-md transition-all"
        >
          <div v-if="loading" class="space-y-2 animate-pulse">
            <div class="h-3 w-20 bg-slate-100 rounded"></div>
            <div class="h-7 w-28 bg-slate-100 rounded"></div>
            <div class="h-3 w-16 bg-slate-100 rounded"></div>
          </div>
          <template v-else>
            <div
              class="absolute top-0 left-0 right-0 h-[3px] rounded-t-2xl"
              :style="{ background: card.accent }"
            ></div>
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div
                  class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 truncate"
                >
                  {{ card.label }}
                </div>
                <div
                  class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight leading-none"
                >
                  <span
                    v-if="card.type === 'currency'"
                    class="text-xs font-bold text-[#229DE7] mr-0.5"
                    >KES</span
                  >
                  {{ formatValue(card.value) }}
                </div>
                <div class="flex items-center gap-1 mt-1.5">
                  <span
                    class="text-[10.5px] font-bold"
                    :class="card.trend >= 0 ? 'text-emerald-600' : 'text-red-500'"
                  >
                    {{ card.trend >= 0 ? "↑" : "↓" }} {{ Math.abs(card.trend) }}%
                  </span>
                  <span class="text-[10px] text-slate-400 font-semibold hidden sm:inline"
                    >vs previous</span
                  >
                </div>
              </div>
              <div
                class="w-9 h-9 md:w-10 md:h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :style="{ background: card.iconBg }"
              >
                <component
                  :is="card.icon"
                  class="w-4 h-4 md:w-5 md:h-5"
                  :style="{ color: card.iconColor }"
                />
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- CHARTS ROW -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-5 mb-5">
        <!-- LINE CHART -->
        <div
          class="lg:col-span-8 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-4 md:p-5">
            <div
              class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4"
            >
              <div>
                <h3 class="font-extrabold text-sm text-slate-900">Sales Overview</h3>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">
                  Revenue trend for selected period
                </p>
              </div>
              <div class="flex items-center gap-3 text-[10.5px] font-bold text-slate-400">
                <span class="flex items-center gap-1.5"
                  ><span class="w-2.5 h-2.5 rounded-full bg-[#229DE7] inline-block"></span
                  >Sales</span
                >
                <span class="flex items-center gap-1.5"
                  ><span
                    class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"
                  ></span
                  >Profit</span
                >
              </div>
            </div>
            <div
              v-if="loading"
              class="h-48 md:h-64 animate-pulse bg-slate-50 rounded-xl flex items-center justify-center"
            >
              <div
                class="w-8 h-8 rounded-full border-4 border-[#D9EBF7] border-t-[#229DE7] animate-spin"
              ></div>
            </div>
            <div
              v-else-if="!lineChartData.labels.length"
              class="h-48 md:h-64 flex flex-col items-center justify-center text-center gap-2"
            >
              <svg
                class="w-10 h-10 text-slate-200"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
                />
              </svg>
              <p class="text-xs font-semibold text-slate-400">
                No chart data for this period
              </p>
            </div>
            <div v-else class="h-48 md:h-64">
              <LineChart :chart-data="lineChartData" :chart-options="lineChartOptions" />
            </div>
          </div>
        </div>

        <!-- PAYMENT METHODS -->
        <div
          class="lg:col-span-4 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-4 md:p-5">
            <h3 class="font-extrabold text-sm text-slate-900 mb-0.5">Payment Methods</h3>
            <p class="text-xs text-slate-400 font-semibold mb-4">
              How customers are paying
            </p>
            <div v-if="loading" class="h-36 md:h-44 flex items-center justify-center">
              <div
                class="w-8 h-8 rounded-full border-4 border-[#D9EBF7] border-t-[#229DE7] animate-spin"
              ></div>
            </div>
            <div
              v-else-if="!paymentMethods.length"
              class="h-36 md:h-44 flex flex-col items-center justify-center gap-2 text-center"
            >
              <svg
                class="w-10 h-10 text-slate-200"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                />
              </svg>
              <p class="text-xs font-semibold text-slate-400">No payment data yet</p>
            </div>
            <template v-else>
              <div class="h-36 md:h-44 flex items-center justify-center">
                <PieChart :chart-data="pieChartData" :chart-options="pieChartOptions" />
              </div>
              <div class="mt-4 space-y-2">
                <div
                  v-for="method in paymentMethods"
                  :key="method.label"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center gap-2">
                    <span
                      class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                      :style="{ background: method.color }"
                    ></span>
                    <span class="text-xs font-bold text-slate-600">{{
                      method.label
                    }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <div
                      class="w-16 md:w-20 h-1.5 bg-slate-100 rounded-full overflow-hidden"
                    >
                      <div
                        class="h-full rounded-full"
                        :style="{ width: method.pct + '%', background: method.color }"
                      ></div>
                    </div>
                    <span class="text-xs font-extrabold text-slate-700 w-8 text-right"
                      >{{ method.pct }}%</span
                    >
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- BOTTOM ROW 1: Products + Transactions + Low Stock -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 md:gap-5 mb-5">
        <!-- TOP PRODUCTS -->
        <div
          class="md:col-span-1 lg:col-span-4 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-4 md:p-5">
            <h3 class="font-extrabold text-sm text-slate-900 mb-0.5">Top Products</h3>
            <p class="text-xs text-slate-400 font-semibold mb-4">
              Best sellers this period
            </p>
            <div v-if="loading" class="space-y-3">
              <div
                v-for="n in 5"
                :key="n"
                class="h-12 bg-slate-50 animate-pulse rounded-xl"
              ></div>
            </div>
            <div v-else-if="!topProducts.length" class="py-8 text-center">
              <svg
                class="w-10 h-10 text-slate-200 mx-auto mb-2"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                />
              </svg>
              <p class="text-xs font-semibold text-slate-400">
                No product sales this period
              </p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="(product, i) in topProducts"
                :key="product.name"
                class="flex items-center gap-3 p-3 rounded-xl bg-[#F5FAFF] border border-[#D9EBF7] hover:border-[#B3DEFA] transition group"
              >
                <div
                  class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                >
                  {{ i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-xs text-slate-900 truncate">
                    {{ product.name }}
                  </div>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="flex-1 h-1 bg-slate-200 rounded-full overflow-hidden">
                      <div
                        class="h-full rounded-full bg-[#229DE7]"
                        :style="{ width: product.pct + '%' }"
                      ></div>
                    </div>
                    <span
                      class="text-[10px] font-bold text-slate-400 w-14 text-right flex-shrink-0"
                      >{{ product.units }} units</span
                    >
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <div class="text-xs font-extrabold text-slate-900">
                    KES {{ formatValue(product.revenue) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RECENT TRANSACTIONS -->
        <div
          class="md:col-span-1 lg:col-span-5 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="font-extrabold text-sm text-slate-900">Recent Transactions</h3>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">
                  Latest sales activity
                </p>
              </div>
              <router-link
                to="/sales/list"
                class="text-xs font-bold text-[#229DE7] hover:underline flex-shrink-0"
                >View all →</router-link
              >
            </div>
            <div v-if="loading" class="space-y-3">
              <div
                v-for="n in 5"
                :key="n"
                class="h-14 bg-slate-50 animate-pulse rounded-xl"
              ></div>
            </div>
            <div v-else-if="!recentTransactions.length" class="py-8 text-center">
              <svg
                class="w-10 h-10 text-slate-200 mx-auto mb-2"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
              <p class="text-xs font-semibold text-slate-400">
                No transactions recorded yet
              </p>
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="tx in recentTransactions"
                :key="tx.receipt"
                class="flex items-center gap-2 md:gap-3 p-3 rounded-xl hover:bg-[#F5FAFF] transition group"
              >
                <div
                  class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0 group-hover:scale-105 transition-transform"
                >
                  <ShoppingCart class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-xs text-slate-900 truncate">
                    {{ tx.customer }}
                  </div>
                  <div class="font-mono text-[10px] text-slate-400">{{ tx.receipt }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                  <div class="text-xs font-extrabold text-slate-900">
                    KES {{ formatValue(tx.amount) }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-semibold">
                    {{ tx.time }}
                  </div>
                </div>
                <span
                  class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border flex-shrink-0"
                  :class="methodBadge(tx.method)"
                  >{{ tx.method }}</span
                >
              </div>
            </div>
          </div>
        </div>

        <!-- LOW STOCK -->
        <div
          class="md:col-span-2 lg:col-span-3 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-amber-400 via-orange-400 to-amber-400"
          ></div>
          <div class="p-4 md:p-5">
            <div class="flex items-center gap-2 mb-1">
              <div
                class="w-5 h-5 rounded-md bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0"
              >
                <svg
                  class="w-3 h-3 text-amber-500"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <h3 class="font-extrabold text-sm text-slate-900">Low Stock</h3>
              <span
                v-if="lowStockItems.length"
                class="ml-auto text-[10px] font-extrabold px-2 py-0.5 bg-red-50 text-red-500 border border-red-200 rounded-full"
              >
                {{ lowStockItems.length }} item{{ lowStockItems.length > 1 ? "s" : "" }}
              </span>
            </div>
            <p class="text-xs text-slate-400 font-semibold mb-4">Items needing restock</p>
            <div v-if="loading" class="space-y-2">
              <div
                v-for="n in 4"
                :key="n"
                class="h-10 bg-slate-50 animate-pulse rounded-xl"
              ></div>
            </div>
            <div v-else-if="!lowStockItems.length" class="py-6 text-center">
              <div
                class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mx-auto mb-2"
              >
                <svg
                  class="w-5 h-5 text-emerald-500"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
              <p class="text-xs font-bold text-emerald-600">All stock levels OK</p>
              <p class="text-[10px] text-slate-400 mt-0.5">No items need restocking</p>
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="item in lowStockItems"
                :key="item.name"
                class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 border border-amber-200 hover:border-amber-300 transition"
              >
                <div class="min-w-0">
                  <div class="font-bold text-xs text-slate-800 truncate">
                    {{ item.name }}
                  </div>
                  <div class="text-[10px] font-semibold text-slate-400 mt-0.5">
                    {{ item.category }}
                  </div>
                </div>
                <span
                  class="text-[10px] font-extrabold ml-2 flex-shrink-0 px-2 py-0.5 rounded-full border"
                  :class="
                    Number(item.stock) === 0
                      ? 'bg-red-50 text-red-600 border-red-200'
                      : 'bg-amber-100 text-amber-700 border-amber-200'
                  "
                >
                  {{ Number(item.stock) === 0 ? "Out" : Number(item.stock) + " left" }}
                </span>
              </div>
            </div>
            <router-link
              to="/products/list"
              class="mt-4 w-full flex items-center justify-center gap-2 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-xs font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
            >
              View Inventory →
            </router-link>
          </div>
        </div>
      </div>

      <!-- BOTTOM ROW 2: Today Summary + Top Branches -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-5 mt-5">
        <!-- TODAY SALES SUMMARY — list format -->
        <div
          class="lg:col-span-4 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-5 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <h3 class="font-extrabold text-sm text-slate-900">Today's Sales Summary</h3>
            <span
              class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Live
            </span>
          </div>

          <!-- Skeleton -->
          <div v-if="loading" class="p-4 space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-10 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else class="divide-y divide-[#EEF5FB]">
            <div
              v-for="item in todaySummaryItems"
              :key="item.label"
              class="flex items-center justify-between px-5 py-3.5 hover:bg-[#F5FAFF] transition"
            >
              <span class="text-sm font-semibold text-slate-600">{{ item.label }}</span>
              <div
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-extrabold text-sm min-w-[100px] justify-end"
                :class="item.badgeClass || 'bg-[#EAF5FD] text-[#1682C4]'"
              >
                <svg
                  class="w-3 h-3 opacity-70 flex-shrink-0"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                {{
                  item.currency ? formatValue(item.value) : item.value.toLocaleString()
                }}
              </div>
            </div>
          </div>
        </div>

        <!-- TOP BRANCHES -->
        <div
          class="lg:col-span-8 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-5 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <div>
              <h3 class="font-extrabold text-sm text-slate-900">Top Selling Branches</h3>
              <p class="text-xs text-slate-400 font-semibold mt-0.5">
                Performance breakdown by branch
              </p>
            </div>
            <span
              class="text-[10px] font-bold text-[#1682C4] bg-[#EAF5FD] border border-[#B3DEFA] px-2.5 py-1 rounded-full"
            >
              {{ activeFilterLabel }}
            </span>
          </div>

          <!-- Skeleton -->
          <div v-if="loading" class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="n in 4"
              :key="n"
              class="h-28 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <!-- Empty -->
          <div v-else-if="!topBranches.length" class="py-12 text-center">
            <svg
              class="w-10 h-10 text-slate-200 mx-auto mb-2"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
            <p class="text-xs font-semibold text-slate-400">
              No branch data for this period
            </p>
          </div>

          <!-- Branch cards -->
          <div v-else class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="(branch, i) in topBranches"
              :key="branch.id"
              class="relative rounded-xl border border-[#D9EBF7] overflow-hidden hover:border-[#B3DEFA] hover:shadow-md transition-all group"
            >
              <!-- Rank badge -->
              <div
                class="absolute top-3 right-3 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-extrabold"
                :class="
                  i === 0
                    ? 'bg-amber-400 text-white'
                    : i === 1
                    ? 'bg-slate-300 text-slate-700'
                    : i === 2
                    ? 'bg-orange-300 text-white'
                    : 'bg-[#EAF5FD] text-[#229DE7]'
                "
              >
                {{ i + 1 }}
              </div>

              <div class="p-4">
                <div class="flex items-center gap-2.5 mb-3">
                  <div
                    class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0 group-hover:scale-105 transition-transform"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                      />
                    </svg>
                  </div>
                  <div class="min-w-0 pr-6">
                    <div class="font-extrabold text-sm text-slate-900 truncate">
                      {{ branch.name }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-semibold">
                      {{ branch.sales_count }} sale{{
                        branch.sales_count !== 1 ? "s" : ""
                      }}
                    </div>
                  </div>
                </div>

                <!-- Revenue bar -->
                <div class="mb-3">
                  <div class="flex justify-between items-center mb-1">
                    <span
                      class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                      >Revenue</span
                    >
                    <span class="text-xs font-extrabold text-[#229DE7]"
                      >{{ branch.pct }}%</span
                    >
                  </div>
                  <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full bg-gradient-to-r from-[#229DE7] to-[#5BB8F5] transition-all duration-500"
                      :style="{ width: branch.pct + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-2">
                  <div class="bg-[#F5FAFF] rounded-lg px-3 py-2 border border-[#D9EBF7]">
                    <div
                      class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                    >
                      Total Sales
                    </div>
                    <div class="text-sm font-extrabold text-slate-900">
                      {{ formatValue(branch.total_sales) }}
                    </div>
                    <div class="text-[9px] text-slate-400 font-semibold">KES</div>
                  </div>
                  <div class="bg-[#F5FAFF] rounded-lg px-3 py-2 border border-[#D9EBF7]">
                    <div
                      class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                    >
                      Revenue
                    </div>
                    <div class="text-sm font-extrabold text-emerald-600">
                      {{ formatValue(branch.revenue) }}
                    </div>
                    <div class="text-[9px] text-slate-400 font-semibold">KES</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import DashboardService from "@/services/DashboardService";
import BranchesService from "@/services/Branchesservice";
import {
  Package,
  Users,
  ShoppingCart,
  DollarSign,
  Gift,
  Receipt,
  Boxes,
  TrendingUp,
} from "lucide-vue-next";
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

/* ─── STATE ───────────────────────────── */
const loading = ref(false);
const user = ref(null);
const activeFilter = ref("year");
const activeBranch = ref("");
const branches = ref([]);
const customStartDate = ref("");
const customEndDate = ref("");
const todayISO = new Date().toISOString().slice(0, 10);

const filterOptions = [
  { label: "Today", value: "today" },
  { label: "This Week", value: "week" },
  { label: "This Month", value: "month" },
  { label: "This Year", value: "year" },
  { label: "Custom", value: "custom" },
];

const selectPeriod = (value) => {
  activeFilter.value = value;
  // For custom, wait until the user picks both dates and hits Apply
  if (value === "custom" && (!customStartDate.value || !customEndDate.value)) return;
  fetchDashboardData();
};

const activeFilterLabel = computed(() => {
  if (activeFilter.value === "custom" && customStartDate.value && customEndDate.value) {
    const fmt = (d) =>
      new Date(d).toLocaleDateString("en-KE", {
        day: "numeric",
        month: "short",
        year: "numeric",
      });
    return `${fmt(customStartDate.value)} – ${fmt(customEndDate.value)}`;
  }
  return filterOptions.find((f) => f.value === activeFilter.value)?.label ?? "";
});

/* ─── GREETING / DATE ─────────────────── */
const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 12) return "Good morning";
  if (h < 17) return "Good afternoon";
  return "Good evening";
});

const todayDate = computed(() =>
  new Date().toLocaleDateString("en-KE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  })
);

/* ─── TODAY SUMMARY ───────────────────── */
const todaySummary = reactive({
  total_transactions: 0,
  total_revenue: 0,
  paid: 0,
  balance: 0,
  discounts: 0,
  avg_sale: 0,
});

const todaySummaryItems = computed(() => [
  {
    label: "Total Sales",
    value: todaySummary.total_transactions,
    sub: "sales today",
    currency: false,
    badgeClass: "bg-[#EAF5FD] text-[#1682C4]",
  },
  {
    label: "Total Revenue",
    value: todaySummary.total_revenue,
    sub: "gross sales",
    currency: true,
    badgeClass: "bg-[#EAF5FD] text-[#1682C4]",
  },
  {
    label: "Total Paid",
    value: todaySummary.paid,
    sub: "collected",
    currency: true,
    badgeClass: "bg-emerald-50 text-emerald-700",
  },
  {
    label: "Sales Balance",
    value: todaySummary.balance,
    sub: "outstanding",
    currency: true,
    badgeClass:
      todaySummary.balance > 0 ? "bg-red-50 text-red-600" : "bg-slate-50 text-slate-400",
  },
  {
    label: "Total Discounts",
    value: todaySummary.discounts,
    sub: "given today",
    currency: true,
    badgeClass: "bg-amber-50 text-amber-700",
  },
]);

/* ─── STATS CARDS ─────────────────────── */
const statsCards = reactive([
  {
    id: "total_sales",
    label: "Total Revenue",
    value: 0,
    trend: 0,
    icon: DollarSign,
    type: "currency",
    accent: "linear-gradient(90deg,#229DE7,#5BB8F5)",
    iconBg: "#EAF5FD",
    iconColor: "#229DE7",
  },

  {
    id: "today_sales",
    label: "Today's Revenue",
    value: 0,
    trend: 0,
    icon: TrendingUp,
    type: "currency",
    accent: "linear-gradient(90deg,#10B981,#34D399)",
    iconBg: "#ECFDF5",
    iconColor: "#10B981",
  },

  {
    id: "customers",
    label: "Customers",
    value: 0,
    trend: 0,
    icon: Users,
    type: "count",
    accent: "linear-gradient(90deg,#F59E0B,#FCD34D)",
    iconBg: "#FFFBEB",
    iconColor: "#F59E0B",
  },

  {
    id: "profit",
    label: "Gross Profit",
    value: 0,
    trend: 0,
    icon: Gift,
    type: "currency",
    accent: "linear-gradient(90deg,#8B5CF6,#A78BFA)",
    iconBg: "#F5F3FF",
    iconColor: "#8B5CF6",
  },

  {
    id: "expenses",
    label: "Expenses",
    value: 0,
    trend: 0,
    icon: Receipt,
    type: "currency",
    accent: "linear-gradient(90deg,#EF4444,#F87171)",
    iconBg: "#FEF2F2",
    iconColor: "#DC2626",
  },

  {
    id: "purchases",
    label: "Purchases",
    value: 0,
    trend: 0,
    icon: Package,
    type: "currency",
    accent: "linear-gradient(90deg,#F97316,#FB923C)",
    iconBg: "#FFF7ED",
    iconColor: "#EA580C",
  },

  {
    id: "purchase_balance",
    label: "Purchase Balance",
    value: 0,
    trend: 0,
    icon: Boxes,
    type: "currency",
    accent: "linear-gradient(90deg,#E11D48,#FB7185)",
    iconBg: "#FFF1F2",
    iconColor: "#E11D48",
  },
  {
  id: "net_profit",
  label: "Net Profit",
  value: 0,
  trend: 0,
  icon: TrendingUp,
  type: "currency",
  accent: "linear-gradient(90deg,#059669,#10B981)",
  iconBg: "#ECFDF5",
  iconColor: "#059669",
}
]);
/* ─── REACTIVE LISTS ─────────────────── */
const paymentMethods = ref([]);
const topProducts = ref([]);
const recentTransactions = ref([]);
const lowStockItems = ref([]);
const topBranches = ref([]);

/* ─── COMPUTED EMPTY ─────────────────── */
const isEmpty = computed(
  () =>
    !loading.value &&
    statsCards.every((c) => c.value === 0) &&
    !topProducts.value.length &&
    !recentTransactions.value.length
);

/* ─── CHART DATA ─────────────────────── */
const lineChartData = reactive({
  labels: [],
  datasets: [
    {
      label: "Sales",
      data: [],
      borderColor: "#229DE7",
      fill: true,
      tension: 0.45,
      backgroundColor: "rgba(34,157,231,0.08)",
      pointBackgroundColor: "#229DE7",
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2.5,
    },
    {
      label: "Profit",
      data: [],
      borderColor: "#10B981",
      fill: false,
      tension: 0.45,
      backgroundColor: "transparent",
      pointBackgroundColor: "#10B981",
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2,
      borderDash: [5, 3],
    },
  ],
});

const pieChartData = reactive({
  labels: [],
  datasets: [{ data: [], backgroundColor: [], borderWidth: 0, hoverOffset: 6 }],
});

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: "#1e293b",
      titleFont: { size: 11, weight: "bold" },
      bodyFont: { size: 11 },
      padding: 10,
      callbacks: { label: (ctx) => ` KES ${Number(ctx.raw).toLocaleString()}` },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { size: 10, weight: "600" }, color: "#94a3b8", maxTicksLimit: 12 },
    },
    y: {
      grid: { color: "#f1f5f9" },
      ticks: {
        font: { size: 10 },
        color: "#94a3b8",
        callback: (v) => "KES " + (v >= 1000 ? (v / 1000).toFixed(0) + "k" : v),
      },
    },
  },
};

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: "68%",
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: "#1e293b",
      callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.raw}%` },
    },
  },
};

/* ─── CHART WRAPPERS ─────────────────── */
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

/* ─── HELPERS ────────────────────────── */
const PAYMENT_COLORS = {
  cash: "#229DE7",
  mpesa: "#10B981",
  card: "#F59E0B",
  bank: "#8B5CF6",
  other: "#94a3b8",
};

const formatValue = (val) =>
  isNaN(Number(val))
    ? "0"
    : Number(val).toLocaleString("en-KE", { maximumFractionDigits: 0 });

const methodBadge = (m) =>
  ({
    Mpesa: "bg-emerald-50 text-emerald-700 border-emerald-200",
    Cash: "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]",
    Card: "bg-amber-50 text-amber-600 border-amber-200",
  }[m] ?? "bg-slate-100 text-slate-500 border-slate-200");

/* ─── FETCH ──────────────────────────── */
const fetchDashboardData = async () => {
  if (
    activeFilter.value === "custom" &&
    (!customStartDate.value || !customEndDate.value)
  )
    return;

  loading.value = true;

  // Reset
  statsCards.forEach((c) => {
    c.value = 0;
    c.trend = 0;
  });
  paymentMethods.value = [];
  topProducts.value = [];
  recentTransactions.value = [];
  lowStockItems.value = [];
  topBranches.value = [];
  lineChartData.labels = [];
  lineChartData.datasets[0].data = [];
  lineChartData.datasets[1].data = [];
  pieChartData.labels = [];
  pieChartData.datasets[0].data = [];
  pieChartData.datasets[0].backgroundColor = [];
  Object.assign(todaySummary, {
    total_transactions: 0,
    total_revenue: 0,
    paid: 0,
    balance: 0,
    discounts: 0,
    avg_sale: 0,
  });

  try {
    const res = await DashboardService.getDashboardData({
      period: activeFilter.value,
      branch_id: activeBranch.value || undefined,
      ...(activeFilter.value === "custom" && {
        start_date: customStartDate.value,
        end_date: customEndDate.value,
      }),
    });
    const payload = res?.data ?? res;

    // Stats
    const stats = payload.stats ?? {};
    const trends = stats.trends ?? {};
    statsCards.forEach((card) => {
      switch (card.id) {
        case "expenses":
          card.value = stats.expenses?.total ?? 0;
          card.trend = trends.expenses ?? 0;
          break;

        case "purchases":
          card.value = stats.purchases?.total_purchases ?? 0;
          card.trend = trends.purchases ?? 0;
          break;

        case "purchase_balance":
          card.value = stats.purchases?.pending_amount ?? 0;
          card.trend = 0;
          break;

        default:
          card.value = stats[card.id] ?? 0;
          card.trend = trends[card.id] ?? 0;
      }
    });

    // Today summary
    const ts = payload.today_summary ?? {};
    Object.assign(todaySummary, {
      total_transactions: ts.total_transactions ?? 0,
      total_revenue: ts.total_revenue ?? 0,
      paid: ts.paid ?? 0,
      balance: ts.balance ?? 0,
      discounts: ts.discounts ?? 0,
      avg_sale: ts.avg_sale ?? 0,
    });

    // Chart
    const chart = payload.chart ?? {};
    lineChartData.labels = chart.labels ?? [];
    lineChartData.datasets[0].data = chart.sales ?? [];
    lineChartData.datasets[1].data = chart.profit ?? [];

    // Payments
    const payments = chart.payments ?? [];
    paymentMethods.value = payments.map((p) => ({
      label: p.label,
      pct: p.pct,
      color: PAYMENT_COLORS[p.method?.toLowerCase()] ?? "#94a3b8",
    }));
    pieChartData.labels = payments.map((p) => p.label);
    pieChartData.datasets[0].data = payments.map((p) => p.pct);
    pieChartData.datasets[0].backgroundColor = payments.map(
      (p) => PAYMENT_COLORS[p.method?.toLowerCase()] ?? "#94a3b8"
    );

    // Lists
    topProducts.value = payload.top_products ?? [];
    recentTransactions.value = payload.recent_transactions ?? [];
    lowStockItems.value = payload.low_stock ?? [];
    user.value = payload.user ?? null;

    // Top branches — compute pct relative to max
    const rawBranches = payload.top_branches ?? [];
    const maxRev = Math.max(...rawBranches.map((b) => b.total_sales ?? 0), 1);
    topBranches.value = rawBranches.map((b) => ({
      ...b,
      pct: Math.round(((b.total_sales ?? 0) / maxRev) * 100),
    }));
  } catch (err) {
    console.error("Dashboard fetch failed:", err);
  } finally {
    loading.value = false;
  }
};

const fetchBranches = async () => {
  try {
    const res = await BranchesService.getAllBranches();
    branches.value = res.data?.data || res.data || res || [];
  } catch {
    branches.value = [];
  }
};

onMounted(async () => {
  await fetchBranches();
  fetchDashboardData();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
