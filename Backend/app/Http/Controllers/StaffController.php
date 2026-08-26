<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\{JsonResponse, Request};

class StaffController extends Controller
{
    public function __construct(protected StaffService $service) {}

    /**
     * GET /api/staff
     * List all staff for the authenticated business.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $staff = $this->service->list($request->integer('per_page', 50));
        return response()->json($staff);
    }

    /**
     * POST /api/staff
     * Create a new staff member (stored in users table).
     */
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $staff = $this->service->create($request->validated());
        $tempPassword = $staff->temp_password ?? null;

        return response()->json([
            'message'       => 'Staff member added successfully.',
            'data'          => $staff->load(['role', 'registeredBy:id,name']),
            // Only present when no password was supplied — share this with
            // the new staff member now, it will not be shown again.
            'temp_password' => $tempPassword,
        ], 200);
    }

    /**
     * GET /api/staff/{staff}
     * Show a single staff member.
     */
    public function show(int $id): JsonResponse
    {
        $staff = $this->service->find($id);
        $this->authorize('view', $staff);

        return response()->json([
            'data' => $staff,
        ]);
    }

    /**
     * PUT /api/staff/{staff}
     * Update a staff member.
     */
    public function update(StoreStaffRequest $request, int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);

        $staff = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'data'    => $staff,
        ]);
    }

    /**
     * DELETE /api/staff/{staff}
     * Remove a staff member.
     */
    public function destroy(int $id): JsonResponse
    {
        $staff = $this->service->find($id);
        $this->authorize('delete', $staff);

        $this->service->delete($id);
        return response()->json(['message' => 'Staff member removed successfully.']);
    }

    /**
     * PATCH /api/staff/{staff}/toggle-status
     * Activate or deactivate a staff member.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $existing = $this->service->find($id);
        $this->authorize('update', $existing);

        $staff = $this->service->toggleStatus($id);

        return response()->json([
            'message'   => $staff->is_active ? 'Staff member activated.' : 'Staff member deactivated.',
            'is_active' => $staff->is_active,
            'data'      => $staff,
        ]);
    }
}
