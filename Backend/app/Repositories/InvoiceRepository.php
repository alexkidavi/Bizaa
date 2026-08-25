<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;

class InvoiceRepository
{
    public function __construct(protected Invoice $model) {}

public function getAllForBusiness(int $businessId, int $perPage = 20): LengthAwarePaginator
{
    return $this->model->forBusiness($businessId)
        ->with([
            'customer:id,name',
            'branch:id,name',
            'user:id,name'
        ])
        ->latest('issue_date')
        ->paginate($perPage)
        ->through(function ($inv) {

            // ✅ keep original ID
            $inv->invoice_id = Crypt::encryptString($inv->id);
             unset($inv->id);
            return $inv;
        });
}
    public function findById(int $id, int $businessId): Invoice
    {
        return $this->model->forBusiness($businessId)
            ->with(['customer', 'branch', 'items.product', 'payments.user', 'quotation', 'user'])
            ->findOrFail($id);
    }

    public function create(array $data): Invoice        { return $this->model->create($data); }
    public function update(Invoice $inv, array $d): Invoice { $inv->update($d); return $inv->refresh(); }
    public function delete(Invoice $inv): void          { $inv->delete(); }

    public function replaceItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        if (!empty($items)) $invoice->items()->insert($items);
    }

    public function recordPayment(Invoice $invoice, array $data): void
    {
        $invoice->payments()->create($data);
    }
}