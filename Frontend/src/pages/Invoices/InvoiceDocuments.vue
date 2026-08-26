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
      Back to Invoice
    </button>

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Invoice Documents
        </h1>
        <p class="text-slate-500 mt-1 text-sm">
          {{ invoice?.invoice_number }} &middot; {{ invoice?.customer?.name }} &middot;
          {{ formatDate(invoice?.issue_date) }}
        </p>
      </div>

      <!-- Doc type selector -->
      <div class="flex gap-1 bg-white border border-[#D9EBF7] rounded-xl p-1 shadow-sm">
        <button
          v-for="doc in docTypes"
          :key="doc.key"
          @click="switchDoc(doc.key)"
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

    <!-- ACTION BUTTONS -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <button
        @click="printFrame"
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
      <div class="ml-auto flex items-center gap-2 text-xs text-slate-400 font-semibold">
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
        Print → "Save as PDF" to download
      </div>
    </div>

    <!-- PREVIEW CARD -->
    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-lg overflow-hidden">
      <!-- Preview label bar -->
      <div
        class="px-6 py-3 bg-[#EAF5FD] border-b border-[#B3DEFA] flex items-center justify-between"
      >
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#229DE7] animate-pulse"></span>
          <span class="text-xs font-bold text-[#1682C4] uppercase tracking-widest"
            >Preview · {{ currentDocLabel }}</span
          >
        </div>
        <span class="text-[10px] text-slate-400 font-semibold">A4 · KES</span>
      </div>

      <!-- Skeleton while loading -->
      <div v-if="pageLoading" class="p-8 space-y-4">
        <div class="flex justify-between items-start gap-6">
          <div class="space-y-2 flex-1">
            <div class="h-8 w-32 bg-slate-100 animate-pulse rounded-lg"></div>
            <div class="h-4 w-48 bg-slate-100 animate-pulse rounded"></div>
            <div class="h-3 w-36 bg-slate-100 animate-pulse rounded"></div>
          </div>
          <div class="space-y-2 text-right">
            <div class="h-6 w-24 bg-slate-100 animate-pulse rounded ml-auto"></div>
            <div class="h-8 w-40 bg-slate-100 animate-pulse rounded ml-auto"></div>
          </div>
        </div>
        <div class="h-px bg-slate-100 my-4"></div>
        <div class="flex gap-4">
          <div class="flex-1 h-20 bg-slate-50 animate-pulse rounded-xl"></div>
          <div class="flex-1 h-20 bg-slate-50 animate-pulse rounded-xl"></div>
        </div>
        <div class="h-40 bg-slate-50 animate-pulse rounded-xl mt-4"></div>
        <div class="flex justify-end">
          <div class="w-56 h-28 bg-slate-50 animate-pulse rounded-xl"></div>
        </div>
      </div>

      <!-- IFRAME — isolated rendering context, no style bleed -->
      <iframe
        v-else
        ref="docFrame"
        class="w-full border-0 bg-white"
        style="min-height: 900px"
        title="Document Preview"
        sandbox="allow-same-origin allow-scripts"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import InvoiceService from "@/services/InvoiceService";
import { useAuthStore } from "@/stores/auth";

const route = useRoute();
const invoiceId = route.params.id;
const auth = useAuthStore();

/* ─── STATE ───────────────────────────── */
const pageLoading = ref(true);
const invoice = ref(null);
const payments = ref([]);
const activeDoc = ref("invoice");
const docFrame = ref(null);

// ── Business info from auth store ──
const BASE_URL = import.meta.env.VITE_APP_URL ?? "";
const resolvedLogo = (() => {
  const raw = (auth.user?.business?.logo ?? "").trim();
  if (!raw) return null;
  return /^(https?:\/\/|data:)/i.test(raw) ? raw : `${BASE_URL}/storage/${raw}`;
})();

const business = {
  name: auth.user?.business?.name || "Bizaa",
  tagline: auth.user?.business?.industry || "Quality · Reliability · Excellence",
  address: auth.user?.business?.location || "",
  phone: auth.user?.business?.phone || "",
  email: auth.user?.business?.email || "",
  website: auth.user?.business?.website || "",
  logo: resolvedLogo,
};

const docTypes = [
  { key: "invoice", label: "Tax Invoice" },
  { key: "receipt", label: "Payment Receipt" },
  { key: "statement", label: "Account Statement" },
];

const currentDocLabel = computed(
  () => docTypes.find((d) => d.key === activeDoc.value)?.label ?? ""
);

/* ─── HELPERS ─────────────────────────── */
const fmt = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

const fmtDate = (val) => {
  if (!val) return "—";
  return new Date(val).toLocaleDateString("en-KE", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

const formatDate = fmtDate;

const cap = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : "—");

/**
 * These documents are built as a raw HTML string and injected via
 * doc.write() (see writeToFrame below) rather than Vue's auto-escaping
 * template syntax, so every value that originates from user input —
 * customer/business name, product name/SKU, item description, invoice
 * notes, payment reference, branch name — must be escaped by hand before
 * interpolation. Skipping this on any one of them is a stored XSS: e.g. a
 * product or customer named `<img src=x onerror=...>` would execute
 * inside the iframe the moment anyone opens this invoice's documents.
 */
const esc = (val) => {
  if (val === null || val === undefined) return "";
  return String(val)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
};

const isOverdue = computed(
  () =>
    invoice.value?.payment_status !== "paid" &&
    invoice.value?.due_date &&
    new Date(invoice.value.due_date) < new Date()
);

const statusColors = computed(() => {
  const s = invoice.value?.payment_status;
  if (s === "paid") return { bg: "#dcfce7", text: "#15803d", border: "#86efac" };
  if (s === "partial") return { bg: "#fef9c3", text: "#854d0e", border: "#fde047" };
  return { bg: "#fee2e2", text: "#b91c1c", border: "#fca5a5" };
});

const totalPaid = computed(() =>
  payments.value.reduce((s, p) => s + Number(p.amount ?? 0), 0)
);

/* ═══════════════════════════════════════════════════════════
   BASE CSS injected into every iframe document
═══════════════════════════════════════════════════════════ */
const BASE_CSS = `
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body {
    font-family: 'Georgia', 'Times New Roman', Times, serif;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.55;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  table { width: 100%; border-collapse: collapse; }
  th, td { vertical-align: middle; }
  .doc { max-width: 780px; margin: 0 auto; padding: 36px 40px; background: #fff; }

  /* ── Reusable doc atoms ── */
  .accent-bar   { height: 5px; border-radius: 3px 3px 0 0; }
  .section-label{
    font-size: 9px; font-weight: 800; text-transform: uppercase;
    letter-spacing: 2px; color: #94a3b8; margin-bottom: 10px;
  }
  .party-box {
    flex: 1; min-width: 190px; padding: 14px 18px;
    border: 1.5px solid #e2e8f0; border-radius: 8px; background: #f8fafc;
  }
  .party-box .label {
    font-size: 9px; font-weight: 800; text-transform: uppercase;
    letter-spacing: 2px; color: #94a3b8; margin-bottom: 7px;
  }
  .party-box .name  { font-size: 14px; font-weight: 800; color: #0f172a; }
  .party-box .sub   { font-size: 11.5px; color: #64748b; margin-top: 2px; line-height: 1.6; }

  .meta-strip {
    display: flex; flex-wrap: wrap; gap: 8px;
    padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;
  }
  .meta-item .ml  { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; }
  .meta-item .mv  { font-size: 13px; font-weight: 800; margin-top: 2px; }

  .items-table thead tr th {
    padding: 10px 12px; font-size: 10.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px; color: #fff;
  }
  .items-table tbody tr td {
    padding: 10px 12px; font-size: 12px; border-bottom: 1px solid #f1f5f9;
  }
  .items-table tfoot tr td {
    padding: 10px 12px; font-size: 12px;
  }

  .totals-box { min-width: 272px; border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
  .totals-row { display: flex; justify-content: space-between; padding: 9px 16px; }
  .totals-row + .totals-row { border-top: 1px solid #f1f5f9; }
  .totals-label { font-size: 12px; color: #64748b; font-weight: 600; }
  .totals-value { font-size: 12px; font-weight: 700; }
  .totals-grand { padding: 12px 16px; display: flex; justify-content: space-between; }
  .totals-grand-l { font-size: 13px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }
  .totals-grand-r { font-size: 15px; font-weight: 900; color: #fff; }

  .sig-block { display: flex; gap: 28px; margin-top: 36px; flex-wrap: wrap; }
  .sig-line   { flex: 1; min-width: 130px; border-top: 1.5px solid #cbd5e1; padding-top: 7px; }
  .sig-label  { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; }

  .doc-footer {
    margin-top: 28px; padding-top: 14px; border-top: 1.5px solid #e2e8f0;
    display: flex; justify-content: space-between; flex-wrap: wrap;
    gap: 6px; font-size: 10.5px; color: #94a3b8;
  }
  .doc-footer strong { font-weight: 700; }

  .badge {
    display: inline-block; padding: 2px 10px; border-radius: 20px;
    font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
  }

  .notes-box {
    margin-bottom: 22px; padding: 13px 17px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
  }
  .notes-box .nl { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #92400e; margin-bottom: 4px; }
  .notes-box .nv { font-size: 12px; color: #78350f; line-height: 1.6; }

  @media print {
    html, body { margin: 0; padding: 0; }
    .doc       { padding: 0; max-width: 100%; }
    tr { page-break-inside: avoid; }
    @page { margin: 18mm 16mm; size: A4; }
  }
`;

/* ═══════════════════════════════════════════════════════════
   SHARED BUILDERS
═══════════════════════════════════════════════════════════ */

const buildDocHeader = (docTitle, docNumber, accent) => `
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;padding-bottom:24px;border-bottom:3px solid ${accent};margin-bottom:26px;">

    <!-- Business block -->
    <div>
      <div style="width:54px;height:54px;border-radius:12px;background:${accent}18;border:2px solid ${accent}44;
        display:flex;align-items:center;justify-content:center;margin-bottom:12px;overflow:hidden;">
        ${
          business.logo
            ? `<img src="${esc(business.logo)}" style="width:100%;height:100%;object-fit:contain;" />`
            : `<span style="font-size:20px;font-weight:900;color:${accent};">${esc(
                (business.name || "B").substring(0, 2).toUpperCase()
              )}</span>`
        }
      </div>
      <div style="font-size:19px;font-weight:900;color:#0f172a;letter-spacing:-0.3px;">${esc(
        business.name
      )}</div>
      <div style="font-size:11px;color:#94a3b8;font-style:italic;margin-top:1px;">${esc(
        business.tagline
      )}</div>
      <div style="font-size:11.5px;color:#64748b;margin-top:9px;line-height:1.8;">
        ${esc(business.address)}<br>
        ${esc(business.phone)} &nbsp;&middot;&nbsp; ${esc(business.email)}<br>
        PIN: ${esc(business.pin)}
      </div>
    </div>

    <!-- Doc meta block -->
    <div style="text-align:right;">
      <div style="display:inline-block;padding:4px 14px;border-radius:6px;
        background:${accent}18;border:1.5px solid ${accent}33;margin-bottom:10px;">
        <span style="font-size:10px;font-weight:800;color:${accent};text-transform:uppercase;letter-spacing:2px;">${docTitle}</span>
      </div>
      <div style="font-size:26px;font-weight:900;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">${esc(docNumber)}</div>
      <div style="font-size:12px;color:#64748b;margin-top:10px;line-height:1.9;">
        <span style="font-weight:700;color:#475569;">Issued:</span> ${fmtDate(
          invoice.value?.issue_date
        )}<br>
        <span style="font-weight:700;color:#475569;">Due:</span>
        <span style="color:${isOverdue.value ? "#dc2626" : "#475569"};font-weight:${
  isOverdue.value ? 800 : 400
};">
          ${fmtDate(invoice.value?.due_date)}${
  isOverdue.value ? ' &nbsp;<b style="color:#dc2626;">⚠ OVERDUE</b>' : ""
}
        </span><br>
        <span style="font-weight:700;color:#475569;">Status:</span>
        <span class="badge" style="background:${statusColors.value.bg};color:${
  statusColors.value.text
};border:1px solid ${statusColors.value.border};margin-left:4px;">
          ${cap(invoice.value?.payment_status ?? "unpaid")}
        </span>
      </div>
    </div>
  </div>
`;

const buildPartyRow = (accent) => `
  <div style="display:flex;gap:14px;margin-bottom:26px;flex-wrap:wrap;">
    <div class="party-box" style="border-left:4px solid ${accent};">
      <div class="label">Bill To</div>
      <div class="name">${esc(invoice.value?.customer?.name) || "—"}</div>
      <div class="sub">
        ${invoice.value?.customer?.email ? esc(invoice.value.customer.email) + "<br>" : ""}
        ${esc(invoice.value?.customer?.phone)}
      </div>
    </div>
    ${
      invoice.value?.branch
        ? `
    <div class="party-box" style="border-left:4px solid ${accent};">
      <div class="label">Branch</div>
      <div class="name">${esc(invoice.value.branch.name)}</div>
    </div>`
        : ""
    }
    ${
      invoice.value?.quotation
        ? `
    <div class="party-box" style="border-left:4px solid #f59e0b;background:#fffbeb;">
      <div class="label">From Quotation</div>
      <div class="name" style="color:#d97706;">${esc(invoice.value.quotation.quotation_number)}</div>
    </div>`
        : ""
    }
  </div>
`;

const buildItemsTable = (accent) => {
  const rows = (invoice.value?.items || [])
    .map(
      (item, i) => `
    <tr style="background:${i % 2 === 0 ? "#fff" : "#f8fafc"};">
      <td style="color:#94a3b8;font-weight:700;width:30px;">${i + 1}</td>
      <td>
        <div style="font-weight:700;color:#0f172a;">${esc(item.product?.name) || "—"}</div>
        ${
          item.product?.sku
            ? `<div style="font-size:10px;color:#94a3b8;">SKU: ${esc(item.product.sku)}</div>`
            : ""
        }
      </td>
      <td style="color:#64748b;font-style:italic;max-width:150px;">${
        esc(item.description) || "—"
      }</td>
      <td style="text-align:center;font-weight:800;color:#0f172a;">${item.quantity}</td>
      <td style="text-align:right;color:#475569;">${fmt(item.unit_price)}</td>
      <td style="text-align:right;color:${item.discount > 0 ? "#dc2626" : "#cbd5e1"};">${
        item.discount > 0 ? item.discount + "%" : "—"
      }</td>
      <td style="text-align:right;color:${item.tax > 0 ? "#d97706" : "#cbd5e1"};">${
        item.tax > 0 ? item.tax + "%" : "—"
      }</td>
      <td style="text-align:right;font-weight:800;color:#0f172a;">${fmt(
        item.line_total
      )}</td>
    </tr>
  `
    )
    .join("");

  return `
    <table class="items-table" style="margin-bottom:22px;">
      <thead>
        <tr style="background:${accent};">
          <th style="text-align:left;width:30px;">#</th>
          <th style="text-align:left;">Item / Service</th>
          <th style="text-align:left;">Description</th>
          <th style="text-align:center;width:55px;">Qty</th>
          <th style="text-align:right;">Unit Price</th>
          <th style="text-align:right;width:55px;">Disc%</th>
          <th style="text-align:right;width:55px;">Tax%</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  `;
};

const buildTotals = (accent) => `
  <div style="display:flex;justify-content:flex-end;margin-bottom:28px;">
    <div class="totals-box">
      <div class="totals-row"><span class="totals-label">Subtotal</span><span class="totals-value" style="color:#475569;">${fmt(
        invoice.value?.subtotal
      )}</span></div>
      <div class="totals-row" style="border-top:1px solid #f1f5f9;"><span class="totals-label">Total Discount</span><span class="totals-value" style="color:#dc2626;">− ${fmt(
        invoice.value?.total_discount
      )}</span></div>
      <div class="totals-row" style="border-top:1px solid #f1f5f9;"><span class="totals-label">Total Tax (VAT)</span><span class="totals-value" style="color:#d97706;">+ ${fmt(
        invoice.value?.total_tax
      )}</span></div>
      <div class="totals-grand" style="background:${accent};border-top:1px solid ${accent};">
        <span class="totals-grand-l">Grand Total</span><span class="totals-grand-r">${fmt(
          invoice.value?.grand_total
        )}</span>
      </div>
      <div class="totals-row" style="background:#f0fdf4;border-top:1px solid #86efac;">
        <span class="totals-label" style="color:#15803d;">Amount Paid</span>
        <span class="totals-value" style="color:#15803d;">${fmt(
          invoice.value?.paid_amount
        )}</span>
      </div>
      <div class="totals-row" style="background:${
        (invoice.value?.balance ?? 0) > 0 ? "#fef2f2" : "#f0fdf4"
      };border-top:1px solid ${
  (invoice.value?.balance ?? 0) > 0 ? "#fca5a5" : "#86efac"
};">
        <span class="totals-label" style="font-weight:800;font-size:13px;color:${
          (invoice.value?.balance ?? 0) > 0 ? "#b91c1c" : "#15803d"
        };">Balance Due</span>
        <span class="totals-value" style="font-size:15px;color:${
          (invoice.value?.balance ?? 0) > 0 ? "#b91c1c" : "#15803d"
        };">${fmt(invoice.value?.balance)}</span>
      </div>
    </div>
  </div>
`;

const buildNotesBox = () =>
  invoice.value?.notes
    ? `
  <div class="notes-box">
    <div class="nl">Notes / Terms &amp; Conditions</div>
    <div class="nv">${esc(invoice.value.notes)}</div>
  </div>`
    : "";

const buildDocFooter = (docTitle, accent) => `
  <div class="doc-footer">
    <span>${esc(business.name)} &middot; ${docTitle}</span>
    <span>Printed: ${new Date().toLocaleString("en-KE")}</span>
    <span style="color:${accent};font-weight:700;">${esc(business.website)}</span>
  </div>
`;

/* ═══════════════════════════════════════════════════════════
   DOCUMENT 1 — TAX INVOICE
═══════════════════════════════════════════════════════════ */
const buildInvoiceDoc = () => {
  const accent = "#229DE7";
  return `<!DOCTYPE html><html><head><meta charset="utf-8">
  <title>${esc(invoice.value?.invoice_number)}</title>
  <style>${BASE_CSS}</style></head><body>
  <div class="doc">
    ${buildDocHeader("Tax Invoice", invoice.value?.invoice_number ?? "—", accent)}
    ${buildPartyRow(accent)}
    ${buildItemsTable(accent)}
    ${buildTotals(accent)}

    <!-- Payment instructions -->
    <div style="display:flex;gap:14px;margin-bottom:26px;flex-wrap:wrap;">
      <div style="flex:2;min-width:200px;padding:16px 20px;border:1.5px solid #e2e8f0;border-radius:8px;background:#f8fafc;">
        <div class="section-label">Payment Instructions</div>
        <div style="font-size:12px;color:#475569;line-height:1.9;">
          <strong>M-Pesa Paybill:</strong> 123456 &nbsp;&middot;&nbsp; <strong>Account No:</strong> ${esc(
            invoice.value?.invoice_number
          )}<br>
          <strong>Bank:</strong> Your Bank Name &nbsp;&middot;&nbsp; <strong>A/C No:</strong> 1234567890<br>
          <strong>Branch:</strong> Nairobi &nbsp;&middot;&nbsp; <strong>Swift Code:</strong> ABCDKENX
        </div>
      </div>
      <div style="flex:1;min-width:160px;padding:16px 20px;border:1.5px solid #e2e8f0;border-radius:8px;background:#f8fafc;">
        <div class="section-label">Authorised Signatory</div>
        <div style="border-top:1.5px solid #cbd5e1;margin-top:44px;padding-top:7px;">
          <div style="font-size:10.5px;color:#94a3b8;">Signature &amp; Company Stamp</div>
        </div>
      </div>
    </div>

    ${buildNotesBox()}

    <div class="sig-block">
      <div class="sig-line"><div class="sig-label">Prepared By</div></div>
      <div class="sig-line"><div class="sig-label">Approved By</div></div>
      <div class="sig-line"><div class="sig-label">Customer Signature</div></div>
    </div>

    ${buildDocFooter("Tax Invoice", accent)}
  </div>
  </body></html>`;
};

/* ═══════════════════════════════════════════════════════════
   DOCUMENT 2 — PAYMENT RECEIPT
═══════════════════════════════════════════════════════════ */
const buildReceiptDoc = () => {
  const accent = "#059669";

  const payRows = payments.value
    .map(
      (p, i) => `
    <tr style="background:${i % 2 === 0 ? "#fff" : "#f0fdf4"};">
      <td style="color:#94a3b8;font-weight:700;">${i + 1}</td>
      <td style="font-weight:600;color:#475569;">${fmtDate(p.payment_date)}</td>
      <td>
        <span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">
          ${esc(cap(p.payment_method ?? "Cash"))}
        </span>
      </td>
      <td style="color:#475569;">${esc(p.payment_reference) || "—"}</td>
      <td style="color:#475569;">${esc(p.user?.name) || "—"}</td>
      <td style="text-align:right;font-weight:800;color:#065f46;font-size:13px;">${fmt(
        p.amount
      )}</td>
    </tr>
  `
    )
    .join("");

  return `<!DOCTYPE html><html><head><meta charset="utf-8">
  <title>Receipt - ${esc(invoice.value?.invoice_number)}</title>
  <style>${BASE_CSS}</style></head><body>
  <div class="doc">
    ${buildDocHeader(
      "Payment Receipt",
      `RCP-${invoice.value?.invoice_number ?? "—"}`,
      accent
    )}

    <!-- Summary meta strip -->
    <div class="meta-strip" style="background:${accent}0f;border:1.5px solid ${accent}33;">
      ${[
        ["Invoice No.", esc(invoice.value?.invoice_number) || "—"],
        ["Customer", esc(invoice.value?.customer?.name) || "—"],
        ["Invoice Total", fmt(invoice.value?.grand_total)],
        ["Amount Paid", fmt(invoice.value?.paid_amount)],
        ["Balance", fmt(invoice.value?.balance)],
      ]
        .map(
          ([l, v]) => `
        <div class="meta-item" style="flex:1;min-width:110px;">
          <div class="ml">${l}</div>
          <div class="mv" style="color:${accent};">${v}</div>
        </div>
      `
        )
        .join("")}
    </div>

    <!-- Payment history -->
    <div class="section-label">Payment History</div>
    ${
      payments.value.length === 0
        ? `
      <div style="padding:28px;text-align:center;border:1.5px dashed #a7f3d0;border-radius:8px;color:#94a3b8;font-size:13px;margin-bottom:22px;">
        No payments have been recorded for this invoice.
      </div>
    `
        : `
      <table class="items-table" style="margin-bottom:22px;">
        <thead>
          <tr style="background:${accent};">
            <th style="text-align:left;width:30px;">#</th>
            <th style="text-align:left;">Date</th>
            <th style="text-align:left;">Method</th>
            <th style="text-align:left;">Reference</th>
            <th style="text-align:left;">Received By</th>
            <th style="text-align:right;">Amount</th>
          </tr>
        </thead>
        <tbody>${payRows}</tbody>
        <tfoot>
          <tr style="background:${accent}18;border-top:2px solid ${accent}44;">
            <td colspan="5" style="font-weight:800;font-size:12px;text-align:right;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Total Received</td>
            <td style="font-weight:900;font-size:15px;text-align:right;color:${accent};">${fmt(
            totalPaid.value
          )}</td>
          </tr>
        </tfoot>
      </table>
    `
    }

    <!-- Balance status box -->
    ${
      (invoice.value?.balance ?? 0) > 0
        ? `
      <div style="padding:14px 18px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:8px;margin-bottom:24px;
        display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:13px;font-weight:700;color:#b91c1c;">Outstanding Balance</span>
        <span style="font-size:18px;font-weight:900;color:#b91c1c;">${fmt(
          invoice.value.balance
        )}</span>
      </div>
    `
        : `
      <div style="padding:14px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;margin-bottom:24px;
        display:flex;align-items:center;gap:10px;">
        <span style="font-size:20px;">✓</span>
        <span style="font-size:13px;font-weight:800;color:#15803d;">Invoice Fully Paid — Thank You!</span>
      </div>
    `
    }

    ${buildNotesBox()}

    <div class="sig-block">
      <div class="sig-line"><div class="sig-label">Received By</div></div>
      <div class="sig-line"><div class="sig-label">Cashier / Accountant</div></div>
      <div class="sig-line"><div class="sig-label">Date &amp; Stamp</div></div>
    </div>

    ${buildDocFooter("Payment Receipt", accent)}
  </div>
  </body></html>`;
};

/* ═══════════════════════════════════════════════════════════
   DOCUMENT 3 — ACCOUNT STATEMENT
═══════════════════════════════════════════════════════════ */
const buildStatementDoc = () => {
  const accent = "#7c3aed";

  // Build running ledger
  const ledger = [
    {
      date: invoice.value?.issue_date,
      type: "Invoice",
      ref: invoice.value?.invoice_number,
      debit: invoice.value?.grand_total ?? 0,
      credit: 0,
    },
    ...payments.value.map((p) => ({
      date: p.payment_date,
      type: "Payment",
      ref: p.payment_reference || cap(p.payment_method) + " Payment",
      debit: 0,
      credit: Number(p.amount ?? 0),
    })),
  ].sort((a, b) => new Date(a.date) - new Date(b.date));

  let running = 0;
  const ledgerRows = ledger
    .map((e, i) => {
      running += Number(e.debit) - Number(e.credit);
      const isInv = e.type === "Invoice";
      return `
      <tr style="background:${i % 2 === 0 ? "#fff" : "#faf5ff"};">
        <td style="white-space:nowrap;font-weight:600;color:#475569;">${fmtDate(
          e.date
        )}</td>
        <td>
          <span class="badge" style="background:${isInv ? "#ede9fe" : "#dcfce7"};color:${
        isInv ? "#6d28d9" : "#15803d"
      };border:1px solid ${isInv ? "#c4b5fd" : "#86efac"};">
            ${e.type}
          </span>
        </td>
        <td style="color:#475569;">${esc(e.ref)}</td>
        <td style="text-align:right;font-weight:700;color:${
          e.debit > 0 ? "#b91c1c" : "#cbd5e1"
        };">${e.debit > 0 ? fmt(e.debit) : "—"}</td>
        <td style="text-align:right;font-weight:700;color:${
          e.credit > 0 ? "#15803d" : "#cbd5e1"
        };">${e.credit > 0 ? fmt(e.credit) : "—"}</td>
        <td style="text-align:right;font-weight:800;color:${
          running > 0 ? "#b91c1c" : "#15803d"
        };">
          ${fmt(Math.abs(running))} <span style="font-size:10px;">${
        running > 0 ? "DR" : "CR"
      }</span>
        </td>
      </tr>
    `;
    })
    .join("");

  return `<!DOCTYPE html><html><head><meta charset="utf-8">
  <title>Statement - ${esc(invoice.value?.invoice_number)}</title>
  <style>${BASE_CSS}</style></head><body>
  <div class="doc">
    ${buildDocHeader(
      "Account Statement",
      `STMT-${invoice.value?.invoice_number ?? "—"}`,
      accent
    )}

    <!-- Statement period strip -->
    <div class="meta-strip" style="background:${accent}0f;border:1.5px solid ${accent}30;">
      ${[
        ["Customer", esc(invoice.value?.customer?.name) || "—"],
        ["Statement Date", fmtDate(new Date().toISOString())],
        ["Invoice Date", fmtDate(invoice.value?.issue_date)],
        ["Due Date", fmtDate(invoice.value?.due_date)],
        ["Currency", invoice.value?.currency ?? "KES"],
      ]
        .map(
          ([l, v]) => `
        <div class="meta-item" style="flex:1;min-width:110px;">
          <div class="ml">${l}</div>
          <div class="mv" style="color:${accent};">${v}</div>
        </div>
      `
        )
        .join("")}
    </div>

    <!-- Summary row -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:26px;">
      ${[
        ["Total Invoiced", fmt(invoice.value?.grand_total), "#1e293b"],
        ["Total Paid", fmt(invoice.value?.paid_amount), "#15803d"],
        [
          "Balance Due",
          fmt(invoice.value?.balance),
          (invoice.value?.balance ?? 0) > 0 ? "#b91c1c" : "#15803d",
        ],
      ]
        .map(
          ([l, v, c]) => `
        <div style="padding:16px 18px;border:1.5px solid #e2e8f0;border-radius:10px;text-align:center;background:#fafafa;">
          <div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:5px;">${l}</div>
          <div style="font-size:17px;font-weight:900;color:${c};">${v}</div>
        </div>
      `
        )
        .join("")}
    </div>

    <!-- Transaction ledger -->
    <div class="section-label">Transaction Ledger</div>
    <table class="items-table" style="margin-bottom:22px;">
      <thead>
        <tr style="background:${accent};">
          <th style="text-align:left;">Date</th>
          <th style="text-align:left;">Type</th>
          <th style="text-align:left;">Reference</th>
          <th style="text-align:right;">Debit (DR)</th>
          <th style="text-align:right;">Credit (CR)</th>
          <th style="text-align:right;">Balance</th>
        </tr>
      </thead>
      <tbody>${ledgerRows}</tbody>
      <tfoot>
        <tr style="background:${accent}18;border-top:2px solid ${accent}44;">
          <td colspan="4" style="font-weight:800;font-size:12px;text-align:right;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Closing Balance</td>
          <td></td>
          <td style="font-weight:900;font-size:15px;text-align:right;color:${
            (invoice.value?.balance ?? 0) > 0 ? "#b91c1c" : "#15803d"
          };">
            ${fmt(invoice.value?.balance ?? 0)} <span style="font-size:10px;">${
    (invoice.value?.balance ?? 0) > 0 ? "DR" : "CR"
  }</span>
          </td>
        </tr>
      </tfoot>
    </table>

    <!-- Overdue warning -->
    ${
      isOverdue.value
        ? `
      <div style="padding:12px 18px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:8px;margin-bottom:22px;">
        <strong style="color:#b91c1c;">⚠ Overdue Notice:</strong>
        <span style="color:#7f1d1d;font-size:12px;margin-left:6px;">
          This invoice was due on ${fmtDate(invoice.value?.due_date)}.
          Please arrange payment immediately to avoid late charges.
        </span>
      </div>
    `
        : ""
    }

    ${buildNotesBox()}
    ${buildDocFooter("Account Statement", accent)}
  </div>
  </body></html>`;
};

/* ═══════════════════════════════════════════════════════════
   IFRAME WRITER
═══════════════════════════════════════════════════════════ */
const writeToFrame = (html) => {
  nextTick(() => {
    const frame = docFrame.value;
    if (!frame) return;
    const doc = frame.contentDocument || frame.contentWindow?.document;
    if (!doc) return;
    doc.open();
    doc.write(html);
    doc.close();

    // Auto-resize iframe to content height
    frame.onload = () => {
      try {
        const h = frame.contentDocument?.body?.scrollHeight;
        if (h) frame.style.height = h + 40 + "px";
      } catch (_) {}
    };
  });
};

const renderDoc = () => {
  if (!invoice.value) return;
  if (activeDoc.value === "invoice") writeToFrame(buildInvoiceDoc());
  if (activeDoc.value === "receipt") writeToFrame(buildReceiptDoc());
  if (activeDoc.value === "statement") writeToFrame(buildStatementDoc());
};

const switchDoc = (key) => {
  activeDoc.value = key;
  renderDoc();
};

/* ─── PRINT / DOWNLOAD ────────────────── */
const printFrame = () => {
  const frame = docFrame.value;
  if (!frame) return;
  frame.contentWindow?.focus();
  frame.contentWindow?.print();
};

const downloadPDF = () => {
  Swal.fire({
    icon: "info",
    title: "Save as PDF",
    html:
      'In the print dialog, choose <strong>"Save as PDF"</strong> as the destination.',
    confirmButtonText: "Open Print Dialog",
    confirmButtonColor: "#229DE7",
    showCancelButton: true,
  }).then((r) => {
    if (r.isConfirmed) printFrame();
  });
};

/* ─── FETCH ───────────────────────────── */
onMounted(async () => {
  pageLoading.value = true;
  try {
    const [invRes, payRes] = await Promise.all([
      InvoiceService.getById(invoiceId),
      InvoiceService.getPayments(invoiceId),
    ]);
    invoice.value = invRes?.data?.data ?? invRes?.data ?? null;
    payments.value = payRes?.data?.data ?? payRes?.data ?? [];
    renderDoc();
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Could not load invoice documents.", "error");
  } finally {
    setTimeout(() => {
      pageLoading.value = false;
    }, 400);
  }
});

// Re-render when data changes
watch([invoice, payments], renderDoc);
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
</style>
