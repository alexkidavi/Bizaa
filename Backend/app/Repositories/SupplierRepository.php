<?php
namespace App\Repositories;

use App\Http\Helpers\PhoneHelper;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Auth;

class SupplierRepository
{
    public function all()
    {
        $user = Auth::user();
        return Supplier::with([
            'branches:id,name'
        ])->where('business_id', $user->business_id)->get();
    }

    public function getPayments($id)
    {
        return SalePayment::whereHas('sale', function ($query) use ($id) {
            $query->where('supplier_id', $id);
        })->get();
    }

    public function getSales($id)
    {
        return Purchase::where('supplier_id', $id)
            ->with(['payments'])
            ->withCount('items')
            ->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Supplier::with('branches')
            ->where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $supplier = Supplier::create([
            'user_id'      => $data['user_id'],
            'business_id'  => $data['business_id'],
            'company_name' => $data['company_name'] ?? null,
            'name'         => $data['name'],
            'tax_id'       => $data['tax_id'] ?? null,
            'national_id'  => $data['national_id'] ?? null,
            'email'        => $data['email'] ?? null,
            'phone'        => $data['phone'],
            'status'       => $data['status'],
            'notes'        => $data['notes'] ?? null,
        ]);

        if (!empty($data['branch_ids'])) {
            $supplier->branches()->sync($data['branch_ids']);
        }

        return $supplier;
    }

    public function updateStatus($id, $status)
    {
        $supplier            = $this->find($id);
        $supplier->is_active = $status;
        $supplier->save();
        return $supplier;
    }

    public function updatesupplier($id, array $data)
    {
        $supplier = $this->find($id);
        $supplier->update([
            'name'         => $data['name'],
            'email'        => $data['email'] ?? null,
            'tax_id'       => $data['tax_id'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'national_id'  => $data['national_id'] ?? null,
            'phone'        => PhoneHelper::normalize($data['phone']),
            'status'       => $data['status'],
            'notes'        => $data['notes'] ?? null,
        ]);

        if (isset($data['branch_ids'])) {
            $supplier->branches()->sync($data['branch_ids']);
        }

        return $supplier->load('branches');
    }

    public function delete($id)
    {
        $supplier = $this->find($id);
        return $supplier->delete();
    }
}
