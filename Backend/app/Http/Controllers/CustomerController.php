<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class CustomerController extends Controller
{
    use HandlesDeleteErrors;

    protected $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Customer::class);
        return response()->json($this->service->getAllCustomers());
    }

    public function posSearch(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        $search = $request->query('search');
        return response()->json(['data' => $this->service->searchForPos($search)]);
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('create', Customer::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $customer            = $this->service->createCustomer($data);
        return response()->json([
            'message' => 'Customer created successfully',
            'data'    => $customer,
        ]);
    }

    public function show($id)
    {
        $id=Crypt::decryptString($id);
        $customer = $this->service->getCustomer($id);
        $this->authorize('view', $customer);
        return response()->json($customer);
    }

    public function updateStatus($id, Request $request)
    {
        $customer = $this->service->getCustomer($id);
        $this->authorize('update', $customer);
        $customer = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Customer status updated',
            'data'    => $customer,
        ]);
    }

    public function updateCustomer($id, CustomerRequest $request)
    {
        $customer = $this->service->getCustomer($id);
        $this->authorize('update', $customer);
        $customer = $this->service->updateCustomer($id, $request->validated());
        return response()->json([
            'message' => 'Customer updated successfully',
            'data'    => $customer,
        ]);
    }

    public function getSales($id)
    {
         $id=Crypt::decryptString($id);
        $customer = $this->service->getCustomer($id);
        $this->authorize('view', $customer);
        return response()->json($this->service->getSales($id));
    }

    public function getPayments($id)
    {
         $id=Crypt::decryptString($id);
        $customer = $this->service->getCustomer($id);
        $this->authorize('view', $customer);
        return response()->json($this->service->getPayments($id));
    }

    public function destroy($id)
    {
        $customer = $this->service->getCustomer($id);
        $this->authorize('delete', $customer);
        return $this->safeDelete(
            fn () => $this->service->deleteCustomer($id),
            'Customer deleted successfully'
        );
    }
}
