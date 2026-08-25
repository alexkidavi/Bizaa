<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    use HandlesDeleteErrors;
    protected $service;

    public function __construct(UnitService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Unit::class);
        return response()->json($this->service->getAllUnits());
    }

    public function store(UnitRequest $request)
    {
        $this->authorize('create', Unit::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $unit                = $this->service->createUnit($data);
        return response()->json([
            'message' => 'Unit created successfully',
            'data'    => $unit,
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $unit = $this->service->getUnit($id);
        $this->authorize('update', $unit);
        $unit = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Unit status updated',
            'data'    => $unit,
        ]);
    }

    public function updateUnit($id, UnitRequest $request)
    {
        $unit = $this->service->getUnit($id);
        $this->authorize('update', $unit);
        $unit = $this->service->updateUnit($id, $request->validated());
        return response()->json([
            'message' => 'Unit updated successfully',
            'data'    => $unit,
        ]);
    }

    public function destroy($id)
    {
        $unit = $this->service->getUnit($id);
        $this->authorize('delete', $unit);
        return $this->safeDelete(fn () => $this->service->deleteUnit($id), 'Unit deleted successfully');
    }
}
