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

    public function getPayments($id)
    {
        return SalePayment::whereHas('sale', function ($query) use ($id) {
            $query->where('customer_id', $id);
        })->get();
    }

    public function getSales($id)
    {
        return Sale::where('customer_id', $id)
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
