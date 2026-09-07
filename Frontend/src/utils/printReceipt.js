import QRCode from "qrcode";

const BASE_URL = import.meta.env.VITE_APP_URL ?? "";

const fmtKES = (val) =>
  "KES " + Number(val ?? 0).toLocaleString("en-KE", { minimumFractionDigits: 2 });

const cap = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : "");

/**
 * This receipt is built as a raw HTML string and injected via
 * document.write() into a same-origin popup, not through Vue's
 * auto-escaping template syntax — every value sourced from user input
 * (product name, cashier/customer name, branch, payment method) must be
 * escaped by hand or this is a stored XSS that fires on every print.
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

/**
 * Print a thermal receipt in a popup window.
 *
 * @param {Object} info   Normalized receipt data. `qrUrl` (etims_url or,
 *                        before KRA finishes processing, offline_url) draws
 *                        a scannable eTIMS compliance QR on the receipt.
 * @param {Object} biz    Business info { name, logo, address, phone }
 */
export async function printReceipt(info, biz = {}) {
  const {
    ref = "",
    date = "",
    branch = "",
    cashier = "",
    customer = "",
    items = [],
    subtotal = 0,
    discount = 0,
    tax = 0,
    total = 0,
    paid = 0,
    change = 0,
    balance = 0,
    payments = [],
    qrUrl = null,
    kraSerial = null,
    etimsConfirmed = false,
  } = info;

  // Open the window synchronously (still inside the user's click) so
  // popup blockers don't kick in — content gets written in once the QR
  // (if any) has finished generating, a few ms later.
  const win = window.open("", "_blank", "width=420,height=700,toolbar=0,menubar=0,scrollbars=1");
  if (!win) return;

  let qrHtml = "";
  if (qrUrl) {
    try {
      const qrDataUrl = await QRCode.toDataURL(qrUrl, { width: 130, margin: 1 });
      qrHtml = `
      <div class="div">- - - - - - - - - - - - - - - - -</div>
      <div class="center etims">
        <img src="${qrDataUrl}" style="width:90px;height:90px;display:block;margin:0 auto 4px;"/>
        <div class="etims-label">KRA eTIMS Compliant Invoice</div>
        ${kraSerial ? `<div class="biz-sub">CU Serial: ${esc(kraSerial)}</div>` : ""}
        <div class="biz-sub">${etimsConfirmed ? "Scan to verify on KRA eTIMS" : "Scan to view receipt &middot; eTIMS verification pending"}</div>
      </div>`;
    } catch {
      qrHtml = "";
    }
  }

  const logoHtml = biz.logo
    ? `<img src="${esc(biz.logo)}" style="max-height:56px;max-width:160px;object-fit:contain;display:block;margin:0 auto 6px;"/>`
    : "";

  const itemsHtml = items
    .map(
      (item, idx) => `
      <div style="margin-bottom:8px;${idx > 0 ? "border-top:1px dotted #ddd;padding-top:7px;" : ""}">
        <div style="font-weight:700;font-size:12px;">${esc(item.name)}</div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#555;margin-top:2px;">
          <span>${item.qty} × ${fmtKES(item.unit_price)}${item.discount > 0 ? ` <span style="color:#059669">(-${item.discount}%)</span>` : ""}</span>
          <span style="font-weight:600;">${fmtKES(item.line_total)}</span>
        </div>
      </div>`
    )
    .join("");

  const paymentsHtml =
    payments.length > 1
      ? payments
          .map(
            (p) =>
              `<div style="display:flex;justify-content:space-between;font-size:11px;padding:2px 0;color:#475569;">
                <span>${esc(cap(p.method))}</span><span>${fmtKES(p.amount)}</span>
              </div>`
          )
          .join("")
      : "";

  const html = `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Receipt ${esc(ref)}</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Courier New',Courier,monospace;font-size:12px;color:#000;background:#fff;padding:16px;width:300px;margin:0 auto}
    .center{text-align:center}
    .biz-name{font-size:18px;font-weight:900;letter-spacing:3px;text-transform:uppercase;margin-top:4px}
    .biz-sub{font-size:10px;color:#555;margin-top:2px}
    .div{text-align:center;color:#aaa;margin:8px 0;font-size:11px}
    .meta{font-size:10px;color:#555;margin-bottom:6px}
    .meta .ref{font-weight:700;font-size:12px;color:#000;margin-top:2px}
    .tr{display:flex;justify-content:space-between;font-size:11px;padding:2px 0}
    .tr.grand{font-weight:900;font-size:14px;border-top:1.5px solid #000;border-bottom:1.5px solid #000;padding:5px 0;margin:4px 0}
    .tr.grn{color:#059669;font-weight:700}
    .tr.red{color:#dc2626;font-weight:700}
    .footer{text-align:center;color:#888;font-size:10px;margin-top:12px}
    .footer .bold{font-weight:700;color:#000;font-size:12px}
    .etims-label{font-size:10px;font-weight:700;color:#000;margin-top:2px}
    @media print{@page{size:80mm auto;margin:4mm}body{padding:0;width:100%}}
  </style>
</head>
<body>
  <div class="center">
    ${logoHtml}
    <div class="biz-name">${esc(biz.name) || "Bizaa"}</div>
    ${biz.address ? `<div class="biz-sub">${esc(biz.address)}</div>` : ""}
    ${biz.phone ? `<div class="biz-sub">${esc(biz.phone)}</div>` : ""}
  </div>
  <div class="div">- - - - - - - - - - - - - - - - -</div>
  <div class="meta">
    ${branch ? `<div>${esc(branch)}</div>` : ""}
    <div>${esc(date)}</div>
    <div class="ref">Receipt #${esc(ref)}</div>
    ${cashier ? `<div>Cashier: ${esc(cashier)}</div>` : ""}
    ${customer ? `<div>Customer: ${esc(customer)}</div>` : ""}
  </div>
  <div class="div">- - - - - - - - - - - - - - - - -</div>
  ${itemsHtml}
  <div class="div">- - - - - - - - - - - - - - - - -</div>
  <div>
    <div class="tr"><span>Subtotal</span><span>${fmtKES(subtotal)}</span></div>
    ${Number(discount) > 0 ? `<div class="tr grn"><span>Discount</span><span>- ${fmtKES(discount)}</span></div>` : ""}
    ${Number(tax) > 0 ? `<div class="tr"><span>VAT</span><span>${fmtKES(tax)}</span></div>` : ""}
    <div class="tr grand"><span>TOTAL</span><span>${fmtKES(total)}</span></div>
    <div class="tr"><span>Paid</span><span>${fmtKES(paid)}</span></div>
    ${paymentsHtml}
    ${Number(change) > 0 ? `<div class="tr grn"><span>Change</span><span>${fmtKES(change)}</span></div>` : ""}
    ${Number(balance) > 0 ? `<div class="tr red"><span>Balance Due</span><span>${fmtKES(balance)}</span></div>` : ""}
  </div>
  ${qrHtml}
  <div class="footer">
    <div class="bold">Thank you!</div>
    <div style="margin-top:6px">Powered by Bizaa</div>
  </div>
  <script>window.onload=function(){window.print();window.onafterprint=function(){window.close()};}<\/script>
</body>
</html>`;

  win.document.write(html);
  win.document.close();
}

/**
 * Resolve business info from the auth store user object.
 *
 * NOTE: `auth.user.business` comes from GET /me (routes/api.php), which
 * deliberately re-maps the Business model's "business_"-prefixed columns
 * (business_name, business_logo, ...) to short unprefixed keys (name, logo,
 * ...) — NOT the same names as the raw Eloquent model used elsewhere
 * (ReportBranding, receipt-pdf.blade.php). Mixing the two up here silently
 * produces the generic "Bizaa" fallback with no logo/contacts, since the
 * wrong keys just read as undefined. Every place in the frontend that reads
 * business branding off `auth.user.business` should go through this one
 * function rather than re-deriving it, so this mapping only needs fixing
 * in one place if that contract ever changes again.
 */
export function resolveBusiness(authUser) {
  const b = authUser?.business ?? {};
  const raw = (b.logo ?? "").trim();
  const logo = raw
    ? /^(https?:\/\/|data:)/i.test(raw)
      ? raw
      : `${BASE_URL}/storage/${raw}`
    : null;
  return {
    name: b.name || "Bizaa",
    address: b.location || "",
    phone: b.phone || "",
    email: b.email || "",
    website: b.website || "",
    industry: b.industry || "",
    logo,
  };
}
