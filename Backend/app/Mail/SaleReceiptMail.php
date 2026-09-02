<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a cashier taps "Send" on a receipt (mobile app today; web can
 * reuse the same endpoint later). The PDF is generated fresh from the same
 * resources/views/receipts/receipt-pdf.blade.php template the print
 * endpoint streams, so print and email always show identical content —
 * see SaleController::emailReceipt() / receiptPdf().
 */
class SaleReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale,
        public string $businessName,
        public string $pdfContents,
    ) {
    }

    public function build()
    {
        return $this->subject('Your receipt from ' . $this->businessName . ' — #' . $this->sale->reference)
            ->view('emails.sale-receipt')
            ->attachData($this->pdfContents, 'receipt-' . $this->sale->reference . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
