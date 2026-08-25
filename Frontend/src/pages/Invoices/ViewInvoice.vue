<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK -->
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
      Back to Invoices
    </button>

    <!-- ── HERO CARD ── -->
    <div
      class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden mb-6"
    >
      <div
        class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
      ></div>

      <div class="p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
          <!-- Left: Invoice info -->
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
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-3 mb-1">
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                  {{ invoice?.invoice_number }}
                </h1>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                  :class="paymentStatusClass(invoice?.payment_status)"
                >
                  {{ capitalize(invoice?.payment_status ?? "unpaid") }}
                </span>
                <span
                  v-if="isOverdue"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-50 text-orange-600 border border-orange-200"
                >
                  Overdue
                </span>
              </div>

              <!-- Customer -->
              <div class="flex items-center gap-2 mb-2">
                <div
                  class="w-7 h-7 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-[10px]"
                >
                  {{ (invoice?.customer?.name || "C").substring(0, 2).toUpperCase() }}
                </div>
                <span class="text-sm font-bold text-slate-700">{{
                  invoice?.customer?.name || "—"
                }}</span>
                <span v-if="invoice?.customer?.phone" class="text-xs text-slate-400"
                  >· {{ invoice.customer.phone }}</span
                >
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
                  Issued: {{ formatDate(invoice?.issue_date) }}
                </span>
                <span
                  class="text-sm flex items-center gap-1.5"
                  :class="isOverdue ? 'text-red-500 font-bold' : 'text-slate-500'"
                >
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
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  Due: {{ formatDate(invoice?.due_date) }}
                </span>
                <span
                  v-if="invoice?.branch"
                  class="text-sm text-slate-500 flex items-center gap-1.5"
                >
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
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                  </svg>
                  {{ invoice.branch.name }}
                </span>
                <span
                  v-if="invoice?.quotation_id"
                  class="text-sm text-[#229DE7] font-bold flex items-center gap-1.5"
                >
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
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                    />
                  </svg>
                  From Quotation {{ invoice?.quotation?.quotation_number }}
                </span>
              </div>
            </div>
          </div>

          <!-- Right: Stat boxes -->
          <div
            class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 gap-3 flex-shrink-0"
          >
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-lg font-extrabold text-slate-800">
                {{ invoice?.items?.length ?? 0 }}
              </div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
              >
                Items
              </div>
            </div>
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-lg font-extrabold text-[#229DE7]">
                {{ formatCurrency(invoice?.grand_total) }}
              </div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
              >
                Total
              </div>
            </div>
            <div
              class="text-center px-4 py-3 bg-[#F0F6FB] rounded-xl border border-[#D9EBF7]"
            >
              <div class="text-lg font-extrabold text-emerald-600">
                {{ formatCurrency(invoice?.paid_amount) }}
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
                :class="(invoice?.balance ?? 0) > 0 ? 'text-red-500' : 'text-slate-400'"
              >
                {{ formatCurrency(invoice?.balance) }}
              </div>
              <div
                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5"
              >
                Balance
              </div>
            </div>
          </div>
        </div>

        <!-- Notes strip -->
        <div
          v-if="invoice?.notes"
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
          <p class="text-xs text-amber-700 font-semibold">{{ invoice.notes }}</p>
        </div>
      </div>

      <!-- Action bar -->
      <div
        class="px-6 py-3 bg-[#F5FAFF] border-t border-[#EEF5FB] flex flex-wrap items-center gap-3"
      >
        <router-link
          :to="{ name: 'EditInvoice', params: { id: invoiceId } }"
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
          Edit Invoice
        </router-link>

        <button
          v-if="(invoice?.balance ?? 0) > 0"
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

        <router-link
          :to="{ name: 'InvoiceDocuments', params: { id: invoiceId } }"
          class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all active:scale-95"
        >
          <svg
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2.2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
            />
          </svg>
          Print / Documents
        </router-link>

        <button
          @click="deleteInvoice"
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

    <!-- ══════ ITEMS TAB ══════ -->
    <div v-if="activeTab === 'items'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Line Items
          </h2>
          <span class="text-xs font-bold text-slate-400"
            >{{ invoice?.items?.length ?? 0 }} item{{
              (invoice?.items?.length ?? 0) !== 1 ? "s" : ""
            }}</span
          >
        </div>

        <div v-if="pageLoading" class="p-6 space-y-3">
          <div
            v-for="n in 4"
            :key="n"
            class="h-12 bg-slate-50 animate-pulse rounded-xl"
          ></div>
        </div>

        <div v-else-if="!invoice?.items?.length" class="py-16 text-center">
          <p class="font-bold text-slate-700">No items</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr
                class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
              >
                <th class="px-5 py-3.5 w-10">#</th>
                <th class="px-5 py-3.5">Product / Service</th>
                <th class="px-5 py-3.5">Description</th>
                <th class="px-5 py-3.5 text-right">Qty</th>
                <th class="px-5 py-3.5 text-right">Unit Price</th>
                <th class="px-5 py-3.5 text-right">Disc %</th>
                <th class="px-5 py-3.5 text-right">Tax %</th>
                <th class="px-5 py-3.5 text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr
                v-for="(item, i) in invoice.items"
                :key="item.id"
                class="hover:bg-[#F5FAFF] transition-colors"
              >
                <td class="px-5 py-3.5 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
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
                      <div v-if="item.product?.sku" class="text-[10.5px] text-slate-400">
                        SKU: {{ item.product.sku }}
                      </div>
                    </div>
                  </div>
                </td>
                <td
                  class="px-5 py-3.5 text-xs text-slate-500 italic max-w-[160px] truncate"
                >
                  {{ item.description || "—" }}
                </td>
                <td class="px-5 py-3.5 text-sm font-bold text-slate-700 text-right">
                  {{ item.quantity }}
                </td>
                <td class="px-5 py-3.5 text-sm text-slate-700 text-right">
                  {{ formatCurrency(item.unit_price) }}
                </td>
                <td class="px-5 py-3.5 text-right">
                  <span v-if="item.discount > 0" class="text-xs font-bold text-red-500"
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
                <td class="px-5 py-3.5 text-sm font-extrabold text-slate-800 text-right">
                  {{ formatCurrency(item.line_total) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals footer -->
        <div v-if="invoice?.items?.length" class="px-6 py-5 border-t border-[#EEF5FB]">
          <div class="flex justify-end">
            <div class="w-72 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Subtotal</span
                ><span class="font-bold text-slate-800">{{
                  formatCurrency(invoice.subtotal)
                }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Total Discount</span
                ><span class="font-bold text-red-500"
                  >- {{ formatCurrency(invoice.total_discount) }}</span
                >
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Total Tax</span
                ><span class="font-bold text-amber-600"
                  >+ {{ formatCurrency(invoice.total_tax) }}</span
                >
              </div>
              <div
                class="flex justify-between text-base font-extrabold pt-2 border-t border-[#D9EBF7]"
              >
                <span class="text-slate-800">Grand Total</span
                ><span class="text-[#229DE7]">{{
                  formatCurrency(invoice.grand_total)
                }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-emerald-600 font-semibold">Amount Paid</span
                ><span class="font-bold text-emerald-600">{{
                  formatCurrency(invoice.paid_amount)
                }}</span>
              </div>
              <div
                class="flex justify-between text-sm font-extrabold pt-1 border-t border-[#D9EBF7]"
              >
                <span
                  :class="(invoice.balance ?? 0) > 0 ? 'text-red-500' : 'text-slate-400'"
                  >Balance Due</span
                >
                <span
                  :class="(invoice.balance ?? 0) > 0 ? 'text-red-500' : 'text-slate-400'"
                  >{{ formatCurrency(invoice.balance) }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════ PAYMENTS TAB ══════ -->
    <div v-if="activeTab === 'payments'">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div
          class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
        >
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
            Payment History
          </h2>
          <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-400"
              >{{ payments.length }} record{{ payments.length !== 1 ? "s" : "" }}</span
            >
            <button
              v-if="(invoice?.balance ?? 0) > 0"
              @click="showPaymentModal = true"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition"
            >
              <svg
                class="w-3 h-3"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              Record Payment
            </button>
          </div>
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
              v-if="(invoice?.balance ?? 0) > 0"
              @click="showPaymentModal = true"
              class="text-[#229DE7] font-bold hover:underline"
            >
              Record the first payment
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
                <td class="px-5 py-3.5 text-xs font-bold text-slate-400">{{ i + 1 }}</td>
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
                  class="px-5 py-3.5 text-xs text-slate-400 italic max-w-[130px] truncate"
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
                  Total Received
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

    <!-- ══════ DETAILS TAB ══════ -->
    <div v-if="activeTab === 'details'">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer info -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Customer
            </h3>
          </div>
          <div class="p-6 space-y-3">
            <div class="detail-block">
              <span class="detail-label">Name</span
              ><span class="detail-value">{{ invoice?.customer?.name || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Email</span
              ><span class="detail-value">{{ invoice?.customer?.email || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Phone</span
              ><span class="detail-value">{{ invoice?.customer?.phone || "—" }}</span>
            </div>
          </div>
        </div>

        <!-- Invoice details -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Invoice Info
            </h3>
          </div>
          <div class="p-6 space-y-3">
            <div class="detail-block">
              <span class="detail-label">Invoice Number</span
              ><span class="detail-value">{{ invoice?.invoice_number || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Issue Date</span
              ><span class="detail-value">{{ formatDate(invoice?.issue_date) }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Due Date</span
              ><span
                class="detail-value"
                :class="isOverdue ? 'text-red-500 font-bold' : ''"
                >{{ formatDate(invoice?.due_date) }}</span
              >
            </div>
            <div class="detail-block">
              <span class="detail-label">Currency</span
              ><span class="detail-value">{{ invoice?.currency || "KES" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Payment Type</span
              ><span class="detail-value">{{
                capitalize(invoice?.payment_type ?? "—")
              }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Payment Method</span
              ><span class="detail-value">{{
                capitalize(invoice?.payment_method ?? "—")
              }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Payment Reference</span
              ><span class="detail-value">{{ invoice?.payment_reference || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">From Quotation</span
              ><span class="detail-value text-[#229DE7]">{{
                invoice?.quotation?.quotation_number || "—"
              }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Branch</span
              ><span class="detail-value">{{ invoice?.branch?.name || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Created By</span
              ><span class="detail-value">{{ invoice?.user?.name || "—" }}</span>
            </div>
            <div class="detail-block">
              <span class="detail-label">Created</span
              ><span class="detail-value">{{ formatDate(invoice?.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════ RECORD PAYMENT MODAL ══════ -->
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
          <!-- Balance info banner -->
          <div
            class="bg-[#F0F6FB] rounded-xl border border-[#D9EBF7] p-4 flex justify-between items-center"
          >
            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                Outstanding Balance
              </p>
              <p class="text-xl font-extrabold text-red-500">
                {{ formatCurrency(invoice?.balance) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                Invoice Total
              </p>
              <p class="text-base font-bold text-slate-700">
                {{ formatCurrency(invoice?.grand_total) }}
              </p>
            </div>
          </div>

          <div>
            <label class="modal-label required">Amount (KES)</label>
            <input
              v-model.number="paymentForm.amount"
              type="number"
              min="0.01"
              :max="invoice?.balance"
              step="any"
              class="modal-input"
              :class="{ 'border-red-400': paymentErrors.amount }"
              placeholder="0.00"
            />
            <p v-if="paymentErrors.amount" class="field-error">
              {{ paymentErrors.amount }}
            </p>
          </div>

          <div>
            <label class="modal-label required">Payment Method</label>
            <select
              v-model="paymentForm.payment_method"
              class="modal-input"
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
            <label class="modal-label"
              >Reference
              <span class="text-slate-400 font-normal text-[11px]"
                >(Optional)</span
              ></label
            >
            <input
              v-model="paymentForm.payment_reference"
              type="text"
              class="modal-input"
              placeholder="e.g. Mpesa code, cheque no."
            />
          </div>

          <div>
            <label class="modal-label required">Payment Date</label>
            <input
              v-model="paymentForm.payment_date"
              type="date"
              class="modal-input"
              :class="{ 'border-red-400': paymentErrors.payment_date }"
            />
            <p v-if="paymentErrors.payment_date" class="field-error">Date is required</p>
          </div>

          <div>
            <label class="modal-label"
              >Notes
              <span class="text-slate-400 font-normal text-[11px]"
                >(Optional)</span
              ></label
            >
            <textarea
              v-model="paymentForm.notes"
              rows="2"
              class="modal-input resize-none"
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
import InvoiceService from "@/services/InvoiceService";

const route = useRoute();
const router = useRouter();
const invoiceId = route.params.id;

const pageLoading = ref(true);
const loadingPayments = ref(false);
const savingPayment = ref(false);
const showPaymentModal = ref(false);

const invoice = ref(null);
const payments = ref([]);

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
const isOverdue = computed(
  () =>
    invoice.value?.payment_status !== "paid" &&
    invoice.value?.due_date &&
    new Date(invoice.value.due_date) < new Date()
);

const totalPaid = computed(() =>
  payments.value.reduce((s, p) => s + Number(p.amount ?? 0), 0)
);

/* ─── HELPERS ─────────────────────────── */
const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : "—");
const formatDate = (val) =>
  val
    ? new Date(val).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "long",
        year: "numeric",
      })
    : "—";
const formatCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));
const paymentStatusClass = (status) =>
  ({
    paid: "bg-emerald-50 text-emerald-700 border-emerald-200",
    partial: "bg-amber-50 text-amber-700 border-amber-200",
    unpaid: "bg-red-50 text-red-600 border-red-200",
  }[status] || "bg-slate-100 text-slate-500 border-slate-200");
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
const loadInvoice = async () => {
  const res = await InvoiceService.getById(invoiceId);
  invoice.value = res?.data?.data ?? res?.data ?? null;
};

const loadPayments = async () => {
  loadingPayments.value = true;
  try {
    const res = await InvoiceService.getPayments(invoiceId);
    payments.value = res?.data?.data ?? res?.data ?? [];
  } finally {
    loadingPayments.value = false;
  }
};

onMounted(async () => {
  pageLoading.value = true;
  try {
    await Promise.all([loadInvoice(), loadPayments()]);
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Could not load invoice.", "error");
  } finally {
    setTimeout(() => {
      pageLoading.value = false;
    }, 400);
  }
});

/* ─── DELETE ──────────────────────────── */
const deleteInvoice = async () => {
  const result = await Swal.fire({
    title: "Delete this invoice?",
    text: "This cannot be undone.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  try {
    await InvoiceService.delete(invoiceId);
    notify("success", "Invoice Deleted");
    router.push({ name: "InvoicesList" });
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
  paymentForm.notes = "";
  paymentForm.payment_date = new Date().toISOString().split("T")[0];
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
  } else if (paymentForm.amount > (invoice.value?.balance ?? 0)) {
    paymentErrors.amount = `Cannot exceed balance of ${formatCurrency(
      invoice.value?.balance
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
    await InvoiceService.recordPayment(invoiceId, {
      amount: paymentForm.amount,
      payment_method: paymentForm.payment_method,
      payment_reference: paymentForm.payment_reference || null,
      payment_date: paymentForm.payment_date,
      notes: paymentForm.notes || null,
    });
    notify("success", "Payment Recorded");
    closePaymentModal();
    await Promise.all([loadInvoice(), loadPayments()]);
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
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

.modal-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.modal-label.required::after {
  content: " *";
  color: #ef4444;
}
.modal-input {
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
.modal-input:focus {
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
