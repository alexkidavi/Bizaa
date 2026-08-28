<?php
namespace App\Repositories;

use App\Http\Helpers\PhoneHelper;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
class CustomerRepository
{


public function all()
{
    $user = Auth::user();

    return Customer::with([
        'branches:id,name'
    ])
    ->where('business_id', $user->business_id)
    ->get()
    ->map(function ($customer) {
        return [
            'customer_id' => Crypt::encryptString($customer->id),
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'branches' => $customer->branches,
        ];
    });
}

/**
 * Purpose-built for the POS customer picker — unlike all() above, this
 * returns a plain integer id (POS submits it straight back as
 * sales.customer_id, which the DB and StoreSaleRequest both expect as a
 * real integer, not the encrypted opaque token all() hands out for the
 * Customers module's own URL-based navigation).
 *
 * Also unlike all(), this never loads the whole table: no search term
 * returns only the most recently added customers (a small, useful
 * default — not "everyone"), and a search term is required to reach past
 * that. At a business with thousands of customers, an unfiltered
 * customer list would be exactly the kind of unbounded payload that
 * silently made POS unusable once real data volume showed up — this
 * endpoint is built so that can't happen no matter how big the table gets.
 */
public function searchForPos(?string $search, int $limit = 20)
{
    $businessId = Auth::user()->business_id;

    $query = Customer::where('business_id', $businessId)
        ->select('id', 'name', 'phone', 'email');

    if ($search !== null && $search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    return $query->orderByDesc('id')->limit($limit)->get();
}

    public function getPayments($id)
    {
        $businessId = Auth::user()->business_id;

        return SalePayment::whereHas('sale', function ($query) use ($id, $businessId) {
            $query->where('customer_id', $id)
                  ->where('business_id', $businessId);
        })->get();
    }

    public function getSales($id)
    {
        $businessId = Auth::user()->business_id;

        return Sale::where('customer_id', $id)
            ->where('business_id', $businessId)
            ->with(['payments'])
            ->withCount('items')
            ->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Customer::with('branches')
            ->where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $customer = Customer::create([
            'user_id'     => $data['user_id'],
            'business_id' => $data['business_id'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'],
            'status'      => $data['status'],
            'notes'       => $data['notes'] ?? null,
        ]);

        if (!empty($data['branch_ids'])) {
            $customer->branches()->sync($data['branch_ids']);
        }

        return $customer;
    }

    public function updateStatus($id, $status)
    {
        $customer            = $this->find($id);
        $customer->is_active = $status;
        $customer->save();
        return $customer;
    }

    public function updateCustomer($id, array $data)
    {
        $customer = $this->find($id);
        $customer->update([
            'name'   => $data['name'],
            'email'  => $data['email'] ?? null,
            'phone'  => PhoneHelper::normalize($data['phone']),
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? null,
        ]);

        if (isset($data['branch_ids'])) {
            $customer->branches()->sync($data['branch_ids']);
        }

        return $customer->load('branches');
    }

    public function delete($id)
    {
        $customer = $this->find($id);
        return $customer->delete();
    }
}
