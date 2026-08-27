<template>
  <div class="min-h-screen bg-[#F0F6FB] font-sans text-slate-900">
    <!-- TOP BAR -->
    <div class="px-6 pt-6 pb-0">
      <button
        @click="router.back()"
        class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#229DE7] transition mb-5 group"
      >
        <svg
          class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Products
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="px-6 space-y-4">
      <div class="h-40 bg-white rounded-2xl animate-pulse border border-[#D9EBF7]"></div>
      <div class="h-64 bg-white rounded-2xl animate-pulse border border-[#D9EBF7]"></div>
    </div>

    <!-- NOT FOUND -->
    <div v-else-if="!product" class="px-6 py-20 text-center">
      <div
        class="w-16 h-16 rounded-full bg-red-50 border border-red-200 flex items-center justify-center mx-auto mb-4"
      >
        <svg
          class="w-8 h-8 text-red-400"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
          />
        </svg>
      </div>
      <h3 class="font-bold text-base text-slate-900">Product not found</h3>
      <p class="text-slate-500 text-sm mt-1">
        This product may have been deleted or doesn't exist.
      </p>
    </div>

    <!-- CONTENT -->
    <div v-else class="px-6 pb-10 space-y-5">
      <!-- HERO CARD -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
        ></div>
        <div class="p-6">
          <div class="flex flex-col md:flex-row md:items-start gap-5">
            <!-- Avatar -->
            <div
              class="w-20 h-20 rounded-2xl bg-[#EAF5FD] border-2 border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-3xl flex-shrink-0 shadow-inner"
            >
              {{ product.name?.charAt(0).toUpperCase() ?? "?" }}
            </div>

            <!-- Main Info -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 truncate">
                  {{ product.name }}
                </h1>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] self-start"
                >
                  {{ product.category.name }}
                </span>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border self-start"
                  :class="stockBadge(product.current_stock).cls"
                >
                  {{ stockBadge(product.current_stock).label }}
                </span>
              </div>
              <div class="font-mono text-xs text-slate-400 mb-3">
                {{ product.product_code }}
              </div>

              <!-- Metrics row -->
              <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
                  <div
                    class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                  >
                    Selling Price
                  </div>
                  <div class="font-extrabold text-slate-900 text-base">
                    KES {{ Number(product.selling_price).toLocaleString() }}
                  </div>
                </div>
                <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
                  <div
                    class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                  >
                    Buying Price
                  </div>
                  <div class="font-extrabold text-slate-900 text-base">
                    KES {{ Number(product.buying_price).toLocaleString() }}
                  </div>
                </div>
                <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
                  <div
                    class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                  >
                    Wholesale Price
                  </div>
                  <div class="font-extrabold text-slate-900 text-base">
                    KES {{ Number(product.wholesale_price).toLocaleString() }}
                  </div>
                </div>
                <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
                  <div
                    class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                  >
                    Margin
                  </div>
                  <div class="font-extrabold text-emerald-600 text-base">
                    {{ margin(product) }}%
                  </div>
                </div>
                <div class="bg-[#F5FAFF] rounded-xl p-3 border border-[#D9EBF7]">
                  <div
                    class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5"
                  >
                    Total Stock
                  </div>
                  <div class="font-extrabold text-slate-900 text-base">
                    {{ product.current_stock }} units
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 flex-shrink-0">
              <button
                @click="editProduct"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-[#D9EBF7] rounded-xl shadow-sm hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Edit
              </button>
              <button
                @click="deleteProduct"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-red-600 bg-white border border-red-200 rounded-xl shadow-sm hover:bg-red-50 transition"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                Delete
              </button>
            </div>
          </div>

          <!-- Extra meta row -->
          <div
            class="mt-5 pt-4 border-t border-[#EEF5FB] grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs"
          >
            <div>
              <span class="text-slate-400 font-semibold">Brand</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{ product?.brand?.name ?? "—" }}
              </div>
            </div>
            <div>
              <span class="text-slate-400 font-semibold">Unit</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{ product?.unit?.name ?? "—" }}
              </div>
            </div>
            <div>
              <span class="text-slate-400 font-semibold">Reorder Level</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{ product.reorder_level ?? "—" }} units
              </div>
            </div>
            <div>
              <span class="text-slate-400 font-semibold">Tax Type</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{
                  product?.tax_type === "non_vat"
                    ? "Non Vat"
                    : product?.tax_type === "vat"
                    ? "VAT"
                    : "—"
                }}
              </div>
            </div>

            <div>
              <span class="text-slate-400 font-semibold">For Sale</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{
                  product?.for_sale === 1 ? "Yes" : product?.for_sale === 0 ? "No" : "—"
                }}
              </div>
            </div>

            <div>
              <span class="text-slate-400 font-semibold">For Purchase</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{
                  product?.for_purchase === 1
                    ? "Yes"
                    : product?.for_purchase === 0
                    ? "No"
                    : "—"
                }}
              </div>
            </div>
            <div>
              <span class="text-slate-400 font-semibold">Color</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{ product.color ?? "—" }}
              </div>
            </div>
            <div>
              <span class="text-slate-400 font-semibold">Added On</span>
              <div class="font-bold text-slate-700 mt-0.5">
                {{ formatDate(product.created_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABS -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <!-- Tab Nav -->
        <div class="flex border-b border-[#EEF5FB] bg-[#F5FAFF] overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="flex items-center gap-2 px-5 py-3.5 text-sm font-bold whitespace-nowrap transition border-b-2 -mb-px"
            :class="
              activeTab === tab.key
                ? 'border-[#229DE7] text-[#229DE7] bg-white'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-white'
            "
          >
            <component :is="tab.icon" class="w-4 h-4" />
            {{ tab.label }}
          </button>
        </div>

        <!-- TAB: STOCK PER BRANCH -->
        <div v-if="activeTab === 'stock'" class="p-6">
          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"
          >
            <div>
              <h2 class="font-extrabold text-base text-slate-900">Stock by Branch</h2>
              <p class="text-slate-400 text-xs mt-0.5">
                Current inventory levels across all locations
              </p>
            </div>
            <button
              @click="showAdjustModal = true"
              class="flex items-center gap-2 px-4 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 5v14M5 12h14"
                />
              </svg>
              Adjust Stock
            </button>
          </div>

          <div v-if="stockLoading" class="space-y-3">
            <div
              v-for="n in 4"
              :key="n"
              class="h-16 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div
            v-else-if="branchStock.length === 0"
            class="py-12 text-center text-slate-400 text-sm"
          >
            No branch stock data available.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="bs in branchStock"
              :key="bs.branch_id"
              class="flex items-center gap-4 p-4 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl hover:border-[#B3DEFA] transition group"
            >
              <!-- Branch icon -->
              <div
                class="w-10 h-10 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0"
              >
                <svg
                  class="w-5 h-5"
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

              <!-- Branch info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                  <span class="font-bold text-sm text-slate-900 truncate">{{
                    bs.branch_name
                  }}</span>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border flex-shrink-0"
                    :class="stockBadge(bs.quantity).cls"
                  >
                    {{ stockBadge(bs.quantity).label }}
                  </span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :style="stockBarStyle(bs.quantity)"
                    ></div>
                  </div>
                  <span class="text-xs font-extrabold text-slate-600 w-14 text-right"
                    >{{ bs.quantity }} units</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Totals footer -->
          <div
            class="mt-5 pt-4 border-t border-[#EEF5FB] flex justify-between items-center text-sm"
          >
            <span class="text-slate-400 font-semibold">Total across all branches</span>
            <span class="font-extrabold text-slate-900 text-base"
              >{{ branchStock.reduce((s, b) => s + b.quantity, 0) }} units</span
            >
          </div>
        </div>

        <!-- TAB: SALES HISTORY -->
        <div v-if="activeTab === 'sales'" class="p-6">
          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"
          >
            <div>
              <h2 class="font-extrabold text-base text-slate-900">Sales History</h2>
              <p class="text-slate-400 text-xs mt-0.5">
                All sales transactions for this product
              </p>
            </div>
            <div class="flex items-center gap-2">
              <input
                v-model="salesDateFrom"
                type="date"
                class="px-3 py-2 bg-white border border-[#D9EBF7] rounded-xl text-xs text-slate-700 outline-none focus:border-[#229DE7] transition"
              />
              <span class="text-slate-400 text-xs font-semibold">to</span>
              <input
                v-model="salesDateTo"
                type="date"
                class="px-3 py-2 bg-white border border-[#D9EBF7] rounded-xl text-xs text-slate-700 outline-none focus:border-[#229DE7] transition"
              />
            </div>
          </div>

          <!-- Summary cards -->
          <div class="grid grid-cols-3 gap-3 mb-5">
            <div class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-3 text-center">
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-[#1682C4] mb-1"
              >
                Total Sales
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                {{ salesSummary.total_transactions }}
              </div>
            </div>
            <div
              class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center"
            >
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 mb-1"
              >
                Units Sold
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                {{ salesSummary.total_units }}
              </div>
            </div>
            <div class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-3 text-center">
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-[#1682C4] mb-1"
              >
                Revenue
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                KES {{ Number(salesSummary.total_revenue).toLocaleString() }}
              </div>
            </div>
          </div>

          <div v-if="salesLoading" class="space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-12 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div
            v-else-if="sales.length === 0"
            class="py-12 text-center text-slate-400 text-sm"
          >
            No sales recorded for this product yet.
          </div>

          <div v-else class="overflow-x-auto rounded-xl border border-[#D9EBF7]">
            <table class="w-full text-left">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-4 py-3">Date</th>
                  <th class="px-4 py-3">Receipt #</th>
                  <th class="px-4 py-3">Branch</th>
                  <th class="px-4 py-3">Qty</th>
                  <!-- <th class="px-4 py-3">Unit Price</th> -->
                  <th class="px-4 py-3 text-right">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="sale in sales"
                  :key="sale.id"
                  class="hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-4 py-3 text-xs font-semibold text-slate-600">
                    {{ formatDate(sale.created_at) }}
                  </td>
                  <td class="px-4 py-3">
                    <span class="font-mono text-xs text-[#229DE7] font-bold">{{
                      sale.reference
                    }}</span>
                  </td>
                  <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    {{ sale.branch.name }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                    >
                      × {{ sale.items_count }}
                    </span>
                  </td>
                  <!-- <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    KES {{ Number(sale.unit_price).toLocaleString() }}
                  </td> -->
                  <td class="px-4 py-3 text-right font-extrabold text-sm text-slate-900">
                    KES {{ Number(sale.total).toLocaleString() }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Sales Pagination -->
          <div v-if="salesTotalPages > 1" class="mt-4 flex items-center justify-between">
            <span class="text-xs text-slate-400 font-semibold"
              >Page {{ salesPage }} of {{ salesTotalPages }}</span
            >
            <div class="flex gap-2">
              <button
                :disabled="salesPage === 1"
                @click="
                  salesPage--;
                  fetchSales();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-xs font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                ← Prev
              </button>
              <button
                :disabled="salesPage === salesTotalPages"
                @click="
                  salesPage++;
                  fetchSales();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-xs font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                Next →
              </button>
            </div>
          </div>
        </div>

        <!-- TAB: PURCHASE HISTORY -->
        <div v-if="activeTab === 'purchases'" class="p-6">
          <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"
          >
            <div>
              <h2 class="font-extrabold text-base text-slate-900">Purchase History</h2>
              <p class="text-slate-400 text-xs mt-0.5">
                All stock-in / purchase orders for this product
              </p>
            </div>
            <div class="flex items-center gap-2">
              <input
                v-model="purchaseDateFrom"
                type="date"
                class="px-3 py-2 bg-white border border-[#D9EBF7] rounded-xl text-xs text-slate-700 outline-none focus:border-[#229DE7] transition"
              />
              <span class="text-slate-400 text-xs font-semibold">to</span>
              <input
                v-model="purchaseDateTo"
                type="date"
                class="px-3 py-2 bg-white border border-[#D9EBF7] rounded-xl text-xs text-slate-700 outline-none focus:border-[#229DE7] transition"
              />
            </div>
          </div>

          <!-- Summary cards -->
          <div class="grid grid-cols-3 gap-3 mb-5">
            <div class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-3 text-center">
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-[#1682C4] mb-1"
              >
                Orders
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                {{ purchaseSummary.total_orders }}
              </div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-amber-600 mb-1"
              >
                Units Received
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                {{ purchaseSummary.total_units }}
              </div>
            </div>
            <div class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl p-3 text-center">
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-[#1682C4] mb-1"
              >
                Total Spent
              </div>
              <div class="font-extrabold text-slate-900 text-lg">
                KES {{ Number(purchaseSummary.total_cost).toLocaleString() }}
              </div>
            </div>
          </div>

          <div v-if="purchasesLoading" class="space-y-3">
            <div
              v-for="n in 5"
              :key="n"
              class="h-12 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div
            v-else-if="purchases.length === 0"
            class="py-12 text-center text-slate-400 text-sm"
          >
            No purchase records found for this product.
          </div>

          <div v-else class="overflow-x-auto rounded-xl border border-[#D9EBF7]">
            <table class="w-full text-left">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-4 py-3">Date</th>
                  <!-- <th class="px-4 py-3">LPO / Ref #</th>
                  <th class="px-4 py-3">Supplier</th> -->
                  <th class="px-4 py-3">Branch</th>
                  <th class="px-4 py-3">Qty</th>
                  <th class="px-4 py-3">Type</th>
                  <th class="px-4 py-3">Action</th>
                  <!-- <th class="px-4 py-3 text-right">Total</th>
                  <th class="px-4 py-3 text-center">Status</th> -->
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="po in purchases"
                  :key="po.id"
                  class="hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-4 py-3 text-xs font-semibold text-slate-600">
                    {{ formatDate(po.created_at) }}
                  </td>
                  <!-- <td class="px-4 py-3">
                    <span class="font-mono text-xs text-[#229DE7] font-bold">{{
                      po.reference_number
                    }}</span>
                  </td> -->
                  <!-- <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    {{ po.supplier_name }}
                  </td> -->
                  <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    {{ po.branch_name }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200"
                    >
                      {{ po.quantity }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    {{ formatType(po.type) }}
                  </td>

                  <td class="px-4 py-3 text-xs font-semibold">
                    <span
                      class="px-2 py-1 rounded-md border text-xs font-bold"
                      :class="actionClass(po.action)"
                    >
                      {{ formatAction(po.action) }}
                    </span>
                  </td>
                  <!-- <td class="px-4 py-3 text-xs font-semibold text-slate-700">
                    KES {{ Number(po.unit_cost).toLocaleString() }}
                  </td>
                  <td class="px-4 py-3 text-right font-extrabold text-sm text-slate-900">
                    KES {{ Number(po.total_cost).toLocaleString() }}
                  </td> -->
                  <!-- <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border"
                      :class="purchaseStatusBadge(po.status)"
                    >
                      {{ po.status }}
                    </span>
                  </td> -->
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Purchases Pagination -->
          <div
            v-if="purchasesTotalPages > 1"
            class="mt-4 flex items-center justify-between"
          >
            <span class="text-xs text-slate-400 font-semibold"
              >Page {{ purchasesPage }} of {{ purchasesTotalPages }}</span
            >
            <div class="flex gap-2">
              <button
                :disabled="purchasesPage === 1"
                @click="
                  purchasesPage--;
                  fetchPurchases();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-xs font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                ← Prev
              </button>
              <button
                :disabled="purchasesPage === purchasesTotalPages"
                @click="
                  purchasesPage++;
                  fetchPurchases();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-xs font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STOCK ADJUST MODAL -->
    <transition name="modal-fade">
      <div
        v-if="showAdjustModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
        @click.self="showAdjustModal = false"
      >
        <div
          class="bg-white rounded-2xl shadow-2xl border border-[#D9EBF7] w-full max-w-md overflow-hidden"
        >
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] to-[#5BB8F5]"></div>
          <div class="p-6">
            <div class="flex items-center justify-between mb-5">
              <h3 class="font-extrabold text-base text-slate-900">Adjust Stock</h3>
              <button
                @click="showAdjustModal = false"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block"
                  >Branch</label
                >
                <select
                  v-model="adjustForm.branch_id"
                  class="w-full px-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] transition"
                >
                  <option value="">Select branch</option>
                  <option
                    v-for="bs in branchStock"
                    :key="bs.branch_id"
                    :value="bs.branch_id"
                  >
                    {{ bs.branch_name }} ({{ bs.quantity }} units)
                  </option>
                </select>
              </div>

              <div>
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block"
                  >Adjustment Type</label
                >
                <div class="grid grid-cols-3 gap-2">
                  <button
                    v-for="t in ['add', 'remove', 'set']"
                    :key="t"
                    @click="adjustForm.action = t"
                    class="py-2 rounded-xl text-xs font-bold border capitalize transition"
                    :class="
                      adjustForm.action === t
                        ? 'bg-[#229DE7] border-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
                        : 'bg-white border-[#D9EBF7] text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7]'
                    "
                  >
                    {{ t === "add" ? "+ Add" : t === "remove" ? "− Remove" : "= Set" }}
                  </button>
                </div>
              </div>

              <div>
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block"
                  >Quantity</label
                >
                <input
                  v-model.number="adjustForm.quantity"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="Enter quantity (decimals allowed, e.g. 10.2)"
                  class="w-full px-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] transition"
                />
              </div>

              <div>
                <label
                  class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 block"
                  >Reason / Note</label
                >
                <textarea
                  v-model="adjustForm.reason"
                  rows="2"
                  placeholder="e.g. Received new shipment, damaged goods, stock count..."
                  class="w-full px-4 py-2.5 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] transition resize-none"
                ></textarea>
              </div>
            </div>

            <div class="flex gap-3 mt-6">
              <button
                @click="showAdjustModal = false"
                class="flex-1 py-2.5 border border-[#D9EBF7] rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition"
              >
                Cancel
              </button>
              <button
                @click="submitAdjust"
                :disabled="adjusting"
                class="flex-1 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition disabled:opacity-50"
              >
                {{ adjusting ? "Saving..." : "Apply Adjustment" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, h } from "vue";
import { useRouter, useRoute } from "vue-router";
import ProductService from "@/services/ProductService";
import StockService from "@/services/stockService";
import SalesService from "@/services/SalesService";
import PurchaseService from "@/services/purchaseService";
import Swal from "sweetalert2";
const router = useRouter();
const route = useRoute();
const productId = computed(() => route.params.id);

/* ─── PRODUCT ──────────────────────────── */
const product = ref(null);
const loading = ref(true);
const formatAction = (action) => {
  if (action === "add") return "Add";
  if (action === "remove") return "Remove";
  if (action === "set") return "Set";
  return action || "—";
};

const formatType = (type) => {
  if (!type) return "—";
  return type.charAt(0).toUpperCase() + type.slice(1);
};
const actionClass = (action) => {
  if (action === "add") return "bg-green-50 text-green-600 border-green-200";
  if (action === "remove") return "bg-red-50 text-red-600 border-red-200";
  if (action === "set") return "bg-orange-50 text-orange-600 border-orange-200";
  return "bg-slate-50 text-slate-600 border-slate-200";
};
const fetchProduct = async () => {
  loading.value = true;
  try {
    const res = await ProductService.getById(productId.value);
    product.value = res.data;
  } catch {
    product.value = null;
  } finally {
    loading.value = false;
  }
};

/* ─── TABS ─────────────────────────────── */
const activeTab = ref("stock");

// Icon components (inline SVG via render functions)
const StockIcon = {
  render: () =>
    h(
      "svg",
      { fill: "none", stroke: "currentColor", "stroke-width": "2", viewBox: "0 0 24 24" },
      [
        h("path", {
          "stroke-linecap": "round",
          "stroke-linejoin": "round",
          d: "M3 10h18M3 14h18M10 3v18",
        }),
      ]
    ),
};
const SalesIcon = {
  render: () =>
    h(
      "svg",
      { fill: "none", stroke: "currentColor", "stroke-width": "2", viewBox: "0 0 24 24" },
      [
        h("path", {
          "stroke-linecap": "round",
          "stroke-linejoin": "round",
          d: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
        }),
      ]
    ),
};
const PurchaseIcon = {
  render: () =>
    h(
      "svg",
      { fill: "none", stroke: "currentColor", "stroke-width": "2", viewBox: "0 0 24 24" },
      [
        h("path", {
          "stroke-linecap": "round",
          "stroke-linejoin": "round",
          d: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
        }),
      ]
    ),
};

const tabs = [
  { key: "stock", label: "Stock per Branch", icon: StockIcon },
  { key: "sales", label: "Sales History", icon: SalesIcon },
  { key: "purchases", label: "Stock History", icon: PurchaseIcon },
];

/* ─── BRANCH STOCK ─────────────────────── */
const branchStock = ref([]);
const stockLoading = ref(false);

const fetchBranchStock = async () => {
  stockLoading.value = true;
  try {
    const res = await StockService.getByProduct(productId.value);
    branchStock.value = res.data || [];
  } catch {
    branchStock.value = [];
  } finally {
    stockLoading.value = false;
  }
};

/* ─── STOCK ADJUST ─────────────────────── */
const showAdjustModal = ref(false);
const adjusting = ref(false);
const adjustForm = ref({ branch_id: "", action: "add", quantity: null, reason: "" });

const submitAdjust = async () => {
  if (!adjustForm.value.branch_id || !adjustForm.value.quantity) return;

  const selectedBranch = branchStock.value.find(
    (b) => b.branch_id === adjustForm.value.branch_id
  );

  const available = selectedBranch ? Number(selectedBranch.quantity) : 0;

  if (adjustForm.value.action === "remove" && adjustForm.value.quantity > available) {
    Swal.fire({
      icon: "error",
      title: "Stock Limit Exceeded",
      html: `
    <div style="font-size:14px">
      You cannot remove more than <b>${available}</b> units available.
    </div>
  `,
      confirmButtonText: "OK",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  adjusting.value = true;

  try {
    await StockService.adjust(productId.value, adjustForm.value);

    showAdjustModal.value = false;

    adjustForm.value = {
      branch_id: "",
      action: "add",
      quantity: null,
      reason: "",
    };

    fetchBranchStock();
    fetchProduct();
  } catch (e) {
    console.error(e);
    alert("Failed to adjust stock. Please try again.");
  } finally {
    adjusting.value = false;
  }
};
/* ─── SALES ────────────────────────────── */
const sales = ref([]);
const salesLoading = ref(false);
const salesPage = ref(1);
const salesTotalPages = ref(1);
const salesDateFrom = ref("");
const salesDateTo = ref("");
const salesSummary = ref({ total_transactions: 0, total_units: 0, total_revenue: 0 });

const fetchSales = async () => {
  salesLoading.value = true;

  try {
    const res = await SalesService.getByProduct(productId.value, {
      page: salesPage.value,
      per_page: 10,
      date_from: salesDateFrom.value,
      date_to: salesDateTo.value,
    });

    sales.value = res.data.sales || [];

    salesSummary.value = res.data.salesSummary || {
      total_transactions: 0,
      total_units: 0,
      total_revenue: 0,
    };

    salesTotalPages.value = res.data.last_page || 1;
  } catch (e) {
    sales.value = [];
    salesSummary.value = {
      total_transactions: 0,
      total_units: 0,
      total_revenue: 0,
    };
  } finally {
    salesLoading.value = false;
  }
};

watch([salesDateFrom, salesDateTo], () => {
  salesPage.value = 1;
  fetchSales();
});

/* ─── PURCHASES ────────────────────────── */
const purchases = ref([]);
const purchasesLoading = ref(false);
const purchasesPage = ref(1);
const purchasesTotalPages = ref(1);
const purchaseDateFrom = ref("");
const purchaseDateTo = ref("");
const purchaseSummary = ref({ total_orders: 0, total_units: 0, total_cost: 0 });

const fetchPurchases = async () => {
  purchasesLoading.value = true;
  try {
    const res = await PurchaseService.getByProduct(productId.value, {
      page: purchasesPage.value,
      per_page: 10,
      date_from: purchaseDateFrom.value,
      date_to: purchaseDateTo.value,
    });
    purchases.value = res.data || [];
    purchasesTotalPages.value = res.data.last_page || 1;
    purchaseSummary.value = res.data.summary || {
      total_orders: 0,
      total_units: 0,
      total_cost: 0,
    };
  } catch {
    purchases.value = [];
  } finally {
    purchasesLoading.value = false;
  }
};

watch([purchaseDateFrom, purchaseDateTo], () => {
  purchasesPage.value = 1;
  fetchPurchases();
});

/* ─── TAB SWITCH → LAZY LOAD ───────────── */
watch(activeTab, (tab) => {
  if (tab === "stock" && branchStock.value.length === 0) fetchBranchStock();
  if (tab === "sales" && sales.value.length === 0) fetchSales();
  if (tab === "purchases" && purchases.value.length === 0) fetchPurchases();
});

/* ─── MOUNT ─────────────────────────────── */
onMounted(() => {
  fetchProduct();
  fetchBranchStock(); // load stock tab by default
});

/* ─── ACTIONS ───────────────────────────── */
const editProduct = () => router.push(`/products/edit/${productId.value}`);

const deleteProduct = async () => {
  if (!confirm("Are you sure you want to delete this product?")) return;
  try {
    await ProductService.delete(productId.value);
    router.push("/products");
  } catch {
    alert("Error deleting product.");
  }
};

/* ─── HELPERS ───────────────────────────── */
const margin = (p) =>
  p.selling_price > 0
    ? Math.round(((p.selling_price - p.buying_price) / p.selling_price) * 100)
    : 0;

const stockBadge = (qty) => {
  if (qty === 0)
    return { label: "Out of Stock", cls: "bg-red-50 text-red-600 border-red-200" };
  if (qty < 10)
    return { label: "Low Stock", cls: "bg-amber-50 text-amber-600 border-amber-200" };
  return { label: "In Stock", cls: "bg-emerald-50 text-emerald-700 border-emerald-200" };
};

const stockBarStyle = (qty) => {
  if (qty === 0) return "width:0%;background:#E84545";
  if (qty < 10) return `width:${Math.round((qty / 10) * 40 + 10)}%;background:#CA8A04`;
  return `width:${Math.min(qty, 100)}%;background:#16A34A`;
};

const purchaseStatusBadge = (status) => {
  const s = (status || "").toLowerCase();
  if (s === "received") return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (s === "pending") return "bg-amber-50 text-amber-600 border-amber-200";
  if (s === "cancelled") return "bg-red-50 text-red-600 border-red-200";
  return "bg-slate-50 text-slate-500 border-slate-200";
};

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return new Date(dateStr).toLocaleDateString("en-KE", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-active .bg-white,
.modal-fade-leave-active .bg-white {
  transition: transform 0.2s ease;
}
.modal-fade-enter-from .bg-white {
  transform: scale(0.96) translateY(8px);
}
</style>
