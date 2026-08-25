<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK BUTTON -->
    <button
      @click="$router.back()"
      class="inline-flex items-center gap-2 text-sm font-bold text-[#229DE7] hover:text-[#1682C4] mb-5 transition group"
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
      Back to Purchases
    </button>

    <!-- Skeleton while loading purchase header -->
    <div v-if="pageLoading" class="space-y-4 mb-6">
      <div class="h-36 bg-white border border-[#D9EBF7] rounded-2xl animate-pulse"></div>
      <div
        class="h-10 w-64 bg-white border border-[#D9EBF7] rounded-xl animate-pulse"
      ></div>
      <div class="h-48 bg-white border border-[#D9EBF7] rounded-2xl animate-pulse"></div>
    </div>

    <template v-else>
      <!-- ── HERO HEADER CARD ── -->
      <div
        class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden mb-6"
      >
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
        ></div>

        <div class="p-6">
          <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
            <!-- Left: PO info -->
            <div class="flex items-start gap-4">
              <div
                class="w-14 h-14 rounded-2xl bg-[#EAF5FD] border-2 border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0"
              >
                <svg
                  class="w-7 h-7"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
                  />
                </svg>
              </div>
              <div>
                <div class="flex flex-wrap items-center gap-3 mb-1">
                  <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                    {{
                      purchase?.reference || `PO-${String(purchase?.id).padStart(4, "0")}`
                    }}
                  </h1>
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                    :class="paymentStatusClass(purchase?.payment_status)"
                  >
                    {{ capitalize(purchase?.payment_status ?? "unpaid") }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-1">
                  <span class="text-sm text-slate-500 flex items-center gap-1.5">
                    <svg
                      class="w-3.5 h-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                    {{ formatDate(purchase?.purchase_date) }}
                  </span>
                  <span class="text-sm text-slate-500 flex items-center gap-1.5">
                    <svg
                      class="w-3.5 h-3.5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                      />
                    </svg>
                    Created by {{ purchase?.user?.name || "Admin" }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-3">
                  <span
                    v-for="b in purchase?.branches"
                    :key="b.id"
                    class="px-2.5 py-0.5 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-full text-[10.5px] font-bold"
                  >
                    {{ b.name }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Right: Financials summary -->
            <div
              class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 gap-3 flex-shrink-0"
            >
              <div
                class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
              >
                <div class="text-lg font-extrabold text-slate-800">
                  {{ formatCurrency(purchase?.grand_total) }}
                </div>
                <div
                  class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
                >
                  Grand Total
                </div>
              </div>
              <div
                class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
              >
                <div class="text-lg font-extrabold text-emerald-600">
                  {{ formatCurrency(purchase?.paid_amount) }}
                </div>
                <div
                  class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
                >
                  Paid
                </div>
              </div>
              <div
                class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
              >
                <div
                  class="text-lg font-extrabold"
                  :class="
                    (purchase?.balance ?? 0) > 0 ? 'text-red-500' : 'text-slate-400'
                  "
                >
                  {{ formatCurrency(purchase?.balance) }}
                </div>
                <div
                  class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
                >
                  Balance
                </div>
              </div>
              <div
                class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
              >
                <div class="text-lg font-extrabold text-[#229DE7]">{{ itemsCount }}</div>
                <div
                  class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
                >
                  Items
                </div>
              </div>
            </div>
          </div>

          <!-- Notes strip -->
          <div
            v-if="purchase?.notes"
            class="mt-4 px-4 py-3 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-2"
          >
            <svg
              class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              />
            </svg>
            <p class="text-xs text-amber-700 font-semibold">{{ purchase.notes }}</p>
          </div>
        </div>

        <!-- Action bar -->
        <div
          class="px-6 py-3 bg-[#F5FAFF] border-t border-[#EEF5FB] flex flex-wrap items-center gap-3"
        >
          <router-link
            :to="{ name: 'EditPurchase', params: { id: purchaseId } }"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-xs font-bold rounded-xl shadow-sm shadow-[#229DE7]/30 transition-all active:scale-95"
          >
            <svg
              class="w-3.5 h-3.5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
              />
            </svg>
            Edit Purchase
          </router-link>
          <button
            v-if="(purchase?.balance ?? 0) > 0"
            @click="showPaymentModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95"
          >
            <svg
              class="w-3.5 h-3.5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
              />
            </svg>
            Record Payment
          </button>
          <button
            @click="deletePurchase"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-500 hover:bg-red-50 text-xs font-bold rounded-xl transition-all active:scale-95 ml-auto"
          >
            <svg
              class="w-3.5 h-3.5"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
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

      <!-- ── TABS ── -->
      <div
        class="flex gap-1 mb-5 bg-white border border-[#D9EBF7] rounded-xl p-1 w-fit shadow-sm"
      >
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-5 py-2 rounded-lg text-sm font-bold transition-all"
          :class="
            activeTab === tab.key
              ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
              : 'text-slate-500 hover:text-[#229DE7] hover:bg-[#EAF5FD]'
          "
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ══════════ ITEMS TAB ══════════ -->
      <div v-if="activeTab === 'items'">
        <div
          v-if="loadingItems"
          class="bg-white border border-[#D9EBF7] rounded-2xl p-6 space-y-3"
        >
          <div
            v-for="n in 4"
            :key="n"
            class="h-12 bg-slate-50 animate-pulse rounded-xl"
          ></div>
        </div>

        <div v-else class="space-y-5">
          <div
            v-for="group in groupedItems"
            :key="group.branch.id"
            class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between bg-[#F5FAFF]"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center"
                >
                  <svg
                    class="w-4 h-4 text-[#229DE7]"
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
                <div>
                  <h3 class="font-extrabold text-sm text-slate-800">
                    {{ group.branch.name }}
                  </h3>
                  <p class="text-[10.5px] text-slate-400 font-semibold">
                    {{ group.items.length }} item{{ group.items.length !== 1 ? "s" : "" }}
                  </p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                  Branch Total
                </p>
                <p class="text-base font-extrabold text-[#229DE7]">
                  {{ formatCurrency(group.subtotal) }}
                </p>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr
                    class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                  >
                    <th class="px-5 py-3 w-10">#</th>
                    <th class="px-5 py-3">Product</th>
                    <th class="px-5 py-3 text-right">Qty</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-right">Disc %</th>
                    <th class="px-5 py-3 text-right">Tax %</th>
                    <th class="px-5 py-3">Expiry</th>
                    <th class="px-5 py-3">Batch</th>
                    <th class="px-5 py-3 text-right">Line Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF5FB]">
                  <tr
                    v-for="(item, i) in group.items"
                    :key="item.id"
                    class="hover:bg-[#F5FAFF] transition-colors"
                  >
                    <td class="px-5 py-3.5 text-xs font-bold text-slate-400">
                      {{ i + 1 }}
                    </td>
                    <td class="px-5 py-3.5">
                      <div class="flex items-center gap-2.5">
                        <div
                          class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-[10px] flex-shrink-0"
                        >
                          {{ (item.product?.name || "P").substring(0, 2).toUpperCase() }}
                        </div>
                        <div>
                          <div class="font-bold text-sm text-slate-800">
                            {{ item.product?.name || "—" }}
                          </div>
                          <div class="text-[10.5px] text-slate-400">
                            {{ item.product?.sku || "" }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm font-bold text-slate-700 text-right">
                      {{ item.quantity }}
                    </td>
                    <td class="px-5 py-3.5 text-sm font-bold text-slate-700 text-right">
                      {{ formatCurrency(item.unit_price) }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                      <span
                        v-if="item.discount > 0"
                        class="text-xs font-bold text-red-500"
                        >{{ item.discount }}%</span
                      >
                      <span v-else class="text-xs text-slate-300">—</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                      <span v-if="item.tax > 0" class="text-xs font-bold text-amber-600"
                        >{{ item.tax }}%</span
                      >
                      <span v-else class="text-xs text-slate-300">—</span>
                    </td>
                    <td class="px-5 py-3.5">
                      <span
                        v-if="item.expiry_date"
                        class="text-xs font-semibold"
                        :class="
                          isExpiringSoon(item.expiry_date)
                            ? 'text-red-500 font-bold'
                            : 'text-slate-500'
                        "
                      >
                        {{ formatDate(item.expiry_date) }}
                        <span
                          v-if="isExpiringSoon(item.expiry_date)"
                          class="ml-1 text-[9px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-bold"
                          >SOON</span
                        >
                      </span>
                      <span v-else class="text-xs text-slate-300">—</span>
                    </td>
                    <td class="px-5 py-3.5">
                      <span
                        v-if="item.batch_number"
                        class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10.5px] font-bold"
                      >
                        {{ item.batch_number }}
                      </span>
                      <span v-else class="text-xs text-slate-300">—</span>
                    </td>
                    <td
                      class="px-5 py-3.5 text-sm font-extrabold text-slate-800 text-right"
                    >
                      {{ formatCurrency(item.line_total) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="bg-[#F0F6FB] border-t-2 border-[#D9EBF7]">
                    <td
                      colspan="8"
                      class="px-5 py-3 text-xs font-bold text-slate-500 text-right uppercase tracking-widest"
                    >
                      Branch Subtotal
                    </td>
                    <td
                      class="px-5 py-3 text-base font-extrabold text-[#229DE7] text-right"
                    >
                      {{ formatCurrency(group.subtotal) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div
            v-if="groupedItems.length === 0"
            class="bg-white border border-[#D9EBF7] rounded-2xl py-16 text-center"
          >
            <p class="font-bold text-slate-700">No items found</p>
            <p class="text-xs text-slate-400 mt-1">
              This purchase has no recorded items.
            </p>
          </div>

          <div
            v-if="groupedItems.length > 0"
            class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
          >
            <div class="px-6 py-4 border-b border-[#EEF5FB]">
              <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
                Order Totals
              </h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg ml-auto">
              <div
                class="flex justify-between items-center text-sm border-b border-[#EEF5FB] pb-2"
              >
                <span class="text-slate-500 font-semibold">Subtotal</span>
                <span class="font-bold text-slate-800">{{
                  formatCurrency(purchase?.subtotal)
                }}</span>
              </div>
              <div
                class="flex justify-between items-center text-sm border-b border-[#EEF5FB] pb-2 sm:col-start-2"
              >
                <span class="text-slate-500 font-semibold">Total Discount</span>
                <span class="font-bold text-red-500"
                  >- {{ formatCurrency(purchase?.total_discount) }}</span
                >
              </div>
              <div
                class="flex justify-between items-center text-sm border-b border-[#EEF5FB] pb-2"
              >
                <span class="text-slate-500 font-semibold">Total Tax</span>
                <span class="font-bold text-amber-600"
                  >+ {{ formatCurrency(purchase?.total_tax) }}</span
                >
              </div>
              <div
                class="flex justify-between items-center text-base font-extrabold border-t-2 border-[#D9EBF7] pt-2 sm:col-span-2"
              >
                <span class="text-slate-800">Grand Total</span>
                <span class="text-[#229DE7]">{{
                  formatCurrency(purchase?.grand_total)
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════ PAYMENTS TAB ══════════ -->
      <div v-if="activeTab === 'payments'">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Payment History
            </h2>
            <span class="text-xs font-bold text-slate-400"
              >{{ payments.length }} record{{ payments.length !== 1 ? "s" : "" }}</span
            >
          </div>

          <div v-if="loadingPayments" class="p-6 space-y-3">
            <div
              v-for="n in 3"
              :key="n"
              class="h-12 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else-if="payments.length === 0" class="py-16 text-center">
            <div
              class="w-14 h-14 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-3"
            >
              <svg
                class="w-7 h-7 text-[#229DE7]"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                />
              </svg>
            </div>
            <p class="font-bold text-slate-700">No payments recorded</p>
            <p class="text-xs text-slate-400 mt-1">
              <button
                v-if="(purchase?.balance ?? 0) > 0"
                @click="showPaymentModal = true"
                class="text-[#229DE7] font-bold hover:underline"
              >
                Record a payment
              </button>
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-5 py-3.5 w-10">#</th>
                  <th class="px-5 py-3.5">Date</th>
                  <th class="px-5 py-3.5">Method</th>
                  <th class="px-5 py-3.5">Reference</th>
                  <th class="px-5 py-3.5">Recorded By</th>
                  <th class="px-5 py-3.5">Notes</th>
                  <th class="px-5 py-3.5 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(payment, i) in payments"
                  :key="payment.id"
                  class="hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-3.5 text-xs font-bold text-slate-400">
                    {{ i + 1 }}
                  </td>
                  <td
                    class="px-5 py-3.5 text-xs font-semibold text-slate-600 whitespace-nowrap"
                  >
                    {{ formatDate(payment.payment_date) }}
                  </td>
                  <td class="px-5 py-3.5">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA]"
                    >
                      {{ capitalize(payment.payment_method ?? "Cash") }}
                    </span>
                  </td>
                  <td class="px-5 py-3.5 text-xs font-semibold text-slate-600">
                    {{ payment.payment_reference || "—" }}
                  </td>
                  <td class="px-5 py-3.5 text-xs text-slate-500">
                    {{ payment.user?.name || "—" }}
                  </td>
                  <td
                    class="px-5 py-3.5 text-xs text-slate-400 italic max-w-[140px] truncate"
                  >
                    {{ payment.notes || "—" }}
                  </td>
                  <td
                    class="px-5 py-3.5 text-sm font-extrabold text-emerald-600 text-right"
                  >
                    {{ formatCurrency(payment.amount) }}
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-[#F0F6FB] border-t-2 border-[#D9EBF7]">
                  <td
                    colspan="6"
                    class="px-5 py-3 text-xs font-bold text-slate-500 text-right uppercase tracking-widest"
                  >
                    Total Paid
                  </td>
                  <td
                    class="px-5 py-3 text-base font-extrabold text-emerald-600 text-right"
                  >
                    {{ formatCurrency(totalPaid) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- ══════════ DETAILS TAB ══════════ -->
      <div v-if="activeTab === 'details'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Supplier -->
          <div
            class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
          >
            <div
              class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
            ></div>
            <div class="px-6 py-4 border-b border-[#EEF5FB]">
              <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
                Supplier
              </h3>
            </div>
            <div class="p-6 space-y-3">
              <div class="detail-block">
                <span class="detail-label">Name</span
                ><span class="detail-value">{{ purchase?.supplier?.name || "—" }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Email</span
                ><span class="detail-value">{{ purchase?.supplier?.email || "—" }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Phone</span
                ><span class="detail-value">{{ purchase?.supplier?.phone || "—" }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Address</span
                ><span class="detail-value">{{
                  purchase?.supplier?.address || "—"
                }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Tax Number</span
                ><span class="detail-value">{{
                  purchase?.supplier?.tax_number || "—"
                }}</span>
              </div>
            </div>
          </div>

          <!-- Purchase Info -->
          <div
            class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
          >
            <div
              class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
            ></div>
            <div class="px-6 py-4 border-b border-[#EEF5FB]">
              <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
                Purchase Info
              </h3>
            </div>
            <div class="p-6 space-y-3">
              <div class="detail-block">
                <span class="detail-label">Reference</span
                ><span class="detail-value">{{ purchase?.reference || "—" }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Purchase Date</span
                ><span class="detail-value">{{
                  formatDate(purchase?.purchase_date)
                }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Payment Type</span
                ><span class="detail-value">{{
                  capitalize(purchase?.payment_type ?? "—")
                }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Payment Method</span
                ><span class="detail-value">{{
                  capitalize(purchase?.payment_method ?? "—")
                }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Payment Reference</span
                ><span class="detail-value">{{
                  purchase?.payment_reference || "—"
                }}</span>
              </div>
              <div class="detail-block">
                <span class="detail-label">Created</span
                ><span class="detail-value">{{ formatDate(purchase?.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Branches -->
          <div
            class="lg:col-span-2 bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
          >
            <div class="px-6 py-4 border-b border-[#EEF5FB]">
              <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
                Branches
              </h3>
            </div>
            <div class="p-6">
              <div class="flex flex-wrap gap-3">
                <div
                  v-for="b in purchase?.branches"
                  :key="b.id"
                  class="flex items-center gap-3 px-4 py-3 bg-[#F0F6FB] border border-[#D9EBF7] rounded-xl"
                >
                  <div
                    class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7]"
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
                  <div>
                    <p class="text-sm font-bold text-slate-800">{{ b.name }}</p>
                    <p class="text-[10.5px] text-slate-400">
                      Subtotal:
                      <span class="font-bold text-[#229DE7]">{{
                        formatCurrency(b.pivot?.subtotal)
                      }}</span>
                    </p>
                  </div>
                </div>
                <div v-if="!purchase?.branches?.length" class="text-sm text-slate-400">
                  No branches
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>

  <!-- ══════════ RECORD PAYMENT MODAL ══════════ -->
  <transition name="modal">
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
        @click="closePaymentModal"
      ></div>
      <div
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden"
      >
        <div
          class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
        ></div>
        <div
          class="px-6 py-5 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Record Payment
          </h3>
          <button
            @click="closePaymentModal"
            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 transition"
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

        <div class="p-6 space-y-4">
          <div
            class="bg-[#F0F6FB] rounded-xl border border-[#D9EBF7] p-4 flex justify-between items-center"
          >
            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                Outstanding Balance
              </p>
              <p class="text-xl font-extrabold text-red-500">
                {{ formatCurrency(purchase?.balance) }}
              </p>
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                Grand Total
              </p>
              <p class="text-base font-bold text-slate-700">
                {{ formatCurrency(purchase?.grand_total) }}
              </p>
            </div>
          </div>

          <div>
            <label class="field-label required">Amount (KES)</label>
            <input
              v-model.number="paymentForm.amount"
              type="number"
              min="0.01"
              :max="purchase?.balance"
              step="any"
              class="field-input"
              :class="{ 'border-red-400': paymentErrors.amount }"
              placeholder="0.00"
            />
            <p v-if="paymentErrors.amount" class="field-error">
              {{ paymentErrors.amount }}
            </p>
          </div>

          <div>
            <label class="field-label required">Payment Method</label>
            <select
              v-model="paymentForm.payment_method"
              class="field-input"
              :class="{ 'border-red-400': paymentErrors.payment_method }"
            >
              <option value="" disabled>Select method...</option>
              <option value="cash">Cash</option>
              <option value="mpesa">M-Pesa</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="credit">Credit</option>
            </select>
            <p v-if="paymentErrors.payment_method" class="field-error">
              Method is required
            </p>
          </div>

          <div>
            <label class="field-label"
              >Reference
              <span class="text-slate-400 font-normal text-[11px]"
                >(Optional)</span
              ></label
            >
            <input
              v-model="paymentForm.payment_reference"
              type="text"
              class="field-input"
              placeholder="e.g. Mpesa code"
            />
          </div>

          <div>
            <label class="field-label required">Payment Date</label>
            <input
              v-model="paymentForm.payment_date"
              type="date"
              class="field-input"
              :class="{ 'border-red-400': paymentErrors.payment_date }"
            />
            <p v-if="paymentErrors.payment_date" class="field-error">Date is required</p>
          </div>

          <div>
            <label class="field-label"
              >Notes
              <span class="text-slate-400 font-normal text-[11px]"
                >(Optional)</span
              ></label
            >
            <textarea
              v-model="paymentForm.notes"
              rows="2"
              class="field-input resize-none"
              placeholder="Optional note..."
            ></textarea>
          </div>
        </div>

        <div class="px-6 pb-6 flex gap-3">
          <button
            @click="closePaymentModal"
            class="flex-1 py-2.5 border border-[#D9EBF7] rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition"
          >
            Cancel
          </button>
          <button
            @click="submitPayment"
            :disabled="savingPayment"
            class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition disabled:opacity-60"
          >
            <svg
              v-if="savingPayment"
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
            {{ savingPayment ? "Saving..." : "Record Payment" }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import PurchaseService from "@/services/purchaseService";

const route = useRoute();
const router = useRouter();
const purchaseId = route.params.id;

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const loadingItems = ref(false);
const loadingPayments = ref(false);
const savingPayment = ref(false);
const showPaymentModal = ref(false);

const purchase = ref(null);
const payments = ref([]);
const groupedItems = ref([]);

const activeTab = ref("items");
const tabs = [
  { key: "items", label: "Items" },
  { key: "payments", label: "Payments" },
  { key: "details", label: "Details" },
];

const paymentForm = reactive({
  amount: null,
  payment_method: "",
  payment_reference: "",
  payment_date: new Date().toISOString().split("T")[0],
  notes: "",
});

const paymentErrors = reactive({
  amount: "",
  payment_method: false,
  payment_date: false,
});

/* ─── COMPUTED ────────────────────────── */
const itemsCount = computed(() =>
  groupedItems.value.reduce((sum, g) => sum + g.items.length, 0)
);
const totalPaid = computed(() =>
  payments.value.reduce((sum, p) => sum + Number(p.amount ?? 0), 0)
);

/* ─── HELPERS ─────────────────────────── */
const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : "—");

const formatDate = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

const paymentStatusClass = (status) => {
  const map = {
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    partial: "bg-amber-50 text-amber-700 border-amber-200",
    unpaid: "bg-red-50 text-red-600 border-red-200",
    pending: "bg-slate-100 text-slate-500 border-slate-200",
  };
  return map[status?.toLowerCase()] || "bg-slate-100 text-slate-500 border-slate-200";
};

const isExpiringSoon = (dateStr) => {
  if (!dateStr) return false;
  const diff = (new Date(dateStr) - new Date()) / (1000 * 60 * 60 * 24);
  return diff >= 0 && diff <= 90;
};

/* ─── NOTIFY ──────────────────────────── */
const notify = (icon, title) =>
  Swal.fire({
    icon,
    title,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });

/* ─── FETCH ───────────────────────────── */
const loadPurchase = async () => {
  const res = await PurchaseService.getById(purchaseId);
  purchase.value = res?.data?.data ?? res?.data ?? res ?? null;
};

const loadItems = async () => {
  loadingItems.value = true;
  try {
    const res = await PurchaseService.getItems(purchaseId);
    const raw = res?.data?.data ?? res?.data ?? res ?? [];
    if (Array.isArray(raw) && raw[0]?.branch) {
      groupedItems.value = raw;
    } else {
      const map = {};
      raw.forEach((item) => {
        const bid = item.branch_id;
        if (!map[bid]) map[bid] = { branch: item.branch, items: [], subtotal: 0 };
        map[bid].items.push(item);
        map[bid].subtotal += Number(item.line_total ?? 0);
      });
      groupedItems.value = Object.values(map);
    }
  } finally {
    loadingItems.value = false;
  }
};

const loadPayments = async () => {
  loadingPayments.value = true;
  try {
    const res = await PurchaseService.getPayments(purchaseId);
    payments.value = res?.data?.data ?? res?.data ?? res ?? [];
  } finally {
    loadingPayments.value = false;
  }
};

onMounted(async () => {
  try {
    await Promise.all([loadPurchase(), loadItems(), loadPayments()]);
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Could not load purchase.", "error");
  } finally {
    pageLoading.value = false;
  }
});

/* ─── DELETE ──────────────────────────── */
const deletePurchase = async () => {
  const result = await Swal.fire({
    title: "Delete this purchase?",
    text: "This action cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  try {
    await PurchaseService.delete(purchaseId);
    notify("success", "Purchase Deleted");
    router.push({ name: "PurchasesList" });
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

/* ─── PAYMENT MODAL ───────────────────── */
const closePaymentModal = () => {
  showPaymentModal.value = false;
  paymentForm.amount = null;
  paymentForm.payment_method = "";
  paymentForm.payment_reference = "";
  paymentForm.payment_date = new Date().toISOString().split("T")[0];
  paymentForm.notes = "";
  paymentErrors.amount = "";
  paymentErrors.payment_method = false;
  paymentErrors.payment_date = false;
};

const validatePayment = () => {
  let valid = true;
  paymentErrors.amount = "";
  paymentErrors.payment_method = false;
  paymentErrors.payment_date = false;

  if (!paymentForm.amount || paymentForm.amount <= 0) {
    paymentErrors.amount = "Enter a valid amount";
    valid = false;
  } else if (paymentForm.amount > (purchase.value?.balance ?? 0)) {
    paymentErrors.amount = `Cannot exceed balance of ${formatCurrency(
      purchase.value?.balance
    )}`;
    valid = false;
  }
  if (!paymentForm.payment_method) {
    paymentErrors.payment_method = true;
    valid = false;
  }
  if (!paymentForm.payment_date) {
    paymentErrors.payment_date = true;
    valid = false;
  }
  return valid;
};

const submitPayment = async () => {
  if (!validatePayment()) return;
  savingPayment.value = true;
  try {
    await PurchaseService.recordPayment(purchaseId, {
      amount: paymentForm.amount,
      payment_method: paymentForm.payment_method,
      payment_reference: paymentForm.payment_reference || null,
      payment_date: paymentForm.payment_date,
      notes: paymentForm.notes || null,
    });
    notify("success", "Payment Recorded");
    closePaymentModal();
    await Promise.all([loadPurchase(), loadPayments()]);
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Failed",
      text: err?.response?.data?.message || "Could not record payment.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    savingPayment.value = false;
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.25s ease;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}

.detail-block {
  display: flex;
  flex-direction: column;
  padding: 12px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
}
.detail-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 3px;
}
.detail-value {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
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
.field-label.required::after {
  content: " *";
  color: #ef4444;
}
.field-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 3px;
}
</style>
