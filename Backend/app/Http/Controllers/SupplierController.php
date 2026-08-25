<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    use HandlesDeleteErrors;

    protected $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Supplier::class);
        return response()->json($this->service->getAllSuppliers());
    }

    public function store(SupplierRequest $request)
    {
        $this->authorize('create', Supplier::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $supplier            = $this->service->createSupplier($data);
        return response()->json([
            'message' => 'Supplier created successfully',
            'data'    => $supplier,
        ]);
    }

    public function show($id)
    {
        $supplier = $this->service->getSupplier($id);
        $this->authorize('view', $supplier);
        return response()->json($supplier);
    }

    public function updateStatus($id, Request $request)
    {
        $supplier = $this->service->getSupplier($id);
        $this->authorize('update', $supplier);
        $supplier = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Supplier status updated',
            'data'    => $supplier,
        ]);
    }

    public function updatesupplier($id, SupplierRequest $request)
    {
        $supplier = $this->service->getSupplier($id);
        $this->authorize('update', $supplier);
        $supplier = $this->service->updateSupplier($id, $request->validated());
        return response()->json([
            'message' => 'Supplier updated successfully',
            'data'    => $supplier,
        ]);
    }

    public function getSales($id)
    {
        info('fetching sales');
        $supplier = $this->service->getSupplier($id);
        $this->authorize('view', $supplier);
        return response()->json($this->service->getSales($id));
    }

    public function getPayments($id)
    {
        $supplier = $this->service->getSupplier($id);
        $this->authorize('view', $supplier);
        return response()->json($this->service->getPayments($id));
    }

    public function destroy($id)
    {
        $supplier = $this->service->getSupplier($id);
        $this->authorize('delete', $supplier);
        return $this->safeDelete(
            fn () => $this->service->deletesupplier($id),
            'Supplier deleted successfully'
        );
    }
}
