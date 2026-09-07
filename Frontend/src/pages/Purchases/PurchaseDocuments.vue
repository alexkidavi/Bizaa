<template>
  <!-- SCREEN UI (hidden when printing) -->
  <div class="no-print">
    <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
      <!-- Back -->
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
        Back to Purchase
      </button>

      <!-- Skeleton while loading -->
      <div v-if="pageLoading" class="space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div class="space-y-2">
            <div
              class="h-8 w-56 bg-white border border-[#D9EBF7] rounded-xl animate-pulse"
            ></div>
            <div
              class="h-4 w-40 bg-white border border-[#D9EBF7] rounded-lg animate-pulse"
            ></div>
          </div>
          <div
            class="h-10 w-80 bg-white border border-[#D9EBF7] rounded-xl animate-pulse"
          ></div>
        </div>
        <div class="flex gap-3 mb-6">
          <div
            class="h-10 w-36 bg-white border border-[#D9EBF7] rounded-xl animate-pulse"
          ></div>
          <div
            class="h-10 w-36 bg-white border border-[#D9EBF7] rounded-xl animate-pulse"
          ></div>
        </div>
        <div
          class="w-full max-w-4xl mx-auto bg-white rounded-2xl border border-[#D9EBF7] overflow-hidden"
        >
          <div class="h-10 bg-[#EAF5FD] border-b border-[#B3DEFA]"></div>
          <div class="p-10 space-y-4">
            <div class="h-20 bg-slate-50 animate-pulse rounded-xl"></div>
            <div class="h-16 bg-slate-50 animate-pulse rounded-xl"></div>
            <div class="h-48 bg-slate-50 animate-pulse rounded-xl"></div>
            <div class="h-24 bg-slate-50 animate-pulse rounded-xl"></div>
          </div>
        </div>
      </div>

      <!-- Loaded content -->
      <template v-else>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
              Purchase Documents
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
              {{ purchase?.reference }} · {{ formatDate(purchase?.purchase_date) }}
            </p>
          </div>

          <!-- Document selector tabs -->
          <div
            class="flex gap-1 bg-white border border-[#D9EBF7] rounded-xl p-1 shadow-sm"
          >
            <button
              v-for="doc in docTypes"
              :key="doc.key"
              @click="activeDoc = doc.key"
              class="px-4 py-2 rounded-lg text-xs font-bold transition-all whitespace-nowrap"
              :class="
                activeDoc === doc.key
                  ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30'
                  : 'text-slate-500 hover:text-[#229DE7] hover:bg-[#EAF5FD]'
              "
            >
              {{ doc.label }}
            </button>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
          <button
            @click="printDoc"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95"
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
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
              />
            </svg>
            Print {{ currentDocLabel }}
          </button>
          <button
            @click="downloadPDF"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-sm font-bold rounded-xl shadow-sm transition-all active:scale-95"
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
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
              />
            </svg>
            Download PDF
          </button>
          <div
            class="flex items-center gap-2 ml-auto text-xs text-slate-400 font-semibold"
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
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            Use browser print → Save as PDF to download
          </div>
        </div>

        <!-- Document preview wrapper -->
        <div class="flex justify-center">
          <div
            class="w-full max-w-4xl bg-white rounded-2xl shadow-lg border border-[#D9EBF7] overflow-hidden"
          >
            <div
              class="px-6 py-3 bg-[#EAF5FD] border-b border-[#B3DEFA] flex items-center gap-2"
            >
              <span class="w-2.5 h-2.5 rounded-full bg-[#229DE7] animate-pulse"></span>
              <span class="text-xs font-bold text-[#1682C4] uppercase tracking-widest"
                >Document Preview</span
              >
            </div>
            <div class="p-6 md:p-10">
              <component
                :is="currentDocComponent"
                :purchase="purchase"
                :grouped-items="groupedItems"
                :payments="payments"
                :business="business"
              />
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- PRINT AREA (visible only when printing) -->
  <div class="print-only">
    <component
      :is="currentDocComponent"
      :purchase="purchase"
      :grouped-items="groupedItems"
      :payments="payments"
      :business="business"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, defineComponent, h } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import PurchaseService from "@/services/purchaseService";
import { useAuthStore } from "@/stores/auth";
import { resolveBusiness } from "@/utils/printReceipt.js";
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const purchaseId = route.params.id;

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const purchase = ref(null);
const payments = ref([]);
const groupedItems = ref([]);
const activeDoc = ref("grn");

// ── Business info from auth store ──
const business = ref(resolveBusiness(auth.user));
const docTypes = [
  { key: "grn", label: "Goods Received Note" },
  { key: "invoice", label: "Purchase Invoice" },
  { key: "delivery", label: "Delivery Note" },
];

/* ─── COMPUTED ────────────────────────── */
const currentDocLabel = computed(
  () => docTypes.find((d) => d.key === activeDoc.value)?.label ?? ""
);
const currentDocComponent = computed(() => {
  if (activeDoc.value === "grn") return GRNDocument;
  if (activeDoc.value === "invoice") return InvoiceDocument;
  if (activeDoc.value === "delivery") return DeliveryDocument;
  return GRNDocument;
});

/* ─── HELPERS ─────────────────────────── */
const formatDate = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

/* ─── FETCH ───────────────────────────── */
onMounted(async () => {
  pageLoading.value = true;
  try {
    const [purchaseRes, itemsRes, paymentsRes] = await Promise.all([
      PurchaseService.getById(purchaseId),
      PurchaseService.getItems(purchaseId),
      PurchaseService.getPayments(purchaseId),
    ]);

    purchase.value = purchaseRes?.data?.data ?? purchaseRes?.data ?? null;
    payments.value = paymentsRes?.data?.data ?? paymentsRes?.data ?? [];

    const raw = itemsRes?.data?.data ?? itemsRes?.data ?? [];
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
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Could not load purchase documents.", "error");
  } finally {
    pageLoading.value = false;
  }
});

/* ─── PRINT / DOWNLOAD ────────────────── */
const printDoc = () => window.print();

const downloadPDF = () => {
  Swal.fire({
    icon: "info",
    title: "Save as PDF",
    html: `In the print dialog, select <strong>"Save as PDF"</strong> as your printer to download a PDF copy.`,
    confirmButtonText: "Open Print Dialog",
    confirmButtonColor: "#229DE7",
    showCancelButton: true,
  }).then((r) => {
    if (r.isConfirmed) window.print();
  });
};
</script>

<!-- ============================================================
     SHARED DOCUMENT STYLES
     ============================================================ -->
<style>
.print-only {
  display: none;
}

@media print {
  .no-print {
    display: none !important;
  }
  .print-only {
    display: block !important;
  }
  body {
    margin: 0;
    padding: 0;
    background: #fff;
  }
  .page-break {
    page-break-after: always;
  }
  tr {
    page-break-inside: avoid;
  }
}
</style>

<!-- ============================================================
     DOCUMENT COMPONENTS
     ============================================================ -->
<script>
const fmtDate = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

const fmtCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : "—");

const DocHeader = defineComponent({
  props: ["business", "purchase", "title", "docNumber", "accentColor", "accentBg"],
  setup(props) {
    return () =>
      h("div", { style: "margin-bottom: 32px;" }, [
        h("div", {
          style: `background: ${props.accentColor}; height: 6px; border-radius: 3px 3px 0 0;`,
        }),
        h(
          "div",
          {
            style:
              "border: 1.5px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; padding: 28px 32px;",
          },
          [
            h(
              "div",
              {
                style:
                  "display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;",
              },

              [
                h("div", {}, [
                  props.business?.logo
                    ? h("img", {
                        src: props.business.logo,
                        style: "height: 48px; margin-bottom: 12px; object-fit: contain;",
                      })
                    : h(
                        "div",
                        {
                          style: `display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 12px; background: ${props.accentBg}; border: 2px solid ${props.accentColor}40; margin-bottom: 12px;`,
                        },
                        [
                          h(
                            "span",
                            {
                              style: `font-size: 20px; font-weight: 900; color: ${props.accentColor};`,
                            },
                            (props.business?.name || "B").substring(0, 2).toUpperCase()
                          ),
                        ]
                      ),
                  h(
                    "div",
                    {
                      style:
                        "font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;",
                    },
                    props.business?.name || "Your Business"
                  ),
                  h(
                    "div",
                    { style: "font-size: 12px; color: #64748b; margin-top: 2px;" },
                    props.business?.address || ""
                  ),
                  h(
                    "div",
                    { style: "font-size: 12px; color: #64748b;" },
                    props.business?.phone || ""
                  ),
                  h(
                    "div",
                    { style: "font-size: 12px; color: #64748b;" },
                    props.business?.email || ""
                  ),
                ]),
                h("div", { style: "text-align: right;" }, [
                  h(
                    "div",
                    {
                      style: `display: inline-block; padding: 6px 16px; border-radius: 6px; background: ${props.accentBg}; border: 1.5px solid ${props.accentColor}30; margin-bottom: 10px;`,
                    },
                    [
                      h(
                        "span",
                        {
                          style: `font-size: 11px; font-weight: 800; color: ${props.accentColor}; text-transform: uppercase; letter-spacing: 1.5px;`,
                        },
                        props.title
                      ),
                    ]
                  ),
                  h(
                    "div",
                    {
                      style:
                        "font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;",
                    },
                    props.docNumber
                  ),
                  h(
                    "div",
                    { style: "font-size: 12px; color: #64748b; margin-top: 6px;" },
                    [
                      h("span", { style: "font-weight: 600;" }, "Date: "),
                      fmtDate(props.purchase?.purchase_date),
                    ]
                  ),
                  h("div", { style: "font-size: 12px; color: #64748b;" }, [
                    h("span", { style: "font-weight: 600;" }, "Status: "),
                    h(
                      "span",
                      {
                        style: `font-weight: 700; color: ${
                          props.purchase?.payment_status === "paid"
                            ? "#16a34a"
                            : props.purchase?.payment_status === "partial"
                            ? "#d97706"
                            : "#dc2626"
                        };`,
                      },
                      capitalize(props.purchase?.payment_status ?? "unpaid")
                    ),
                  ]),
                ]),
              ]
            ),
          ]
        ),
      ]);
  },
});

const PartyBlock = defineComponent({
  props: ["title", "name", "email", "phone", "address", "accentColor", "accentBg"],
  setup(props) {
    return () =>
      h(
        "div",
        {
          style: `flex: 1; padding: 16px 20px; border: 1.5px solid #e2e8f0; border-left: 4px solid ${props.accentColor}; border-radius: 8px; background: ${props.accentBg};`,
        },
        [
          h(
            "div",
            {
              style:
                "font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px;",
            },
            props.title
          ),
          h(
            "div",
            { style: "font-size: 15px; font-weight: 800; color: #0f172a;" },
            props.name || "—"
          ),
          props.email
            ? h(
                "div",
                { style: "font-size: 12px; color: #64748b; margin-top: 2px;" },
                props.email
              )
            : null,
          props.phone
            ? h("div", { style: "font-size: 12px; color: #64748b;" }, props.phone)
            : null,
          props.address
            ? h("div", { style: "font-size: 12px; color: #64748b;" }, props.address)
            : null,
        ]
      );
  },
});

const ItemsTable = defineComponent({
  props: ["group", "showFinancials", "accentColor", "accentBg", "showBatch"],
  setup(props) {
    return () =>
      h("div", { style: "margin-bottom: 24px;" }, [
        h(
          "div",
          {
            style: `display: flex; align-items: center; gap: 8px; padding: 8px 14px; background: ${props.accentBg}; border-radius: 6px; margin-bottom: 12px; border: 1px solid ${props.accentColor}20;`,
          },
          [
            h("div", {
              style: `width: 8px; height: 8px; border-radius: 50%; background: ${props.accentColor};`,
            }),
            h(
              "span",
              {
                style: `font-size: 12px; font-weight: 800; color: ${props.accentColor};`,
              },
              props.group.branch?.name || "Branch"
            ),
            h(
              "span",
              { style: "font-size: 11px; color: #94a3b8; margin-left: auto;" },
              `${props.group.items.length} item(s)`
            ),
          ]
        ),
        h(
          "table",
          { style: "width: 100%; border-collapse: collapse; font-size: 12px;" },
          [
            h("thead", {}, [
              h(
                "tr",
                { style: `background: ${props.accentColor}; color: #fff;` },
                [
                  h(
                    "th",
                    {
                      style:
                        "padding: 9px 12px; text-align: left; font-weight: 700; width: 30px;",
                    },
                    "#"
                  ),
                  h(
                    "th",
                    { style: "padding: 9px 12px; text-align: left; font-weight: 700;" },
                    "Product"
                  ),
                  h(
                    "th",
                    {
                      style:
                        "padding: 9px 12px; text-align: center; font-weight: 700; width: 60px;",
                    },
                    "Qty"
                  ),
                  props.showBatch
                    ? h(
                        "th",
                        {
                          style: "padding: 9px 12px; text-align: left; font-weight: 700;",
                        },
                        "Batch"
                      )
                    : null,
                  props.showBatch
                    ? h(
                        "th",
                        {
                          style: "padding: 9px 12px; text-align: left; font-weight: 700;",
                        },
                        "Expiry"
                      )
                    : null,
                  props.showFinancials
                    ? h(
                        "th",
                        {
                          style:
                            "padding: 9px 12px; text-align: right; font-weight: 700;",
                        },
                        "Unit Price"
                      )
                    : null,
                  props.showFinancials
                    ? h(
                        "th",
                        {
                          style:
                            "padding: 9px 12px; text-align: right; font-weight: 700; width: 50px;",
                        },
                        "Disc%"
                      )
                    : null,
                  props.showFinancials
                    ? h(
                        "th",
                        {
                          style:
                            "padding: 9px 12px; text-align: right; font-weight: 700; width: 50px;",
                        },
                        "Tax%"
                      )
                    : null,
                  props.showFinancials
                    ? h(
                        "th",
                        {
                          style:
                            "padding: 9px 12px; text-align: right; font-weight: 700;",
                        },
                        "Total"
                      )
                    : null,
                ].filter(Boolean)
              ),
            ]),
            h(
              "tbody",
              {},
              props.group.items.map((item, i) =>
                h(
                  "tr",
                  {
                    style: `background: ${
                      i % 2 === 0 ? "#ffffff" : "#f8fafc"
                    }; border-bottom: 1px solid #f1f5f9;`,
                  },
                  [
                    h(
                      "td",
                      { style: "padding: 9px 12px; color: #94a3b8; font-weight: 700;" },
                      String(i + 1)
                    ),
                    h("td", { style: "padding: 9px 12px;" }, [
                      h(
                        "div",
                        { style: "font-weight: 700; color: #0f172a;" },
                        item.product?.name || "—"
                      ),
                      item.product?.sku
                        ? h(
                            "div",
                            { style: "font-size: 10px; color: #94a3b8;" },
                            `SKU: ${item.product.sku}`
                          )
                        : null,
                    ]),
                    h(
                      "td",
                      {
                        style:
                          "padding: 9px 12px; text-align: center; font-weight: 800; color: #0f172a;",
                      },
                      item.quantity
                    ),
                    props.showBatch
                      ? h(
                          "td",
                          { style: "padding: 9px 12px; color: #475569;" },
                          item.batch_number || "—"
                        )
                      : null,
                    props.showBatch
                      ? h(
                          "td",
                          { style: "padding: 9px 12px; color: #475569;" },
                          item.expiry_date ? fmtDate(item.expiry_date) : "—"
                        )
                      : null,
                    props.showFinancials
                      ? h(
                          "td",
                          {
                            style:
                              "padding: 9px 12px; text-align: right; color: #475569;",
                          },
                          fmtCurrency(item.unit_price)
                        )
                      : null,
                    props.showFinancials
                      ? h(
                          "td",
                          {
                            style:
                              "padding: 9px 12px; text-align: right; color: #ef4444;",
                          },
                          item.discount > 0 ? `${item.discount}%` : "—"
                        )
                      : null,
                    props.showFinancials
                      ? h(
                          "td",
                          {
                            style:
                              "padding: 9px 12px; text-align: right; color: #d97706;",
                          },
                          item.tax > 0 ? `${item.tax}%` : "—"
                        )
                      : null,
                    props.showFinancials
                      ? h(
                          "td",
                          {
                            style:
                              "padding: 9px 12px; text-align: right; font-weight: 800; color: #0f172a;",
                          },
                          fmtCurrency(item.line_total)
                        )
                      : null,
                  ].filter(Boolean)
                )
              )
            ),
            props.showFinancials
              ? h("tfoot", {}, [
                  h(
                    "tr",
                    {
                      style: `background: ${props.accentBg}; border-top: 2px solid ${props.accentColor}30;`,
                    },
                    [
                      h(
                        "td",
                        {
                          colspan: props.showBatch ? 8 : 6,
                          style:
                            "padding: 9px 12px; font-weight: 800; font-size: 12px; text-align: right; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;",
                        },
                        "Branch Subtotal"
                      ),
                      h(
                        "td",
                        {
                          style: `padding: 9px 12px; font-weight: 900; font-size: 13px; text-align: right; color: ${props.accentColor};`,
                        },
                        fmtCurrency(props.group.subtotal)
                      ),
                    ]
                  ),
                ])
              : null,
          ].filter(Boolean)
        ),
      ]);
  },
});

const TotalsBlock = defineComponent({
  props: ["purchase", "accentColor", "accentBg"],
  setup(props) {
    return () =>
      h("div", { style: "display: flex; justify-content: flex-end; margin-top: 8px;" }, [
        h("div", { style: "min-width: 280px;" }, [
          h(
            "div",
            {
              style: "border: 1.5px solid #e2e8f0; border-radius: 8px; overflow: hidden;",
            },
            [
              ...[
                ["Subtotal", fmtCurrency(props.purchase?.subtotal), "#475569"],
                [
                  "Total Discount",
                  `− ${fmtCurrency(props.purchase?.total_discount)}`,
                  "#ef4444",
                ],
                ["Total Tax", `+ ${fmtCurrency(props.purchase?.total_tax)}`, "#d97706"],
              ].map(([label, val, color]) =>
                h(
                  "div",
                  {
                    style:
                      "display: flex; justify-content: space-between; padding: 9px 16px; border-bottom: 1px solid #f1f5f9;",
                  },
                  [
                    h(
                      "span",
                      { style: "font-size: 12px; color: #64748b; font-weight: 600;" },
                      label
                    ),
                    h(
                      "span",
                      { style: `font-size: 12px; font-weight: 700; color: ${color};` },
                      val
                    ),
                  ]
                )
              ),
              h(
                "div",
                {
                  style: `display: flex; justify-content: space-between; padding: 12px 16px; background: ${props.accentColor};`,
                },
                [
                  h(
                    "span",
                    {
                      style:
                        "font-size: 13px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px;",
                    },
                    "Grand Total"
                  ),
                  h(
                    "span",
                    { style: "font-size: 15px; font-weight: 900; color: #fff;" },
                    fmtCurrency(props.purchase?.grand_total)
                  ),
                ]
              ),
              h(
                "div",
                {
                  style:
                    "display: flex; justify-content: space-between; padding: 9px 16px; border-top: 1px solid #f1f5f9;",
                },
                [
                  h(
                    "span",
                    { style: "font-size: 12px; color: #16a34a; font-weight: 600;" },
                    "Amount Paid"
                  ),
                  h(
                    "span",
                    { style: "font-size: 12px; font-weight: 700; color: #16a34a;" },
                    fmtCurrency(props.purchase?.paid_amount)
                  ),
                ]
              ),
              h(
                "div",
                {
                  style: `display: flex; justify-content: space-between; padding: 9px 16px; background: ${
                    (props.purchase?.balance ?? 0) > 0 ? "#fef2f2" : "#f0fdf4"
                  };`,
                },
                [
                  h(
                    "span",
                    {
                      style: `font-size: 12px; font-weight: 700; color: ${
                        (props.purchase?.balance ?? 0) > 0 ? "#dc2626" : "#16a34a"
                      };`,
                    },
                    "Balance Due"
                  ),
                  h(
                    "span",
                    {
                      style: `font-size: 13px; font-weight: 900; color: ${
                        (props.purchase?.balance ?? 0) > 0 ? "#dc2626" : "#16a34a"
                      };`,
                    },
                    fmtCurrency(props.purchase?.balance)
                  ),
                ]
              ),
            ]
          ),
        ]),
      ]);
  },
});

const SignatureBlock = defineComponent({
  props: ["labels"],
  setup(props) {
    return () =>
      h(
        "div",
        { style: "display: flex; gap: 32px; margin-top: 48px; flex-wrap: wrap;" },
        props.labels.map((label) =>
          h("div", { style: "flex: 1; min-width: 160px;" }, [
            h("div", { style: "border-top: 1.5px solid #cbd5e1; padding-top: 8px;" }, [
              h(
                "div",
                {
                  style:
                    "font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px;",
                },
                label
              ),
              h(
                "div",
                { style: "font-size: 11px; color: #94a3b8; margin-top: 2px;" },
                "Name & Date"
              ),
            ]),
          ])
        )
      );
  },
});

const DocFooter = defineComponent({
  props: ["business", "docTitle", "accentColor"],
  setup(props) {
    return () =>
      h(
        "div",
        {
          style:
            "margin-top: 32px; padding-top: 16px; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;",
        },
        [
          h(
            "div",
            { style: "font-size: 11px; color: #94a3b8;" },
            `${props.business?.name || ""} · ${props.docTitle}`
          ),
          h(
            "div",
            { style: "font-size: 11px; color: #94a3b8;" },
            `Printed: ${new Date().toLocaleString("en-KE")}`
          ),
          h(
            "div",
            { style: `font-size: 11px; font-weight: 700; color: ${props.accentColor};` },
            "Powered by BizSystem"
          ),
        ]
      );
  },
});

// ════════════════════════════════════════════════════
// DOCUMENT 1: GOODS RECEIVED NOTE (GRN)
// ════════════════════════════════════════════════════
export const GRNDocument = defineComponent({
  name: "GRNDocument",
  props: ["purchase", "groupedItems", "payments", "business"],
  setup(props) {
    const accent = "#229DE7";
    const accentBg = "#EAF5FD";
    return () =>
      h(
        "div",
        {
          style:
            "font-family: Georgia, serif; color: #1e293b; max-width: 800px; margin: 0 auto;",
        },
        [
          h(DocHeader, {
            business: props.business,
            purchase: props.purchase,
            title: "Goods Received Note",
            docNumber: `GRN-${props.purchase?.reference || props.purchase?.id}`,
            accentColor: accent,
            accentBg,
          }),
          h(
            "div",
            { style: "display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;" },
            [
              h(PartyBlock, {
                title: "Received From (Supplier)",
                name: props.purchase?.supplier?.name,
                email: props.purchase?.supplier?.email,
                phone: props.purchase?.supplier?.phone,
                address: props.purchase?.supplier?.address,
                accentColor: accent,
                accentBg,
              }),
              h(PartyBlock, {
                title: "Received By (Business)",
                name: props.business?.name,
                email: props.business?.email,
                phone: props.business?.phone,
                address: props.business?.address,
                accentColor: accent,
                accentBg,
              }),
            ]
          ),
          h(
            "div",
            {
              style: `display: flex; gap: 12px; margin-bottom: 24px; padding: 14px 18px; background: ${accentBg}; border-radius: 8px; flex-wrap: wrap;`,
            },
            [
              ...[
                ["Purchase Date", fmtDate(props.purchase?.purchase_date)],
                [
                  "Branches",
                  (props.purchase?.branches || []).map((b) => b.name).join(", ") || "—",
                ],
                [
                  "Total Items",
                  (props.groupedItems || []).reduce((s, g) => s + g.items.length, 0),
                ],
                ["Payment Status", capitalize(props.purchase?.payment_status)],
              ].map(([label, val]) =>
                h("div", { style: "flex: 1; min-width: 120px;" }, [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;",
                    },
                    label
                  ),
                  h(
                    "div",
                    {
                      style: `font-size: 13px; font-weight: 800; color: ${accent}; margin-top: 2px;`,
                    },
                    String(val)
                  ),
                ])
              ),
            ]
          ),
          ...(props.groupedItems || []).map((group) =>
            h(ItemsTable, {
              group,
              showFinancials: false,
              showBatch: true,
              accentColor: accent,
              accentBg,
            })
          ),
          h(
            "div",
            {
              style:
                "margin-top: 20px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px 20px;",
            },
            [
              h(
                "div",
                {
                  style:
                    "font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px;",
                },
                "Remarks / Condition of Goods"
              ),
              h(
                "div",
                { style: "min-height: 48px; font-size: 12px; color: #475569;" },
                props.purchase?.notes || "All goods received in good condition."
              ),
            ]
          ),
          h(SignatureBlock, {
            labels: ["Delivered By", "Received By", "Store Manager", "Authorised By"],
          }),
          h(DocFooter, {
            business: props.business,
            docTitle: "Goods Received Note",
            accentColor: accent,
          }),
        ]
      );
  },
});

// ════════════════════════════════════════════════════
// DOCUMENT 2: PURCHASE INVOICE
// ════════════════════════════════════════════════════
export const InvoiceDocument = defineComponent({
  name: "InvoiceDocument",
  props: ["purchase", "groupedItems", "payments", "business"],
  setup(props) {
    const accent = "#1e40af";
    const accentBg = "#EFF6FF";
    return () =>
      h(
        "div",
        {
          style:
            'font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #1e293b; max-width: 800px; margin: 0 auto;',
        },
        [
          h(DocHeader, {
            business: props.business,
            purchase: props.purchase,
            title: "Purchase Invoice",
            docNumber: `INV-${props.purchase?.reference || props.purchase?.id}`,
            accentColor: accent,
            accentBg,
          }),
          h(
            "div",
            { style: "display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;" },
            [
              h(PartyBlock, {
                title: "Invoice From (Supplier)",
                name: props.purchase?.supplier?.name,
                email: props.purchase?.supplier?.email,
                phone: props.purchase?.supplier?.phone,
                address: props.purchase?.supplier?.address,
                accentColor: accent,
                accentBg,
              }),
              h(PartyBlock, {
                title: "Bill To (Buyer)",
                name: props.business?.name,
                email: props.business?.email,
                phone: props.business?.phone,
                address: props.business?.address,
                accentColor: accent,
                accentBg,
              }),
            ]
          ),
          h(
            "div",
            {
              style: `display: flex; gap: 12px; margin-bottom: 24px; padding: 14px 18px; background: ${accentBg}; border-radius: 8px; flex-wrap: wrap;`,
            },
            [
              ...[
                ["Invoice Date", fmtDate(props.purchase?.purchase_date)],
                ["Invoice No.", `INV-${props.purchase?.reference || props.purchase?.id}`],
                ["Payment Terms", capitalize(props.purchase?.payment_type)],
                ["Payment Method", capitalize(props.purchase?.payment_method ?? "N/A")],
                ["Payment Ref.", props.purchase?.payment_reference || "—"],
              ].map(([label, val]) =>
                h("div", { style: "flex: 1; min-width: 100px;" }, [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;",
                    },
                    label
                  ),
                  h(
                    "div",
                    {
                      style: `font-size: 13px; font-weight: 800; color: ${accent}; margin-top: 2px;`,
                    },
                    val
                  ),
                ])
              ),
            ]
          ),
          ...(props.groupedItems || []).map((group) =>
            h(ItemsTable, {
              group,
              showFinancials: true,
              showBatch: false,
              accentColor: accent,
              accentBg,
            })
          ),
          h(TotalsBlock, { purchase: props.purchase, accentColor: accent, accentBg }),
          (props.payments || []).length > 0
            ? h("div", { style: "margin-top: 28px;" }, [
                h(
                  "div",
                  {
                    style:
                      "font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 10px;",
                  },
                  "Payment History"
                ),
                h(
                  "table",
                  { style: "width: 100%; border-collapse: collapse; font-size: 12px;" },
                  [
                    h("thead", {}, [
                      h("tr", { style: `background: ${accent}; color: #fff;` }, [
                        h(
                          "th",
                          {
                            style:
                              "padding: 8px 12px; text-align: left; font-weight: 700;",
                          },
                          "Date"
                        ),
                        h(
                          "th",
                          {
                            style:
                              "padding: 8px 12px; text-align: left; font-weight: 700;",
                          },
                          "Method"
                        ),
                        h(
                          "th",
                          {
                            style:
                              "padding: 8px 12px; text-align: left; font-weight: 700;",
                          },
                          "Reference"
                        ),
                        h(
                          "th",
                          {
                            style:
                              "padding: 8px 12px; text-align: right; font-weight: 700;",
                          },
                          "Amount"
                        ),
                      ]),
                    ]),
                    h(
                      "tbody",
                      {},
                      (props.payments || []).map((p, i) =>
                        h(
                          "tr",
                          {
                            style: `background: ${
                              i % 2 === 0 ? "#fff" : accentBg
                            }; border-bottom: 1px solid #e2e8f0;`,
                          },
                          [
                            h(
                              "td",
                              { style: "padding: 8px 12px; color: #475569;" },
                              fmtDate(p.payment_date)
                            ),
                            h(
                              "td",
                              { style: "padding: 8px 12px; color: #475569;" },
                              capitalize(p.payment_method)
                            ),
                            h(
                              "td",
                              { style: "padding: 8px 12px; color: #475569;" },
                              p.payment_reference || "—"
                            ),
                            h(
                              "td",
                              {
                                style:
                                  "padding: 8px 12px; text-align: right; font-weight: 800; color: #16a34a;",
                              },
                              fmtCurrency(p.amount)
                            ),
                          ]
                        )
                      )
                    ),
                  ]
                ),
              ])
            : null,
          props.purchase?.notes
            ? h(
                "div",
                {
                  style:
                    "margin-top: 20px; padding: 14px 18px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;",
                },
                [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #92400e; margin-bottom: 4px;",
                    },
                    "Notes"
                  ),
                  h(
                    "div",
                    { style: "font-size: 12px; color: #78350f;" },
                    props.purchase.notes
                  ),
                ]
              )
            : null,
          h(SignatureBlock, {
            labels: ["Supplier Signature", "Authorised Signatory", "Date"],
          }),
          h(DocFooter, {
            business: props.business,
            docTitle: "Purchase Invoice",
            accentColor: accent,
          }),
        ]
      );
  },
});

// ════════════════════════════════════════════════════
// DOCUMENT 3: DELIVERY NOTE
// ════════════════════════════════════════════════════
export const DeliveryDocument = defineComponent({
  name: "DeliveryDocument",
  props: ["purchase", "groupedItems", "payments", "business"],
  setup(props) {
    const accent = "#059669";
    const accentBg = "#ECFDF5";
    return () =>
      h(
        "div",
        {
          style:
            "font-family: system-ui, sans-serif; color: #1e293b; max-width: 800px; margin: 0 auto;",
        },
        [
          h(DocHeader, {
            business: props.business,
            purchase: props.purchase,
            title: "Delivery Note",
            docNumber: `DN-${props.purchase?.reference || props.purchase?.id}`,
            accentColor: accent,
            accentBg,
          }),
          h(
            "div",
            { style: "display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;" },
            [
              h(PartyBlock, {
                title: "Dispatched By (Supplier)",
                name: props.purchase?.supplier?.name,
                email: props.purchase?.supplier?.email,
                phone: props.purchase?.supplier?.phone,
                address: props.purchase?.supplier?.address,
                accentColor: accent,
                accentBg,
              }),
              h(PartyBlock, {
                title: "Delivered To (Business)",
                name: props.business?.name,
                email: props.business?.email,
                phone: props.business?.phone,
                address: props.business?.address,
                accentColor: accent,
                accentBg,
              }),
            ]
          ),
          h(
            "div",
            {
              style: `display: flex; gap: 12px; margin-bottom: 24px; padding: 14px 18px; background: ${accentBg}; border-radius: 8px; flex-wrap: wrap;`,
            },
            [
              ...[
                ["Delivery Date", fmtDate(props.purchase?.purchase_date)],
                ["Order Ref.", props.purchase?.reference || "—"],
                [
                  "Delivery To",
                  (props.purchase?.branches || []).map((b) => b.name).join(", ") || "—",
                ],
                [
                  "Total Packages",
                  (props.groupedItems || []).reduce((s, g) => s + g.items.length, 0),
                ],
              ].map(([label, val]) =>
                h("div", { style: "flex: 1; min-width: 120px;" }, [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;",
                    },
                    label
                  ),
                  h(
                    "div",
                    {
                      style: `font-size: 13px; font-weight: 800; color: ${accent}; margin-top: 2px;`,
                    },
                    String(val)
                  ),
                ])
              ),
            ]
          ),
          ...(props.groupedItems || []).map((group) =>
            h(ItemsTable, {
              group,
              showFinancials: false,
              showBatch: true,
              accentColor: accent,
              accentBg,
            })
          ),
          h(
            "div",
            { style: "display: flex; gap: 16px; margin-top: 20px; flex-wrap: wrap;" },
            [
              h(
                "div",
                {
                  style:
                    "flex: 2; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px 18px;",
                },
                [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px;",
                    },
                    "Special Instructions / Notes"
                  ),
                  h(
                    "div",
                    { style: "min-height: 44px; font-size: 12px; color: #475569;" },
                    props.purchase?.notes || "—"
                  ),
                ]
              ),
              h(
                "div",
                {
                  style:
                    "flex: 1; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px 18px;",
                },
                [
                  h(
                    "div",
                    {
                      style:
                        "font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px;",
                    },
                    "Condition of Goods"
                  ),
                  h(
                    "div",
                    { style: "display: flex; flex-direction: column; gap: 8px;" },
                    ["Good", "Damaged", "Partial"].map((opt) =>
                      h(
                        "label",
                        {
                          style:
                            "display: flex; align-items: center; gap: 8px; font-size: 12px; color: #475569;",
                        },
                        [
                          h("div", {
                            style:
                              "width: 16px; height: 16px; border: 1.5px solid #cbd5e1; border-radius: 3px; flex-shrink: 0;",
                          }),
                          opt,
                        ]
                      )
                    )
                  ),
                ]
              ),
            ]
          ),
          h(SignatureBlock, {
            labels: ["Driver / Courier", "Received By", "Date & Time", "Stamp / Seal"],
          }),
          h(DocFooter, {
            business: props.business,
            docTitle: "Delivery Note",
            accentColor: accent,
          }),
        ]
      );
  },
});
</script>
