<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\AssignRolePermissionsRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use HandlesDeleteErrors;

    public function __construct(protected RoleService $service)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return response()->json(['data' => $this->service->getAll()]);
    }

    public function getRolePermissions($id): JsonResponse
    {
        $role = $this->service->findForAuthorization((int) $id);
        $this->authorize('view', $role);

        return response()->json(['data' => $this->service->getPermissionsFor((int) $id)]);
    }

    public function create(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->service->create($request->validated());

        return response()->json(['status' => 200, 'data' => $role]);
    }

    public function assignPermissions(AssignRolePermissionsRequest $request, $roleId): JsonResponse
    {
        $role = $this->service->findForAuthorization((int) $roleId);
        $this->authorize('update', $role);

        $this->service->assignPermissions((int) $roleId, $request->validated()['permission_ids']);

        return response()->json(['message' => 'Permissions updated successfully']);
    }

    public function update(UpdateRoleRequest $request, $id): JsonResponse
    {
        $role = $this->service->findForAuthorization((int) $id);
        $this->authorize('update', $role);

        $updated = $this->service->update((int) $id, $request->validated());

        return response()->json(['message' => 'Role updated', 'data' => $updated]);
    }

    public function destroy($id): JsonResponse
    {
        $role = $this->service->findForAuthorization((int) $id);
        $this->authorize('delete', $role);

        return $this->safeDelete(
            fn () => $this->service->delete((int) $id),
            'Role deleted'
        );
    }
}
