<?php

namespace App\Repositories;

use App\Models\Quotation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;

class QuotationRepository
{
    public function __construct(protected Quotation $model) {}

    public function getAllForBusiness(int $businessId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->forBusiness($businessId)
            ->with(['customer', 'branch', 'user'])
            ->latest('issue_date')
            ->paginate($perPage)
            ->through(fn($q) => tap($q, fn($i) => $i->id = Crypt::encryptString($i->id)));
    }

    public function findById(int $id, int $businessId): Quotation
    {
        return $this->model->forBusiness($businessId)
            ->with(['customer', 'branch', 'items.product', 'invoice', 'user'])
            ->findOrFail($id);
    }

    public function create(array $data): Quotation           { return $this->model->create($data); }
    public function update(Quotation $q, array $d): Quotation { $q->update($d); return $q->refresh(); }
    public function delete(Quotation $q): void               { $q->delete(); }

    public function replaceItems(Quotation $quotation, array $items): void
    {
        $quotation->items()->delete();
        if (!empty($items)) $quotation->items()->insert($items);
    }
}