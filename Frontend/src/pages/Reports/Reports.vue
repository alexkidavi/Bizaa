<template>
  <!-- PAGE LOADER -->
  <transition name="fade">
    <div
      v-if="pageLoading"
      class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm"
    >
      <div class="relative flex items-center justify-center">
        <div class="h-16 w-16 rounded-full border-4 border-[#D9EBF7]"></div>
        <div
          class="absolute h-16 w-16 rounded-full border-4 border-t-[#229DE7] animate-spin"
        ></div>
      </div>
      <p
        class="mt-4 text-sm font-bold text-slate-500 tracking-widest uppercase animate-pulse"
      >
        Loading Reports...
      </p>
    </div>
  </transition>

  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="mb-7">
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Reports</h1>
      <p class="text-slate-500 mt-1 text-sm">
        Business analytics, financial summaries and stock movements.
      </p>
    </div>

    <!-- TAB NAV — a wrapping grid so every report is visible at once,
         no horizontal scrolling needed even as the report count grows. -->
    <div
      class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm mb-6 overflow-hidden"
    >
      <div
        class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
      ></div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-2 p-4">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="switchTab(tab.key)"
          class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold transition-all border"
          :class="
            activeTab === tab.key
              ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm'
              : 'bg-[#F5FAFF] text-slate-500 border-transparent hover:bg-[#EAF5FD] hover:text-[#229DE7]'
          "
        >
          <span v-html="tab.icon" class="w-4 h-4 flex-shrink-0"></span>
          <span class="truncate">{{ tab.label }}</span>
        </button>
      </div>
    </div>

    <!-- ═══════════ SHARED DATE FILTER BAR ═══════════ -->
    <div
      v-if="!['customer-balances', 'supplier-balances', 'account-statement'].includes(activeTab)"
      class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5 mb-5"
    >
      <div class="flex flex-wrap items-end gap-4">
        <!-- Close Day: from / to range -->
        <template v-if="activeTab === 'close-day'">
          <div>
            <label class="field-label">From</label>
            <input v-model="filters.closeDayFrom" type="date" class="field-input w-44" :max="filters.closeDayTo" />
          </div>
          <div>
            <label class="field-label">To</label>
            <input v-model="filters.closeDayTo" type="date" class="field-input w-44" :min="filters.closeDayFrom" :max="today" />
          </div>
        </template>

        <!-- Other reports: preset pills + optional custom range -->
        <template v-else>
          <div>
            <label class="field-label">Period</label>
            <div class="flex gap-2 mt-1">
              <button
                v-for="p in presets"
                :key="p.value"
                @click="filters.preset = p.value"
                class="px-3.5 py-2 rounded-xl text-xs font-bold border transition"
                :class="
                  filters.preset === p.value
                    ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm'
                    : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'
                "
              >
                {{ p.label }}
              </button>
            </div>
          </div>
          <transition name="slide">
            <div v-if="filters.preset === 'custom'" class="flex items-end gap-3">
              <div>
                <label class="field-label">From</label
                ><input v-model="filters.from" type="date" class="field-input w-40" />
              </div>
              <div>
                <label class="field-label">To</label
                ><input v-model="filters.to" type="date" class="field-input w-40" />
              </div>
            </div>
          </transition>

          

          <!-- Branch filter -->
          <div v-if="['items-sale', 'general-sales', 'revenue-item', 'staff-sales'].includes(activeTab)">
            <label class="field-label">Branch</label>
            <select v-model="filters.branch_id" class="field-input w-44">
              <option value="">All Branches</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">
                {{ b.name }}
              </option>
            </select>
          </div>

          <!-- Payment method filter (general sales only) -->
          <div v-if="activeTab === 'general-sales'">
            <label class="field-label">Payment Method</label>
            <select v-model="filters.payment_method" class="field-input w-40">
              <option value="">All Methods</option>
              <option value="cash">Cash</option>
              <option value="mpesa">M-Pesa</option>
              <option value="card">Card</option>
              <option value="credit">Credit</option>
            </select>
          </div>

          <!-- Supplier filter (items purchase only) -->
          <div v-if="activeTab === 'items-purchase'">
            <label class="field-label">Supplier</label>
            <select v-model="filters.supplier_id" class="field-input w-44">
              <option value="">All Suppliers</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">
                {{ s.name }}
              </option>
            </select>
          </div>

          <!-- Category filter (expenses only) -->
          <div v-if="activeTab === 'expenses'">
            <label class="field-label">Category</label>
            <select v-model="filters.category_id" class="field-input w-44">
              <option value="">All Categories</option>
              <option v-for="c in expenseCategories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>
        </template>

        <div class="flex items-center gap-2 ml-auto">
          <button @click="runReport" :disabled="loading" class="run-btn">
            <svg
              v-if="loading"
              class="animate-spin h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
            <svg
              v-else
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            {{ loading ? "Generating..." : "Generate Report" }}
          </button>
          <ExportButtons :exporting="exporting" @export="exportReport" />
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════
         1. CLOSE DAY REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'close-day'">
      <div v-if="data.closeDay" class="space-y-5">
        <!-- Row 1: 4 key stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Transactions</p>
            <p class="stat-value text-slate-800">
              {{ data.closeDay.total_transactions }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Sales</p>
            <p class="stat-value text-[#229DE7]">{{ fmt(data.closeDay.gross_sales) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Net Sales</p>
            <p class="stat-value text-slate-800">{{ fmt(data.closeDay.net_sales) }}</p>
            <p class="text-[10px] text-slate-400 mt-1">After discounts & refunds</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Profit</p>
            <p
              class="stat-value"
              :class="
                data.closeDay.gross_profit >= 0 ? 'text-emerald-600' : 'text-red-500'
              "
            >
              {{ fmt(data.closeDay.gross_profit) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-1">
              Margin: {{ data.closeDay.profit_margin }}%
            </p>
          </div>
        </div>

        <!-- Row 2: refunds + discounts + qty -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card border-amber-100">
            <p class="stat-label">Discounts Given</p>
            <p class="stat-value text-amber-500">
              {{ fmt(data.closeDay.total_discounts) }}
            </p>
          </div>
          <div class="stat-card border-red-100">
            <p class="stat-label">Refunds</p>
            <p class="stat-value text-red-500">{{ fmt(data.closeDay.refund_total) }}</p>
            <p class="text-[10px] text-slate-400 mt-1">
              {{ data.closeDay.refund_count }} refund(s)
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Cost of Goods</p>
            <p class="stat-value text-slate-600">{{ fmt(data.closeDay.total_cogs) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Items Sold</p>
            <p class="stat-value text-slate-800">{{ data.closeDay.total_qty_sold }}</p>
          </div>
        </div>

        <!-- Row 3: payment + summary + top items -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- Payment breakdown -->
          <div class="report-card">
            <div class="report-card-header">Payment Breakdown</div>
            <div class="p-5 space-y-3">
              <div
                v-for="(val, key) in data.closeDay.payment_breakdown"
                :key="key"
                class="flex items-center justify-between"
              >
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full" :class="paymentDot(key)"></span>
                  <span class="text-sm font-semibold text-slate-600 capitalize">{{
                    key
                  }}</span>
                </div>
                <span class="text-sm font-bold text-slate-900">{{ fmt(val) }}</span>
              </div>
            </div>
          </div>

          <!-- Day summary -->
          <div class="report-card">
            <div class="report-card-header">Day Summary</div>
            <div class="p-5 space-y-3">
              <div class="summary-row">
                <span>Total Paid</span
                ><span class="font-bold text-emerald-600">{{
                  fmt(data.closeDay.total_paid)
                }}</span>
              </div>
              <div class="summary-row">
                <span>Balance Due</span
                ><span class="font-bold text-red-500">{{
                  fmt(data.closeDay.total_balance_due)
                }}</span>
              </div>
              <div class="summary-row">
                <span>Tax Collected</span
                ><span class="font-bold">{{ fmt(data.closeDay.total_tax) }}</span>
              </div>
              <div class="summary-row border-t border-[#EEF5FB] pt-3 mt-1">
                <span class="font-bold text-slate-700">Net Sales</span>
                <span class="font-extrabold text-[#229DE7]">{{
                  fmt(data.closeDay.net_sales)
                }}</span>
              </div>
            </div>
          </div>

          <!-- Top items -->
          <div class="report-card">
            <div class="report-card-header">Top 5 Items</div>
            <div class="p-5 space-y-3">
              <div
                v-for="(item, i) in data.closeDay.top_items"
                :key="i"
                class="flex items-center justify-between gap-2"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span
                    class="w-5 h-5 rounded-lg bg-[#EAF5FD] text-[#229DE7] text-[10px] font-extrabold flex items-center justify-center flex-shrink-0"
                    >{{ i + 1 }}</span
                  >
                  <span class="text-xs font-semibold text-slate-700 truncate">{{
                    item.product_name
                  }}</span>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-xs font-bold text-[#229DE7]">{{ fmt(item.revenue) }}</p>
                  <p class="text-[10px] text-slate-400">qty: {{ item.qty_sold }}</p>
                </div>
              </div>
              <p
                v-if="!data.closeDay.top_items?.length"
                class="text-xs text-slate-400 text-center py-2"
              >
                No items data
              </p>
            </div>
          </div>
        </div>

        <!-- Daily Breakdown -->
        <div v-if="data.closeDay.daily_totals?.length" class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Daily Breakdown</h3>
            <span class="text-xs text-slate-400 font-semibold">{{ data.closeDay.daily_totals.length }} day(s)</span>
          </div>
          <table class="report-table">
            <thead>
              <tr class="report-thead-tr">
                <th class="report-th">Date</th>
                <th class="report-th text-right">Transactions</th>
                <th class="report-th text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="d in data.closeDay.daily_totals" :key="d.date" class="hover:bg-[#F5FAFF]">
                <td class="report-td font-semibold">{{ formatDate(d.date) }}</td>
                <td class="report-td text-right text-slate-500">{{ d.transactions }}</td>
                <td class="report-td text-right font-bold text-[#229DE7]">{{ fmt(d.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sales by branch -->
        <div v-if="data.closeDay.sales_by_branch?.length" class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Sales by Branch</h3>
          </div>
          <table class="report-table">
            <thead>
              <tr class="report-thead-tr">
                <th class="report-th">Branch</th>
                <th class="report-th text-right">Transactions</th>
                <th class="report-th text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="b in data.closeDay.sales_by_branch"
                :key="b.branch"
                class="hover:bg-[#F5FAFF]"
              >
                <td class="report-td font-semibold">{{ b.branch }}</td>
                <td class="report-td text-right">{{ b.count }}</td>
                <td class="report-td text-right font-bold text-[#229DE7]">
                  {{ fmt(b.total) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Select a date range above and click Generate Report."
      />
    </template>

    <!-- ═══════════════════════════════════════
         2. ITEMS SALE REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'items-sale'">
      <div v-if="data.itemsSale" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Products Sold</p>
            <p class="stat-value text-slate-800">
              {{ data.itemsSale.summary.total_products }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Qty Sold</p>
            <p class="stat-value text-[#229DE7]">
              {{ data.itemsSale.summary.total_qty_sold }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Net Revenue</p>
            <p class="stat-value text-slate-800">
              {{ fmt(data.itemsSale.summary.net_revenue) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Profit</p>
            <p class="stat-value text-emerald-600">
              {{ fmt(data.itemsSale.summary.gross_profit) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-1">
              Margin: {{ data.itemsSale.summary.profit_margin }}%
            </p>
          </div>
        </div>

        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Items Sale Detail</h3>
            <input
              v-model="tableSearch"
              type="text"
              placeholder="Search items..."
              class="field-input !py-2 w-48 text-xs"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Product</th>
                  <th class="report-th">SKU</th>
                  <th class="report-th text-right">Qty</th>
                  <th class="report-th text-right">Avg Price</th>
                  <th class="report-th text-right">Discount</th>
                  <th class="report-th text-right">Net Rev</th>
                  <th class="report-th text-right">Cost</th>
                  <th class="report-th text-right">Profit</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(item, i) in filteredItemsSale"
                  :key="item.id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ item.product_name }}</td>
                  <td class="report-td text-slate-400 font-mono text-xs">
                    {{ item.sku || "—" }}
                  </td>
                  <td class="report-td text-right font-bold">{{ item.qty_sold }}</td>
                  <td class="report-td text-right text-slate-600">
                    {{ fmt(item.avg_price) }}
                  </td>
                  <td class="report-td text-right text-amber-500">
                    {{ fmt(item.total_discount) }}
                  </td>
                  <td class="report-td text-right font-bold text-[#229DE7]">
                    {{ fmt(item.net_revenue) }}
                  </td>
                  <td class="report-td text-right text-slate-500">
                    {{ fmt(item.total_cost) }}
                  </td>
                  <td
                    class="report-td text-right font-bold"
                    :class="item.gross_profit >= 0 ? 'text-emerald-600' : 'text-red-500'"
                  >
                    {{ fmt(item.gross_profit) }}
                  </td>
                </tr>
                <tr v-if="!filteredItemsSale.length">
                  <td colspan="9" class="text-center text-slate-400 py-10 text-sm">
                    No items found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Select a period and click Generate Report."
      />
    </template>

    <!-- ═══════════════════════════════════════
         3. SALES BY CUSTOMER
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'sales-customer'">
      <div v-if="data.salesCustomer" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Customers</p>
            <p class="stat-value text-slate-800">
              {{ data.salesCustomer.summary.total_customers }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Total</p>
            <p class="stat-value text-[#229DE7]">
              {{ fmt(data.salesCustomer.summary.gross_total) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">
              {{ fmt(data.salesCustomer.summary.total_paid) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Balance Due</p>
            <p class="stat-value text-red-500">
              {{ fmt(data.salesCustomer.summary.balance_due) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Sales by Customer</h3>
            <input
              v-model="tableSearch"
              type="text"
              placeholder="Search customer..."
              class="field-input !py-2 w-48 text-xs"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Customer</th>
                  <th class="report-th">Phone</th>
                  <th class="report-th text-right">Sales</th>
                  <th class="report-th text-right">Refunds</th>
                  <th class="report-th text-right">Gross</th>
                  <th class="report-th text-right">Discount</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Balance</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(c, i) in filteredSalesCustomer"
                  :key="c.customer_id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ c.customer_name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ c.phone || "—" }}</td>
                  <td class="report-td text-right">{{ c.total_sales }}</td>
                  <td class="report-td text-right text-red-400">
                    {{ c.refund_count || 0 }}
                  </td>
                  <td class="report-td text-right">{{ fmt(c.gross_total) }}</td>
                  <td class="report-td text-right text-amber-500">
                    {{ fmt(c.total_discount) }}
                  </td>
                  <td class="report-td text-right text-emerald-600">
                    {{ fmt(c.total_paid) }}
                  </td>
                  <td
                    class="report-td text-right font-bold"
                    :class="c.balance_due > 0 ? 'text-red-500' : 'text-slate-400'"
                  >
                    {{ fmt(c.balance_due) }}
                  </td>
                </tr>
                <tr v-if="!filteredSalesCustomer.length">
                  <td colspan="9" class="text-center text-slate-400 py-10 text-sm">
                    No data found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Select a period and click Generate Report."
      />
    </template>

    <!-- ═══════════════════════════════════════
         4. SALES BY BRANCH
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'sales-branch'">
      <div v-if="data.salesBranch" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Branches</p>
            <p class="stat-value text-slate-800">
              {{ data.salesBranch.summary.total_branches }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Total</p>
            <p class="stat-value text-[#229DE7]">
              {{ fmt(data.salesBranch.summary.gross_total) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">
              {{ fmt(data.salesBranch.summary.total_paid) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Balance Due</p>
            <p class="stat-value text-red-500">
              {{ fmt(data.salesBranch.summary.balance_due) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Sales by Branch</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Branch</th>
                  <th class="report-th">Location</th>
                  <th class="report-th text-right">Sales</th>
                  <th class="report-th text-right">Refunds</th>
                  <th class="report-th text-right">Gross</th>
                  <th class="report-th text-right">Discount</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Balance</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(b, i) in data.salesBranch.branches"
                  :key="b.branch_id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ b.branch_name }}</td>
                  <td class="report-td text-xs text-slate-500">
                    {{ b.location || "—" }}
                  </td>
                  <td class="report-td text-right">{{ b.total_sales }}</td>
                  <td class="report-td text-right text-red-400">
                    {{ b.refund_count || 0 }}
                  </td>
                  <td class="report-td text-right">{{ fmt(b.gross_total) }}</td>
                  <td class="report-td text-right text-amber-500">
                    {{ fmt(b.total_discount) }}
                  </td>
                  <td class="report-td text-right text-emerald-600">
                    {{ fmt(b.total_paid) }}
                  </td>
                  <td
                    class="report-td text-right font-bold"
                    :class="b.balance_due > 0 ? 'text-red-500' : 'text-slate-400'"
                  >
                    {{ fmt(b.balance_due) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Select a period and click Generate Report."
      />
    </template>

    <!-- ═══════════════════════════════════════
         5. ITEMS LEDGER
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'items-ledger'">
      <div v-if="data.ledger" class="space-y-5">
        <div class="grid grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">Total Stock In</p>
            <p class="stat-value text-emerald-600">{{ data.ledger.summary.total_in }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Stock Out</p>
            <p class="stat-value text-red-500">{{ data.ledger.summary.total_out }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value text-[#229DE7]">
              {{ fmt(data.ledger.summary.total_amount) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Items Ledger</h3>
            <input
              v-model="tableSearch"
              type="text"
              placeholder="Search product..."
              class="field-input !py-2 w-48 text-xs"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">Date</th>
                  <th class="report-th">Type</th>
                  <th class="report-th">Reference</th>
                  <th class="report-th">Product</th>
                  <th class="report-th">SKU</th>
                  <th class="report-th text-right">In</th>
                  <th class="report-th text-right">Out</th>
                  <th class="report-th text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(row, i) in filteredLedger"
                  :key="i"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-xs whitespace-nowrap">
                    {{ formatDate(row.date) }}
                  </td>
                  <td class="report-td">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="{
                        'bg-red-50 text-red-600 border-red-200': row.type === 'sale',
                        'bg-emerald-50 text-emerald-600 border-emerald-200':
                          row.type === 'purchase',
                        'bg-amber-50 text-amber-600 border-amber-200':
                          row.type === 'refund',
                      }"
                      >{{ row.type }}</span
                    >
                  </td>
                  <td class="report-td text-xs font-mono text-slate-400">
                    {{ row.reference_no || "—" }}
                  </td>
                  <td class="report-td font-semibold text-sm">{{ row.product_name }}</td>
                  <td class="report-td text-xs font-mono text-slate-400">
                    {{ row.sku || "—" }}
                  </td>
                  <td class="report-td text-right font-bold text-emerald-600">
                    {{ row.qty_in > 0 ? row.qty_in : "—" }}
                  </td>
                  <td class="report-td text-right font-bold text-red-500">
                    {{ row.qty_out > 0 ? row.qty_out : "—" }}
                  </td>
                  <td class="report-td text-right font-bold text-[#229DE7]">
                    {{ fmt(row.amount) }}
                  </td>
                </tr>
                <tr v-if="!filteredLedger.length">
                  <td colspan="8" class="text-center text-slate-400 py-10 text-sm">
                    No ledger entries found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Select a period and click Generate Report."
      />
    </template>

    <!-- ═══════════════════════════════════════
         6. CUSTOMER BALANCES
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'customer-balances'">
      <div
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5 mb-5 flex flex-wrap items-end gap-4"
      >
        <div>
          <label class="field-label">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Name, phone or email..."
            class="field-input w-64"
          />
        </div>
        <button @click="loadCustomerBalances" :disabled="loading" class="run-btn">
          <svg
            v-if="loading"
            class="animate-spin h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          <span>{{ loading ? "Loading..." : "Load Balances" }}</span>
        </button>
        <ExportButtons :exporting="exporting" @export="exportReport" />
      </div>
      <div v-if="data.customerBalances" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Customers</p>
            <p class="stat-value text-slate-800">
              {{ data.customerBalances.summary.total_customers }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Invoiced</p>
            <p class="stat-value text-[#229DE7]">
              {{ fmt(data.customerBalances.summary.total_invoiced) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">
              {{ fmt(data.customerBalances.summary.total_paid) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Outstanding</p>
            <p class="stat-value text-red-500">
              {{ fmt(data.customerBalances.summary.total_outstanding) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Customer Balances</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Customer</th>
                  <th class="report-th">Phone</th>
                  <th class="report-th">Email</th>
                  <th class="report-th text-right">Invoiced</th>
                  <th class="report-th text-right">Refunded</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Outstanding</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(c, i) in data.customerBalances.customers"
                  :key="c.id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ c.name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ c.phone || "—" }}</td>
                  <td class="report-td text-xs text-slate-500">{{ c.email || "—" }}</td>
                  <td class="report-td text-right">{{ fmt(c.total_invoiced) }}</td>
                  <td class="report-td text-right text-amber-500">
                    {{ fmt(c.total_refunded) }}
                  </td>
                  <td class="report-td text-right text-emerald-600 font-semibold">
                    {{ fmt(c.total_paid) }}
                  </td>
                  <td
                    class="report-td text-right font-bold"
                    :class="c.outstanding > 0 ? 'text-red-500' : 'text-slate-400'"
                  >
                    {{ fmt(c.outstanding) }}
                  </td>
                </tr>
                <tr v-if="!data.customerBalances.customers?.length">
                  <td colspan="8" class="text-center text-slate-400 py-10 text-sm">
                    No customers found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Click Load Balances to view customer outstanding amounts."
      />
    </template>

    <!-- ═══════════════════════════════════════
         7. SUPPLIER BALANCES
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'supplier-balances'">
      <div
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5 mb-5 flex flex-wrap items-end gap-4"
      >
        <div>
          <label class="field-label">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Name, phone or email..."
            class="field-input w-64"
          />
        </div>
        <button @click="loadSupplierBalances" :disabled="loading" class="run-btn">
          <svg
            v-if="loading"
            class="animate-spin h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          <span>{{ loading ? "Loading..." : "Load Balances" }}</span>
        </button>
        <ExportButtons :exporting="exporting" @export="exportReport" />
      </div>
      <div v-if="data.supplierBalances" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Suppliers</p>
            <p class="stat-value text-slate-800">
              {{ data.supplierBalances.summary.total_suppliers }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Purchased</p>
            <p class="stat-value text-[#229DE7]">
              {{ fmt(data.supplierBalances.summary.total_purchased) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">
              {{ fmt(data.supplierBalances.summary.total_paid) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Outstanding</p>
            <p class="stat-value text-red-500">
              {{ fmt(data.supplierBalances.summary.total_outstanding) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Supplier Balances</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Supplier</th>
                  <th class="report-th">Phone</th>
                  <th class="report-th">Email</th>
                  <th class="report-th text-right">Purchased</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Outstanding</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(s, i) in data.supplierBalances.suppliers"
                  :key="s.id"
                  class="hover:bg-[#F5FAFF]"
                >
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ s.name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ s.phone || "—" }}</td>
                  <td class="report-td text-xs text-slate-500">{{ s.email || "—" }}</td>
                  <td class="report-td text-right">{{ fmt(s.total_purchased) }}</td>
                  <td class="report-td text-right text-emerald-600 font-semibold">
                    {{ fmt(s.total_paid) }}
                  </td>
                  <td
                    class="report-td text-right font-bold"
                    :class="s.outstanding > 0 ? 'text-red-500' : 'text-slate-400'"
                  >
                    {{ fmt(s.outstanding) }}
                  </td>
                </tr>
                <tr v-if="!data.supplierBalances.suppliers?.length">
                  <td colspan="7" class="text-center text-slate-400 py-10 text-sm">
                    No suppliers found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Click Load Balances to view supplier outstanding amounts."
      />
    </template>

    <!-- ═══════════════════════════════════════
         8. GENERAL SALES REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'general-sales'">
      <div v-if="data.generalSales" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Total Sales</p>
            <p class="stat-value text-slate-800">{{ data.generalSales.summary.total_sales }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Total</p>
            <p class="stat-value text-[#229DE7]">{{ fmt(data.generalSales.summary.gross_total) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">{{ fmt(data.generalSales.summary.total_paid) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Balance Due</p>
            <p class="stat-value text-red-500">{{ fmt(data.generalSales.summary.balance_due) }}</p>
            <p class="text-[10px] text-slate-400 mt-1">
              Avg sale: {{ fmt(data.generalSales.summary.avg_sale_value) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">General Sales</h3>
            <input v-model="tableSearch" type="text" placeholder="Search reference, customer..." class="field-input !py-2 w-56 text-xs" />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Reference</th>
                  <th class="report-th">Date</th>
                  <th class="report-th">Branch</th>
                  <th class="report-th">Customer</th>
                  <th class="report-th">Cashier</th>
                  <th class="report-th text-right">Items</th>
                  <th class="report-th text-right">Total</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Balance</th>
                  <th class="report-th">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(s, i) in filteredGeneralSales" :key="s.id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-mono text-xs text-slate-500">{{ s.reference || "—" }}</td>
                  <td class="report-td text-xs whitespace-nowrap">{{ formatDate(s.date) }}</td>
                  <td class="report-td text-xs text-slate-500">{{ s.branch_name }}</td>
                  <td class="report-td font-semibold">{{ s.customer_name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ s.cashier_name }}</td>
                  <td class="report-td text-right">{{ s.items_qty }}</td>
                  <td class="report-td text-right font-bold">{{ fmt(s.total) }}</td>
                  <td class="report-td text-right text-emerald-600">{{ fmt(s.paid) }}</td>
                  <td class="report-td text-right font-bold" :class="s.balance > 0 ? 'text-red-500' : 'text-slate-400'">
                    {{ fmt(s.balance) }}
                  </td>
                  <td class="report-td">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                      :class="{
                        'bg-emerald-50 text-emerald-600 border-emerald-200': s.payment_status === 'paid',
                        'bg-amber-50 text-amber-600 border-amber-200': s.payment_status === 'partial',
                        'bg-red-50 text-red-600 border-red-200': s.payment_status === 'unpaid',
                      }"
                      >{{ s.payment_status }}</span
                    >
                  </td>
                </tr>
                <tr v-if="!filteredGeneralSales.length">
                  <td colspan="11" class="text-center text-slate-400 py-10 text-sm">No sales found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         9. REVENUE PER ITEM
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'revenue-item'">
      <div v-if="data.revenueItem" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">Products</p>
            <p class="stat-value text-slate-800">{{ data.revenueItem.summary.total_products }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Qty Sold</p>
            <p class="stat-value text-[#229DE7]">{{ data.revenueItem.summary.total_qty_sold }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Revenue</p>
            <p class="stat-value text-emerald-600">{{ fmt(data.revenueItem.summary.total_revenue) }}</p>
            <p class="text-[10px] text-slate-400 mt-1 truncate">
              Top: {{ data.revenueItem.summary.top_item || "—" }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Revenue by Item</h3>
            <input v-model="tableSearch" type="text" placeholder="Search product..." class="field-input !py-2 w-48 text-xs" />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Product</th>
                  <th class="report-th">Category</th>
                  <th class="report-th text-right">Qty Sold</th>
                  <th class="report-th text-right">Revenue</th>
                  <th class="report-th text-right">% of Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(item, i) in filteredRevenueItem" :key="item.product_id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ item.product_name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ item.category_name }}</td>
                  <td class="report-td text-right font-bold">{{ item.qty_sold }}</td>
                  <td class="report-td text-right font-bold text-[#229DE7]">{{ fmt(item.revenue) }}</td>
                  <td class="report-td text-right">
                    <div class="flex items-center gap-2 justify-end">
                      <div class="w-16 h-1.5 rounded-full bg-[#EEF5FB] overflow-hidden">
                        <div class="h-full bg-[#229DE7]" :style="{ width: item.revenue_percentage + '%' }"></div>
                      </div>
                      <span class="text-xs font-bold text-slate-600 w-10 text-right">{{ item.revenue_percentage }}%</span>
                    </div>
                  </td>
                </tr>
                <tr v-if="!filteredRevenueItem.length">
                  <td colspan="6" class="text-center text-slate-400 py-10 text-sm">No items found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         10. ITEMS PURCHASE REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'items-purchase'">
      <div v-if="data.itemsPurchase" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">Products</p>
            <p class="stat-value text-slate-800">{{ data.itemsPurchase.summary.total_products }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Qty Purchased</p>
            <p class="stat-value text-[#229DE7]">{{ data.itemsPurchase.summary.total_qty_purchased }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Cost</p>
            <p class="stat-value text-slate-800">{{ fmt(data.itemsPurchase.summary.total_cost) }}</p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Items Purchased</h3>
            <input v-model="tableSearch" type="text" placeholder="Search product..." class="field-input !py-2 w-48 text-xs" />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Product</th>
                  <th class="report-th">SKU</th>
                  <th class="report-th text-right">Purchases</th>
                  <th class="report-th text-right">Qty Purchased</th>
                  <th class="report-th text-right">Avg Unit Price</th>
                  <th class="report-th text-right">Total Cost</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(item, i) in filteredItemsPurchase" :key="item.product_id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ item.product_name }}</td>
                  <td class="report-td text-xs font-mono text-slate-400">{{ item.product_code || "—" }}</td>
                  <td class="report-td text-right">{{ item.purchase_count }}</td>
                  <td class="report-td text-right font-bold">{{ item.qty_purchased }}</td>
                  <td class="report-td text-right text-slate-600">{{ fmt(item.avg_unit_price) }}</td>
                  <td class="report-td text-right font-bold text-[#229DE7]">{{ fmt(item.total_cost) }}</td>
                </tr>
                <tr v-if="!filteredItemsPurchase.length">
                  <td colspan="7" class="text-center text-slate-400 py-10 text-sm">No purchases found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         11. PROFIT REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'profit'">
      <div v-if="data.profit" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">Gross Revenue</p>
            <p class="stat-value text-slate-800">{{ fmt(data.profit.summary.gross_revenue) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Cost of Goods</p>
            <p class="stat-value text-slate-600">{{ fmt(data.profit.summary.total_cogs) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Profit</p>
            <p class="stat-value" :class="data.profit.summary.gross_profit >= 0 ? 'text-emerald-600' : 'text-red-500'">
              {{ fmt(data.profit.summary.gross_profit) }}
            </p>
          </div>
          <div class="stat-card border-amber-100">
            <p class="stat-label">Expenses</p>
            <p class="stat-value text-amber-500">{{ fmt(data.profit.summary.total_expenses) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Net Profit</p>
            <p class="stat-value" :class="data.profit.summary.net_profit >= 0 ? 'text-emerald-600' : 'text-red-500'">
              {{ fmt(data.profit.summary.net_profit) }}
            </p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Net Margin</p>
            <p class="stat-value text-[#229DE7]">{{ data.profit.summary.net_margin }}%</p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Profit by Item</h3>
            <input v-model="tableSearch" type="text" placeholder="Search product..." class="field-input !py-2 w-48 text-xs" />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Product</th>
                  <th class="report-th text-right">Qty Sold</th>
                  <th class="report-th text-right">Revenue</th>
                  <th class="report-th text-right">Cost</th>
                  <th class="report-th text-right">Profit</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(item, i) in filteredProfit" :key="item.product_id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ item.product_name }}</td>
                  <td class="report-td text-right">{{ item.qty_sold }}</td>
                  <td class="report-td text-right">{{ fmt(item.revenue) }}</td>
                  <td class="report-td text-right text-slate-500">{{ fmt(item.cost) }}</td>
                  <td class="report-td text-right font-bold" :class="item.profit >= 0 ? 'text-emerald-600' : 'text-red-500'">
                    {{ fmt(item.profit) }}
                  </td>
                </tr>
                <tr v-if="!filteredProfit.length">
                  <td colspan="6" class="text-center text-slate-400 py-10 text-sm">No data found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         12. STAFF SALES REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'staff-sales'">
      <div v-if="data.staffSales" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">Staff</p>
            <p class="stat-value text-slate-800">{{ data.staffSales.summary.total_staff }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Gross Total</p>
            <p class="stat-value text-[#229DE7]">{{ fmt(data.staffSales.summary.gross_total) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">{{ fmt(data.staffSales.summary.total_paid) }}</p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Sales by Staff</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">#</th>
                  <th class="report-th">Staff</th>
                  <th class="report-th text-right">Sales</th>
                  <th class="report-th text-right">Gross Total</th>
                  <th class="report-th text-right">Discount</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Avg Sale</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(s, i) in data.staffSales.staff" :key="s.staff_id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-slate-400 font-bold text-xs">{{ i + 1 }}</td>
                  <td class="report-td font-semibold">{{ s.staff_name }}</td>
                  <td class="report-td text-right">{{ s.total_sales }}</td>
                  <td class="report-td text-right font-bold text-[#229DE7]">{{ fmt(s.gross_total) }}</td>
                  <td class="report-td text-right text-amber-500">{{ fmt(s.total_discount) }}</td>
                  <td class="report-td text-right text-emerald-600">{{ fmt(s.total_paid) }}</td>
                  <td class="report-td text-right text-slate-500">{{ fmt(s.avg_sale_value) }}</td>
                </tr>
                <tr v-if="!data.staffSales.staff?.length">
                  <td colspan="7" class="text-center text-slate-400 py-10 text-sm">No staff sales found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         13. GENERAL EXPENSES REPORT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'expenses'">
      <div v-if="data.expenses" class="space-y-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="stat-card">
            <p class="stat-label">Total Expenses</p>
            <p class="stat-value text-slate-800">{{ data.expenses.summary.total_expenses }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Amount</p>
            <p class="stat-value text-amber-500">{{ fmt(data.expenses.summary.total_amount) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Total Paid</p>
            <p class="stat-value text-emerald-600">{{ fmt(data.expenses.summary.total_paid) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Balance Due</p>
            <p class="stat-value text-red-500">{{ fmt(data.expenses.summary.total_balance) }}</p>
          </div>
        </div>

        <div v-if="data.expenses.by_category?.length" class="report-card">
          <div class="report-card-header">By Category</div>
          <div class="p-5 space-y-3">
            <div v-for="c in data.expenses.by_category" :key="c.category_name" class="flex items-center justify-between gap-2">
              <span class="text-sm font-semibold text-slate-600">{{ c.category_name }}</span>
              <div class="text-right">
                <p class="text-sm font-bold text-slate-900">{{ fmt(c.total) }}</p>
                <p class="text-[10px] text-slate-400">{{ c.count }} expense(s)</p>
              </div>
            </div>
          </div>
        </div>

        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Expenses</h3>
            <input v-model="tableSearch" type="text" placeholder="Search description..." class="field-input !py-2 w-48 text-xs" />
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">Date</th>
                  <th class="report-th">Category</th>
                  <th class="report-th">Branch</th>
                  <th class="report-th">Description</th>
                  <th class="report-th text-right">Amount</th>
                  <th class="report-th text-right">Paid</th>
                  <th class="report-th text-right">Balance</th>
                  <th class="report-th">Status</th>
                  <th class="report-th">Method</th>
                  <th class="report-th">Recorded By</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="e in filteredExpenses" :key="e.id" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-xs whitespace-nowrap">{{ formatDate(e.date) }}</td>
                  <td class="report-td text-xs text-slate-500">{{ e.category_name }}</td>
                  <td class="report-td text-xs text-slate-500">{{ e.branch_name }}</td>
                  <td class="report-td font-semibold">{{ e.description || "—" }}</td>
                  <td class="report-td text-right font-bold text-amber-600">{{ fmt(e.amount) }}</td>
                  <td class="report-td text-right font-semibold text-emerald-600">{{ fmt(e.amount_paid) }}</td>
                  <td class="report-td text-right font-semibold" :class="Number(e.balance) > 0 ? 'text-red-500' : 'text-slate-400'">{{ fmt(e.balance) }}</td>
                  <td class="report-td">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                      :class="{
                        'bg-emerald-50 text-emerald-600 border-emerald-200': e.payment_status === 'paid',
                        'bg-amber-50 text-amber-600 border-amber-200': e.payment_status === 'partially_paid',
                        'bg-red-50 text-red-600 border-red-200': e.payment_status === 'pending',
                      }"
                      >{{ (e.payment_status || 'pending').replace('_', ' ') }}</span
                    >
                  </td>
                  <td class="report-td text-xs capitalize text-slate-500">{{ e.payment_method || "—" }}</td>
                  <td class="report-td text-xs text-slate-500">{{ e.recorded_by }}</td>
                </tr>
                <tr v-if="!filteredExpenses.length">
                  <td colspan="10" class="text-center text-slate-400 py-10 text-sm">No expenses found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt v-else-if="!loading" message="Select a period and click Generate Report." />
    </template>

    <!-- ═══════════════════════════════════════
         14. ACCOUNT STATEMENT
    ════════════════════════════════════════ -->
    <template v-if="activeTab === 'account-statement'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm p-5 mb-5 flex flex-wrap items-end gap-4">
        <div>
          <label class="field-label">Party Type</label>
          <select v-model="filters.partyType" class="field-input w-36" @change="filters.partyId = ''">
            <option value="customer">Customer</option>
            <option value="supplier">Supplier</option>
          </select>
        </div>
        <div>
          <label class="field-label">{{ filters.partyType === "supplier" ? "Supplier" : "Customer" }}</label>
          <select v-model="filters.partyId" class="field-input w-56">
            <option value="">Select {{ filters.partyType === "supplier" ? "a supplier" : "a customer" }}...</option>
            <template v-if="filters.partyType === 'supplier'">
              <option v-for="p in suppliers" :key="p.id" :value="p.id">{{ p.name }}</option>
            </template>
            <template v-else>
              <!-- Customers are listed with an encrypted id (customer_id), not a plain id -->
              <option v-for="p in customers" :key="p.customer_id" :value="p.customer_id">{{ p.name }}</option>
            </template>
          </select>
        </div>
        <div>
          <label class="field-label">From (optional)</label>
          <input v-model="filters.statementFrom" type="date" class="field-input w-40" />
        </div>
        <div>
          <label class="field-label">To (optional)</label>
          <input v-model="filters.statementTo" type="date" class="field-input w-40" />
        </div>
        <button @click="loadAccountStatement" :disabled="loading || !filters.partyId" class="run-btn">
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          <span>{{ loading ? "Loading..." : "Run Statement" }}</span>
        </button>
        <ExportButtons v-if="filters.partyId" :exporting="exporting" @export="exportReport" />
      </div>

      <div v-if="data.statement?.party" class="space-y-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="stat-card">
            <p class="stat-label">{{ filters.partyType === "supplier" ? "Supplier" : "Customer" }}</p>
            <p class="stat-value text-slate-800 truncate">{{ data.statement.party.name }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Opening Balance</p>
            <p class="stat-value text-slate-600">{{ fmt(data.statement.opening_balance) }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-label">Closing Balance</p>
            <p class="stat-value" :class="data.statement.closing_balance > 0 ? 'text-red-500' : 'text-emerald-600'">
              {{ fmt(data.statement.closing_balance) }}
            </p>
          </div>
        </div>
        <div class="report-table-wrap">
          <div class="report-table-header">
            <h3 class="report-table-title">Transaction Ledger</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="report-table">
              <thead>
                <tr class="report-thead-tr">
                  <th class="report-th">Date</th>
                  <th class="report-th">Type</th>
                  <th class="report-th">Reference</th>
                  <th class="report-th text-right">Debit</th>
                  <th class="report-th text-right">Credit</th>
                  <th class="report-th text-right">Balance</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="(e, i) in data.statement.entries" :key="i" class="hover:bg-[#F5FAFF]">
                  <td class="report-td text-xs whitespace-nowrap">{{ formatDate(e.date) }}</td>
                  <td class="report-td">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                      :class="e.type === 'payment' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-[#EAF5FD] text-[#229DE7] border-[#B3DEFA]'"
                      >{{ e.type }}</span
                    >
                  </td>
                  <td class="report-td text-xs font-mono text-slate-400">{{ e.reference || "—" }}</td>
                  <td class="report-td text-right font-bold text-slate-800">{{ e.debit > 0 ? fmt(e.debit) : "—" }}</td>
                  <td class="report-td text-right font-bold text-emerald-600">{{ e.credit > 0 ? fmt(e.credit) : "—" }}</td>
                  <td class="report-td text-right font-bold" :class="e.balance > 0 ? 'text-red-500' : 'text-slate-400'">
                    {{ fmt(e.balance) }}
                  </td>
                </tr>
                <tr v-if="!data.statement.entries?.length">
                  <td colspan="6" class="text-center text-slate-400 py-10 text-sm">No transactions in this period.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <EmptyPrompt
        v-else-if="!loading"
        message="Choose a party type, select who to run the statement for, then click Run Statement."
      />
    </template>

    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-3 mt-2">
      <div
        v-for="n in 6"
        :key="n"
        class="h-14 bg-white border border-[#D9EBF7] animate-pulse rounded-xl"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, defineComponent, h } from "vue";
import Swal from "sweetalert2";
import ReportsService from "@/services/ReportService";
import BranchesService from "@/services/Branchesservice";
import CustomerService from "@/services/CustomerService";
import SupplierService from "@/services/SupplierService";
import ExpenseCategoryService from "@/services/ExpenseCategoryService";
import ReportExportService from "@/services/ReportExportService";

/* ─── EMPTY PROMPT COMPONENT ──────────── */
const EmptyPrompt = defineComponent({
  props: ["message"],
  setup(props) {
    return () =>
      h("div", { class: "py-20 text-center" }, [
        h(
          "div",
          {
            class:
              "w-16 h-16 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-4",
          },
          h(
            "svg",
            {
              class: "w-8 h-8 text-[#229DE7]",
              fill: "none",
              stroke: "currentColor",
              "stroke-width": "1.8",
              viewBox: "0 0 24 24",
            },
            h("path", {
              "stroke-linecap": "round",
              "stroke-linejoin": "round",
              d:
                "M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
            })
          )
        ),
        h("p", { class: "text-slate-500 text-sm font-semibold" }, props.message),
      ]);
  },
});

/* ─── EXPORT BUTTONS COMPONENT ───────────
   Two distinct, brand-colored buttons (Excel green / PDF red) rather than
   one generic download icon repeated twice — makes it scannable which is
   which at a glance, and each shows its own spinner + "Generating…" label
   while that specific format is being built server-side. */
const EXPORT_STYLES = {
  xlsx: {
    label: "Excel",
    genLabel: "Generating…",
    className: "export-btn export-btn--xlsx",
    icon: "M4 4h16v16H4V4zm0 5h16M9 9v11M4 14h5",
  },
  pdf: {
    label: "PDF",
    genLabel: "Generating…",
    className: "export-btn export-btn--pdf",
    icon:
      "M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1zm7 0v5h5M9 13h1.5a1.5 1.5 0 010 3H9v-3zm0 3v2m4-5h1.5v5H13v-5zm4 0h2m-2 2h1.5",
  },
};

const ExportButtons = defineComponent({
  props: { exporting: { type: String, default: null } },
  emits: ["export"],
  setup(props, { emit }) {
    const btn = (format) => {
      const s = EXPORT_STYLES[format];
      const isBusy = props.exporting === format;

      const chip = h(
        "span",
        { class: [`export-btn-chip export-btn-chip--${format}`, { "is-busy": isBusy }] },
        isBusy
          ? h(
              "svg",
              { class: "animate-spin h-4 w-4", fill: "none", viewBox: "0 0 24 24" },
              [
                h("circle", {
                  class: "opacity-30",
                  cx: "12",
                  cy: "12",
                  r: "10",
                  stroke: "currentColor",
                  "stroke-width": "4",
                }),
                h("path", {
                  class: "opacity-90",
                  fill: "currentColor",
                  d: "M4 12a8 8 0 018-8v8H4z",
                }),
              ]
            )
          : h(
              "svg",
              {
                class: "w-4 h-4",
                fill: "none",
                stroke: "currentColor",
                "stroke-width": "2",
                viewBox: "0 0 24 24",
              },
              h("path", { "stroke-linecap": "round", "stroke-linejoin": "round", d: s.icon })
            )
      );

      return h(
        "button",
        {
          class: [s.className, { "is-busy": isBusy }],
          disabled: !!props.exporting,
          title: `Export as ${s.label}`,
          onClick: () => emit("export", format),
        },
        [
          chip,
          h("span", { class: "export-btn-text" }, [
            h("span", { class: "export-btn-label" }, isBusy ? s.genLabel : s.label),
            h("span", { class: "export-btn-sub" }, isBusy ? "Please wait" : "Download"),
          ]),
        ]
      );
    };

    return () => h("div", { class: "export-btn-group" }, [btn("xlsx"), btn("pdf")]);
  },
});

/* ─── TABS ────────────────────────────── */
const tabs = [
  {
    key: "close-day",
    label: "Close Day",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
  },
  {
    key: "items-sale",
    label: "Items Sales",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
  },
  {
    key: "sales-customer",
    label: "By Customer",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
  },
  {
    key: "sales-branch",
    label: "By Branch",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
  },
  {
    key: "items-ledger",
    label: "Items Ledger",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
  },
  {
    key: "customer-balances",
    label: "Customer Balances",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
  },
  {
    key: "supplier-balances",
    label: "Supplier Balances",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>',
  },
  {
    key: "general-sales",
    label: "General Sales",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
  },
  {
    key: "revenue-item",
    label: "Revenue per Item",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
  },
  {
    key: "items-purchase",
    label: "Items Purchase",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>',
  },
  {
    key: "profit",
    label: "Profit Report",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  },
  {
    key: "staff-sales",
    label: "Staff Sales",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
  },
  {
    key: "expenses",
    label: "Expenses",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h1m4 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>',
  },
  {
    key: "account-statement",
    label: "Account Statement",
    icon:
      '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>',
  },
];

const presets = [
  { label: "Today", value: "today" },
  { label: "This Week", value: "this_week" },
  { label: "This Month", value: "this_month" },
  { label: "Custom", value: "custom" },
];

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const loading = ref(false);
// Which format is currently being exported ('xlsx' | 'pdf' | null) — drives
// the spinner on the matching ExportButtons button.
const exporting = ref(null);
const activeTab = ref("close-day");
const tableSearch = ref("");
const branches = ref([]);
const suppliers = ref([]);
const customers = ref([]);
const expenseCategories = ref([]);
const today = new Date().toISOString().split("T")[0];

const filters = reactive({
  date: today,
  closeDayFrom: today,
  closeDayTo: today,
  preset: "today",
  from: today,
  to: today,
  branch_id: "",
  search: "",
  payment_method: "",
  supplier_id: "",
  category_id: "",
  partyType: "customer",
  partyId: "",
  statementFrom: "",
  statementTo: "",
});

const data = reactive({
  closeDay: null,
  itemsSale: null,
  salesCustomer: null,
  salesBranch: null,
  ledger: null,
  customerBalances: null,
  supplierBalances: null,
  generalSales: null,
  revenueItem: null,
  itemsPurchase: null,
  profit: null,
  staffSales: null,
  expenses: null,
  statement: null,
});

/* ─── HELPERS ─────────────────────────── */
const fmt = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(val ?? 0);

const formatDate = (val) =>
  val
    ? new Date(val).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : "—";

const paymentDot = (key) =>
  ({ cash: "bg-emerald-500", mpesa: "bg-[#229DE7]", card: "bg-violet-500" }[key] ??
  "bg-slate-400");

const handleError = (err) =>
  Swal.fire({
    icon: "error",
    title: "Error",
    text: err?.response?.data?.message || "Something went wrong.",
    confirmButtonColor: "#229DE7",
  });

const switchTab = (key) => {
  activeTab.value = key;
  tableSearch.value = "";
};

/* ─── EXPORT (server-side, via queue) ────
   The report_type sent to the backend is just the tab key — they're kept
   1:1 with ReportExportController::REPORT_TYPES on purpose. Params mirror
   whatever the matching loader above sends, so the export always reflects
   exactly the filters currently on screen. */
const buildExportParams = () => {
  switch (activeTab.value) {
    case "close-day":
      return { from: filters.closeDayFrom, to: filters.closeDayTo };
    case "items-sale":
      return { ...rangeParams(), branch_id: filters.branch_id || null };
    case "sales-customer":
    case "sales-branch":
    case "items-ledger":
    case "profit":
      return rangeParams();
    case "customer-balances":
    case "supplier-balances":
      return { search: filters.search || null };
    case "general-sales":
      return {
        ...rangeParams(),
        branch_id: filters.branch_id || null,
        payment_method: filters.payment_method || null,
      };
    case "revenue-item":
    case "staff-sales":
      return { ...rangeParams(), branch_id: filters.branch_id || null };
    case "items-purchase":
      return { ...rangeParams(), supplier_id: filters.supplier_id || null };
    case "expenses":
      return { ...rangeParams(), category_id: filters.category_id || null };
    case "account-statement":
      return {
        party_type: filters.partyType,
        party_id: filters.partyId,
        from: filters.statementFrom || null,
        to: filters.statementTo || null,
      };
    default:
      return {};
  }
};

const POLL_INTERVAL_MS = 1500;
const POLL_MAX_ATTEMPTS = 40; // ~60s — generous for a queued job behind a cold worker

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const exportReport = async (format) => {
  if (exporting.value) return;

  if (activeTab.value === "account-statement" && !filters.partyId) {
    return;
  }

  exporting.value = format;

  const progress = Swal.fire({
    title: `Generating ${format === "xlsx" ? "Excel" : "PDF"} export…`,
    html: '<div class="export-progress-text">Queued — this usually takes a few seconds.</div>',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const created = await ReportExportService.create(activeTab.value, format, buildExportParams());
    const exportId = created?.data?.data?.id;
    if (!exportId) throw new Error("Export could not be queued.");

    let ready = false;
    let failedMsg = null;

    for (let attempt = 0; attempt < POLL_MAX_ATTEMPTS; attempt++) {
      await sleep(POLL_INTERVAL_MS);
      const statusRes = await ReportExportService.status(exportId);
      const status = statusRes?.data?.data;

      if (status?.status === "completed") {
        ready = true;
        break;
      }
      if (status?.status === "failed") {
        failedMsg = status.error || "Export generation failed.";
        break;
      }

      const el = document.querySelector(".export-progress-text");
      if (el) {
        el.textContent =
          status?.status === "processing"
            ? "Building your file…"
            : "Queued — waiting for a worker to pick it up…";
      }
    }

    if (failedMsg) throw new Error(failedMsg);
    if (!ready) throw new Error("Export is taking longer than expected. It may still complete — check back shortly.");

    const fileRes = await ReportExportService.download(exportId);
    const blob = new Blob([fileRes.data]);
    const url = window.URL.createObjectURL(blob);
    const ext = format === "xlsx" ? "xlsx" : "pdf";
    const a = document.createElement("a");
    a.href = url;
    a.download = `${activeTab.value}-report.${ext}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    Swal.close();
    Swal.fire({
      icon: "success",
      title: "Export ready",
      text: "Your download has started.",
      confirmButtonColor: "#229DE7",
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (e) {
    Swal.close();
    handleError(e?.response ? e : { response: { data: { message: e.message } } });
  } finally {
    exporting.value = null;
  }
};

/* ─── COMPUTED FILTERS ─────────────────── */
const filteredItemsSale = computed(() => {
  if (!data.itemsSale?.items) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.itemsSale.items.filter(
        (i) =>
          i.product_name.toLowerCase().includes(q) ||
          (i.sku || "").toLowerCase().includes(q)
      )
    : data.itemsSale.items;
});
const filteredSalesCustomer = computed(() => {
  if (!data.salesCustomer?.customers) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.salesCustomer.customers.filter(
        (c) =>
          c.customer_name.toLowerCase().includes(q) ||
          (c.phone || "").includes(tableSearch.value)
      )
    : data.salesCustomer.customers;
});
const filteredLedger = computed(() => {
  if (!data.ledger?.ledger) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.ledger.ledger.filter(
        (r) =>
          r.product_name.toLowerCase().includes(q) ||
          (r.sku || "").toLowerCase().includes(q)
      )
    : data.ledger.ledger;
});
const filteredGeneralSales = computed(() => {
  if (!data.generalSales?.sales) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.generalSales.sales.filter(
        (s) =>
          (s.reference || "").toLowerCase().includes(q) ||
          s.customer_name.toLowerCase().includes(q)
      )
    : data.generalSales.sales;
});
const filteredRevenueItem = computed(() => {
  if (!data.revenueItem?.items) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.revenueItem.items.filter((i) => i.product_name.toLowerCase().includes(q))
    : data.revenueItem.items;
});
const filteredItemsPurchase = computed(() => {
  if (!data.itemsPurchase?.items) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.itemsPurchase.items.filter(
        (i) =>
          i.product_name.toLowerCase().includes(q) ||
          (i.product_code || "").toLowerCase().includes(q)
      )
    : data.itemsPurchase.items;
});
const filteredProfit = computed(() => {
  if (!data.profit?.items) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.profit.items.filter((i) => i.product_name.toLowerCase().includes(q))
    : data.profit.items;
});
const filteredExpenses = computed(() => {
  if (!data.expenses?.expenses) return [];
  const q = tableSearch.value.toLowerCase();
  return q
    ? data.expenses.expenses.filter((e) => (e.description || "").toLowerCase().includes(q))
    : data.expenses.expenses;
});

/* ─── SHARED RUN ──────────────────────── */
const runReport = async () => {
  const map = {
    "close-day": loadCloseDay,
    "items-sale": loadItemsSale,
    "sales-customer": loadSalesByCustomer,
    "sales-branch": loadSalesByBranch,
    "items-ledger": loadLedger,
    "general-sales": loadGeneralSales,
    "revenue-item": loadRevenueItem,
    "items-purchase": loadItemsPurchase,
    profit: loadProfit,
    "staff-sales": loadStaffSales,
    expenses: loadExpenses,
  };
  await map[activeTab.value]?.();
};

/* ─── LOADERS ─────────────────────────── */
const rangeParams = () =>
  filters.preset === "custom"
    ? { preset: "custom", from: filters.from, to: filters.to }
    : { preset: filters.preset };

const loadCloseDay = async () => {
  loading.value = true;
  data.closeDay = null;
  try {
    const r = await ReportsService.closeDayReport(filters.closeDayFrom, filters.closeDayTo);
    data.closeDay = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadItemsSale = async () => {
  loading.value = true;
  data.itemsSale = null;
  try {
    const r = await ReportsService.itemsSaleReport({
      ...rangeParams(),
      branch_id: filters.branch_id || null,
    });
    data.itemsSale = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadSalesByCustomer = async () => {
  loading.value = true;
  data.salesCustomer = null;
  try {
    const r = await ReportsService.salesByCustomer(rangeParams());
    data.salesCustomer = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadSalesByBranch = async () => {
  loading.value = true;
  data.salesBranch = null;
  try {
    const r = await ReportsService.salesByBranch(rangeParams());
    data.salesBranch = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadLedger = async () => {
  loading.value = true;
  data.ledger = null;
  try {
    const r = await ReportsService.itemsLedger(rangeParams());
    data.ledger = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadCustomerBalances = async () => {
  loading.value = true;
  data.customerBalances = null;
  try {
    const r = await ReportsService.customerBalances(filters.search || null);
    data.customerBalances = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadSupplierBalances = async () => {
  loading.value = true;
  data.supplierBalances = null;
  try {
    const r = await ReportsService.supplierBalances(filters.search || null);
    data.supplierBalances = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadGeneralSales = async () => {
  loading.value = true;
  data.generalSales = null;
  try {
    const r = await ReportsService.generalSalesReport({
      ...rangeParams(),
      branch_id: filters.branch_id || null,
      payment_method: filters.payment_method || null,
    });
    data.generalSales = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadRevenueItem = async () => {
  loading.value = true;
  data.revenueItem = null;
  try {
    const r = await ReportsService.revenueByItem({
      ...rangeParams(),
      branch_id: filters.branch_id || null,
    });
    data.revenueItem = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadItemsPurchase = async () => {
  loading.value = true;
  data.itemsPurchase = null;
  try {
    const r = await ReportsService.itemsPurchaseReport({
      ...rangeParams(),
      supplier_id: filters.supplier_id || null,
    });
    data.itemsPurchase = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadProfit = async () => {
  loading.value = true;
  data.profit = null;
  try {
    const r = await ReportsService.profitReport(rangeParams());
    data.profit = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadStaffSales = async () => {
  loading.value = true;
  data.staffSales = null;
  try {
    const r = await ReportsService.staffSalesReport({
      ...rangeParams(),
      branch_id: filters.branch_id || null,
    });
    data.staffSales = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadExpenses = async () => {
  loading.value = true;
  data.expenses = null;
  try {
    const r = await ReportsService.expensesReport({
      ...rangeParams(),
      category_id: filters.category_id || null,
    });
    data.expenses = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};
const loadAccountStatement = async () => {
  if (!filters.partyId) return;
  loading.value = true;
  data.statement = null;
  try {
    const r = await ReportsService.accountStatement({
      party_type: filters.partyType,
      party_id: filters.partyId,
      from: filters.statementFrom || null,
      to: filters.statementTo || null,
    });
    data.statement = r?.data?.data ?? r?.data;
  } catch (e) {
    handleError(e);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    const [branchesRes, suppliersRes, customersRes, categoriesRes] = await Promise.allSettled([
      BranchesService.getAllBranches(),
      SupplierService.getAll(),
      CustomerService.getAll(),
      ExpenseCategoryService.getAll(),
    ]);
    if (branchesRes.status === "fulfilled") {
      branches.value = branchesRes.value?.data?.data ?? branchesRes.value?.data ?? [];
    }
    if (suppliersRes.status === "fulfilled") {
      suppliers.value = suppliersRes.value?.data?.data ?? suppliersRes.value?.data ?? [];
    }
    if (customersRes.status === "fulfilled") {
      customers.value = customersRes.value?.data?.data ?? customersRes.value?.data ?? [];
    }
    if (categoriesRes.status === "fulfilled") {
      expenseCategories.value = categoriesRes.value?.data?.data ?? categoriesRes.value?.data ?? [];
    }
  } catch {
    /* non-critical */
  } finally {
    setTimeout(() => (pageLoading.value = false), 400);
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.field-input {
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
  appearance: none;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}

.run-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: #229de7;
  color: white;
  font-size: 13px;
  font-weight: 700;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(34, 157, 231, 0.25);
}
.run-btn:hover:not(:disabled) {
  background: #1682c4;
}
.run-btn:active:not(:disabled) {
  transform: scale(0.98);
}
.run-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.export-btn-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.export-btn {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 7px 14px 7px 7px;
  background: white;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.15s;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.export-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
.export-btn-chip {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 9px;
  flex-shrink: 0;
  color: white;
  transition: transform 0.15s;
}
.export-btn-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.25;
}
.export-btn-label {
  font-size: 12.5px;
  font-weight: 800;
  color: #0f172a;
}
.export-btn-sub {
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
}

/* Excel — brand green */
.export-btn--xlsx .export-btn-chip {
  background: linear-gradient(135deg, #21a366, #16744a);
}
.export-btn--xlsx:hover:not(:disabled) {
  border-color: #86efac;
  box-shadow: 0 4px 10px rgba(33, 163, 102, 0.18);
}
.export-btn--xlsx:hover:not(:disabled) .export-btn-chip {
  transform: scale(1.06);
}
.export-btn--xlsx.is-busy {
  border-color: #86efac;
  background: #f0fdf4;
}

/* PDF — brand red */
.export-btn--pdf .export-btn-chip {
  background: linear-gradient(135deg, #ef4444, #b91c1c);
}
.export-btn--pdf:hover:not(:disabled) {
  border-color: #fca5a5;
  box-shadow: 0 4px 10px rgba(220, 38, 38, 0.18);
}
.export-btn--pdf:hover:not(:disabled) .export-btn-chip {
  transform: scale(1.06);
}
.export-btn--pdf.is-busy {
  border-color: #fca5a5;
  background: #fef2f2;
}

.stat-card {
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.stat-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 4px;
}
.stat-value {
  font-size: 20px;
  font-weight: 800;
}

.report-card {
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}
.report-card-header {
  padding: 14px 20px;
  border-bottom: 1px solid #eef5fb;
  font-size: 11px;
  font-weight: 800;
  color: #1682c4;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
}

.report-table-wrap {
  background: white;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}
.report-table-header {
  padding: 16px 20px;
  border-bottom: 1px solid #eef5fb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.report-table-title {
  font-size: 12px;
  font-weight: 800;
  color: #1682c4;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.report-table {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
.report-thead-tr {
  background: #eaf5fd;
  border-bottom: 1px solid #b3defa;
}
.report-th {
  padding: 12px 20px;
  font-size: 10.5px;
  font-weight: 700;
  color: #1682c4;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
}
.report-td {
  padding: 14px 20px;
  font-size: 13px;
  color: #334155;
}

.scrollbar-hide {
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
