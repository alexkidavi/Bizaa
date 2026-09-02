<?php

namespace App\Services;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Renders a Sale into the same receipt PDF bytes for both entry points —
 * the print endpoint (SaleController::receiptPdf, synchronous download)
 * and the email job (SendSaleReceiptEmailJob, queued) — so print and
 * emailed receipts are always pixel-identical, never two templates
 * drifting apart.
 */
class SaleReceiptService
{
    public function renderPdf(Sale $sale): string
    {
        $branding = ReportBranding::forBusiness($sale->business);

        // Sale::$paid_amount / ::$balance are existing accessors (sum of
        // payments; total - paid_amount) — reused here rather than
        // recomputed, so this never drifts from what the rest of the app
        // already considers "paid"/"owing" on a sale.
        $balance = (float) $sale->balance;

        $pdf = Pdf::loadView('receipts.receipt-pdf', [
            'sale'      => $sale,
            'business'  => $sale->business,
            'branding'  => $branding,
            'paidTotal' => (float) $sale->paid_amount,
            'change'    => max(0, -$balance),
            'balance'   => max(0, $balance),
        ]);

        // 80mm-wide thermal-receipt-style page, generously tall — dompdf
        // doesn't reliably support a truly infinite auto-height page, so
        // this gives a single receipt (rarely more than a handful of line
        // items) comfortable room without needing pagination logic.
        $pdf->setPaper([0, 0, 226.77, 1600], 'portrait');

        return $pdf->output();
    }
}
