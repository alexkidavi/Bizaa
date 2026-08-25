<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
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
        $staff = $this->service->list($request->integer('per_page', 50));
        return response()->json($staff);
    }

    /**
     * POST /api/staff
     * Create a new staff member (stored in users table).
     */
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $staff = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Staff member added successfully.',
            'data'    => $staff->load(['role', 'registeredBy:id,name']),
        ], 200);
    }

    /**
     * GET /api/staff/{staff}
     * Show a single staff member.
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->service->find($id),
        ]);
    }

    /**
     * PUT /api/staff/{staff}
     * Update a staff member.
     */
    public function update(StoreStaffRequest $request, int $id): JsonResponse
    {
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
        $this->service->delete($id);
        return response()->json(['message' => 'Staff member removed successfully.']);
    }

    /**
     * PATCH /api/staff/{staff}/toggle-status
     * Activate or deactivate a staff member.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $staff = $this->service->toggleStatus($id);

        return response()->json([
            'message'   => $staff->is_active ? 'Staff member activated.' : 'Staff member deactivated.',
            'is_active' => $staff->is_active,
            'data'      => $staff,
        ]);
    }
}


// =====================================================================
// app/Http/Controllers/Api/RoleController.php
// =====================================================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * GET /api/roles
     * Return all roles (used to populate the role select dropdown).
     */
    public function index(): JsonResponse
    {
        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);
        return response()->json(['data' => $roles]);
    }
}


// =====================================================================
// routes/api.php  — add inside auth middleware group
// =====================================================================
/*
Route::middleware(['auth:api'])->group(function () {

    // ── Staff ────────────────────────────────────────────────────
    Route::apiResource('staff', \App\Http\Controllers\Api\StaffController::class);
    Route::patch('staff/{staff}/toggle-status', [\App\Http\Controllers\Api\StaffController::class, 'toggleStatus']);

    // ── Roles ────────────────────────────────────────────────────
    Route::get('roles', [\App\Http\Controllers\Api\RoleController::class, 'index']);

});

// ── ENDPOINT SUMMARY ─────────────────────────────────────────────
// GET    /api/staff                        → paginated list
// POST   /api/staff                        → create staff member
// GET    /api/staff/{id}                   → show staff member
// PUT    /api/staff/{id}                   → update staff member
// DELETE /api/staff/{id}                   → remove staff member
// PATCH  /api/staff/{id}/toggle-status     → activate / deactivate
// GET    /api/roles                        → all roles (for dropdown)
*/
