<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\{Auth, DB};

class InvoiceService
{
    public function __construct(protected InvoiceRepository $repo) {}

    public function list(int $perPage = 20) { return $this->repo->getAllForBusiness(Auth::user()->business_id, $perPage); }
    public function find(int $id): Invoice  { return $this->repo->findById($id, Auth::user()->business_id); }

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            [$paid, $status] = $this->resolvePayment($data['payment_type'], $data['paid_amount'] ?? 0, $data['grand_total']);

            $invoice = $this->repo->create([
                'business_id'       => $user->business_id,
                'user_id'           => $user->id,
                'customer_id'       => $data['customer_id'],
                'branch_id'         => $data['branch_id'] ?? null,
                'quotation_id'      => $data['quotation_id'] ?? null,
                'invoice_number'    => $data['invoice_number'] ?? null,
                'issue_date'        => $data['issue_date'],
                'due_date'          => $data['due_date'],
                'notes'             => $data['notes'] ?? null,
                'currency'          => $data['currency'] ?? 'KES',
                'subtotal'          => $data['subtotal'],
                'total_discount'    => $data['total_discount'],
                'total_tax'         => $data['total_tax'],
                'grand_total'       => $data['grand_total'],
                'paid_amount'       => $paid,
                'balance'           => $data['grand_total'] - $paid,
                'payment_type'      => $data['payment_type'],
                'payment_status'    => $status,
                'payment_method'    => $data['payment_type'] !== 'none' ? ($data['payment_method'] ?? null) : null,
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            $this->repo->replaceItems($invoice, $this->prepareItems($invoice->id, $data['items']));

            if ($data['payment_type'] !== 'none' && $paid > 0) {
                $this->repo->recordPayment($invoice, [
                    'user_id'           => $user->id,
                    'amount'            => $paid,
                    'payment_method'    => $data['payment_method'],
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'payment_date'      => $data['issue_date'],
                    'notes'             => null,
                ]);
            }

            return $this->repo->findById($invoice->id, $user->business_id);
        });
    }

    public function update(int $id, array $data): Invoice
    {
        return DB::transaction(function () use ($id, $data) {
            $user    = Auth::user();
            $invoice = $this->repo->findById($id, $user->business_id);
            [$paid, $status] = $this->resolvePayment($data['payment_type'], $data['paid_amount'] ?? 0, $data['grand_total']);

            $this->repo->update($invoice, [
                'customer_id'       => $data['customer_id'],
                'branch_id'         => $data['branch_id'] ?? null,
                'invoice_number'    => $data['invoice_number'] ?? $invoice->invoice_number,
                'issue_date'        => $data['issue_date'],
                'due_date'          => $data['due_date'],
                'notes'             => $data['notes'] ?? null,
                'currency'          => $data['currency'] ?? 'KES',
                'subtotal'          => $data['subtotal'],
                'total_discount'    => $data['total_discount'],
                'total_tax'         => $data['total_tax'],
                'grand_total'       => $data['grand_total'],
                'paid_amount'       => $paid,
                'balance'           => $data['grand_total'] - $paid,
                'payment_type'      => $data['payment_type'],
                'payment_status'    => $status,
                'payment_method'    => $data['payment_type'] !== 'none' ? ($data['payment_method'] ?? null) : null,
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            $this->repo->replaceItems($invoice, $this->prepareItems($invoice->id, $data['items']));
            return $this->repo->findById($invoice->id, $user->business_id);
        });
    }

    public function delete(int $id): void { $this->repo->delete($this->find($id)); }

    public function recordPayment(int $id, array $data): Invoice
    {
        return DB::transaction(function () use ($id, $data) {
            $user    = Auth::user();
            $invoice = $this->find($id);
            $amount  = min((float) $data['amount'], $invoice->balance);

            $this->repo->recordPayment($invoice, [
                'user_id'           => $user->id,
                'amount'            => $amount,
                'payment_method'    => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_date'      => $data['payment_date'],
                'notes'             => $data['notes'] ?? null,
            ]);

            $newPaid    = $invoice->paid_amount + $amount;
            $newBalance = $invoice->grand_total - $newPaid;
            $newStatus  = $newBalance <= 0 ? 'paid' : 'partial';

            $this->repo->update($invoice, ['paid_amount' => $newPaid, 'balance' => $newBalance, 'payment_status' => $newStatus]);
            return $this->find($id);
        });
    }

    private function resolvePayment(string $type, float $paid, float $total): array
    {
        if ($type === 'none') return [0.0, 'unpaid'];
        $paid   = min($paid, $total);
        $status = $paid <= 0 ? 'unpaid' : ($paid >= $total ? 'paid' : 'partial');
        return [$paid, $status];
    }

    private function prepareItems(int $invoiceId, array $items): array
    {
        $now = now();
        return array_map(fn ($i) => [
            'invoice_id'  => $invoiceId,
            'product_id'  => $i['product_id'],
            'description' => $i['description'] ?? null,
            'quantity'    => $i['quantity'],
            'unit_price'  => $i['unit_price'],
            'discount'    => $i['discount'] ?? 0,
            'tax'         => $i['tax'] ?? 0,
            'line_total'  => $i['line_total'],
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $items);
    }
}