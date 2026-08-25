<?php
namespace App\Services;

use App\Models\Quotation;
use App\Repositories\{QuotationRepository, InvoiceRepository};
use Illuminate\Support\Facades\{Auth, DB};

class QuotationService
{
    public function __construct(
        protected QuotationRepository $repo,
        protected InvoiceRepository   $invoiceRepo,
        protected InvoiceService      $invoiceService,
    ) {}

    public function list(int $perPage = 20) { return $this->repo->getAllForBusiness(Auth::user()->business_id, $perPage); }
    public function find(int $id): Quotation { return $this->repo->findById($id, Auth::user()->business_id); }

    public function create(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $quotation = $this->repo->create([
                'business_id'      => $user->business_id,
                'user_id'          => $user->id,
                'customer_id'      => $data['customer_id'],
                'branch_id'        => $data['branch_id'] ?? null,
                'quotation_number' => $data['quotation_number'] ?? null,
                'status'           => $data['status'] ?? 'draft',
                'issue_date'       => $data['issue_date'],
                'expiry_date'      => $data['expiry_date'],
                'currency'         => $data['currency'] ?? 'KES',
                'notes'            => $data['notes'] ?? null,
                'subtotal'         => $data['subtotal'],
                'total_discount'   => $data['total_discount'],
                'total_tax'        => $data['total_tax'],
                'grand_total'      => $data['grand_total'],
            ]);
            $this->repo->replaceItems($quotation, $this->prepareItems($quotation->id, $data['items']));
            return $this->repo->findById($quotation->id, $user->business_id);
        });
    }

    public function update(int $id, array $data): Quotation
    {
        return DB::transaction(function () use ($id, $data) {
            $user      = Auth::user();
            $quotation = $this->find($id);
            $this->repo->update($quotation, [
                'customer_id'    => $data['customer_id'],
                'branch_id'      => $data['branch_id'] ?? null,
                'status'         => $data['status'] ?? $quotation->status,
                'issue_date'     => $data['issue_date'],
                'expiry_date'    => $data['expiry_date'],
                'currency'       => $data['currency'] ?? 'KES',
                'notes'          => $data['notes'] ?? null,
                'subtotal'       => $data['subtotal'],
                'total_discount' => $data['total_discount'],
                'total_tax'      => $data['total_tax'],
                'grand_total'    => $data['grand_total'],
            ]);
            $this->repo->replaceItems($quotation, $this->prepareItems($quotation->id, $data['items']));
            return $this->repo->findById($quotation->id, $user->business_id);
        });
    }

    public function updateStatus(int $id, string $status): Quotation
    {
        $quotation = $this->find($id);
        $this->repo->update($quotation, ['status' => $status]);
        return $quotation->refresh();
    }

    public function delete(int $id): void { $this->repo->delete($this->find($id)); }

    /**
     * Convert a quotation to an invoice (one click).
     */
    public function convertToInvoice(int $id): \App\Models\Invoice
    {
        return DB::transaction(function () use ($id) {
            $quotation = $this->find($id);

            if ($quotation->status === 'converted') {
                throw new \Exception('This quotation has already been converted.');
            }

            // Build invoice payload from quotation
            $invoice = $this->invoiceService->create([
                'customer_id'    => $quotation->customer_id,
                'branch_id'      => $quotation->branch_id,
                'quotation_id'   => $quotation->id,
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'currency'       => $quotation->currency,
                'notes'          => $quotation->notes,
                'subtotal'       => $quotation->subtotal,
                'total_discount' => $quotation->total_discount,
                'total_tax'      => $quotation->total_tax,
                'grand_total'    => $quotation->grand_total,
                'payment_type'   => 'none',
                'paid_amount'    => 0,
                'items'          => $quotation->items->map(fn ($i) => [
                    'product_id'  => $i->product_id,
                    'description' => $i->description,
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'discount'    => $i->discount,
                    'tax'         => $i->tax,
                    'line_total'  => $i->line_total,
                ])->toArray(),
            ]);

            // Mark quotation as converted + link invoice
            $this->repo->update($quotation, ['status' => 'converted', 'invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    private function prepareItems(int $quotationId, array $items): array
    {
        $now = now();
        return array_map(fn ($i) => [
            'quotation_id' => $quotationId,
            'product_id'   => $i['product_id'],
            'description'  => $i['description'] ?? null,
            'quantity'     => $i['quantity'],
            'unit_price'   => $i['unit_price'],
            'discount'     => $i['discount'] ?? 0,
            'tax'          => $i['tax'] ?? 0,
            'line_total'   => $i['line_total'],
            'created_at'   => $now,
            'updated_at'   => $now,
        ], $items);
    }
}