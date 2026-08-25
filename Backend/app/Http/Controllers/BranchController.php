<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    use HandlesDeleteErrors;

    protected $service;

    public function __construct(BranchService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Branch::class);
        return response()->json($this->service->getAllBranches());
    }

    public function store(BranchRequest $request)
    {
        $this->authorize('create', Branch::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $branch              = $this->service->createBranch($data);
        return response()->json([
            'message' => 'Branch created successfully',
            'data'    => $branch,
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $branch = $this->service->getBranch($id);
        $this->authorize('update', $branch);
        $branch = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Branch status updated',
            'data'    => $branch,
        ]);
    }

    public function updateBranch($id, BranchRequest $request)
    {
        $branch = $this->service->getBranch($id);
        $this->authorize('update', $branch);
        $branch = $this->service->updateBranch($id, $request->validated());
        return response()->json([
            'message' => 'Branch updated successfully',
            'data'    => $branch,
        ]);
    }

    public function destroy($id)
    {
        $branch = $this->service->getBranch($id);
        $this->authorize('delete', $branch);
        return $this->safeDelete(fn () => $this->service->deleteBranch($id), 'Branch deleted successfully');
    }
}
