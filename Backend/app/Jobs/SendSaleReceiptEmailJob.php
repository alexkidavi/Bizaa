<?php

namespace App\Jobs;

use App\Mail\SaleReceiptMail;
use App\Models\Sale;
use App\Services\SaleReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Renders the receipt PDF fresh (via SaleReceiptService, the same
 * renderer the print endpoint uses) and emails it — dispatched from
 * SaleController::emailReceipt(). Same "dispatch, log every stage, never
 * let a failure block the request" treatment as SendWelcomeMailJob.
 */
class SendSaleReceiptEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300];

    public function __construct(
        protected Sale $sale,
        protected string $recipientEmail,
        protected string $businessName,
    ) {
    }

    public function handle(SaleReceiptService $receipts): void
    {
        $mailer = config('mail.default');

        Log::info('SaleReceiptMail: sending', [
            'sale_id' => $this->sale->id,
            'email' => $this->recipientEmail,
            'mailer' => $mailer,
        ]);

        try {
            $pdfContents = $receipts->renderPdf($this->sale);

            Mail::to($this->recipientEmail)->send(
                new SaleReceiptMail($this->sale, $this->businessName, $pdfContents)
            );

            Log::info('SaleReceiptMail: sent successfully', [
                'sale_id' => $this->sale->id,
                'email' => $this->recipientEmail,
                'mailer' => $mailer,
            ]);
        } catch (\Throwable $e) {
            Log::error('SaleReceiptMail: send failed', [
                'sale_id' => $this->sale->id,
                'email' => $this->recipientEmail,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SaleReceiptMail: gave up after all retries', [
            'sale_id' => $this->sale->id,
            'email' => $this->recipientEmail,
            'error' => $e->getMessage(),
        ]);
    }
}
