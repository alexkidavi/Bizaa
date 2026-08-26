<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    use HandlesDeleteErrors;

    protected $service;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Brand::class);
        return response()->json($this->service->getAllBrands());
    }

    public function store(BrandRequest $request)
    {
        $this->authorize('create', Brand::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $brand               = $this->service->createBrand($data);
        return response()->json([
            'message' => 'Brand created successfully',
            'data'    => $brand,
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $brand = $this->service->getBrand($id);
        $this->authorize('update', $brand);
        $brand = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Brand status updated',
            'data'    => $brand,
        ]);
    }

    public function updateBrand($id, BrandRequest $request)
    {
        $brand = $this->service->getBrand($id);
        $this->authorize('update', $brand);
        $brand = $this->service->updateBrand($id, $request->validated());
        return response()->json([
            'message' => 'Brand updated successfully',
            'data'    => $brand,
        ]);
    }

    public function destroy($id)
    {
        $brand = $this->service->getBrand($id);
        $this->authorize('delete', $brand);
        return $this->safeDelete(fn () => $this->service->deleteBrand($id), 'Brand deleted successfully');
    }
}
