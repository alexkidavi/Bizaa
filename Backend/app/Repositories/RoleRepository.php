<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoleRepository implements RoleRepositoryInterface
{
    protected function scopedQuery(): Builder
    {
        return Role::query()->where('business_id', Auth::user()->business_id);
    }

    public function all()
    {
        return $this->scopedQuery()
            ->with('permissions')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function find(int $id)
    {
        return $this->scopedQuery()->with('permissions')->findOrFail($id);
    }

    public function create(array $data)
    {
        $role = Role::create([
            'name'        => $data['name'],
            'guard_name'  => 'api',
            'business_id' => Auth::user()->business_id,
        ]);

        if (!empty($data['permission_ids'])) {
            $role->permissions()->sync($data['permission_ids']);
        }

        return $role;
    }

    public function update(int $id, array $data)
    {
        $role = $this->scopedQuery()->findOrFail($id);
        $role->update(['name' => $data['name']]);

        return $role;
    }

    public function delete(int $id): void
    {
        $role = $this->scopedQuery()->findOrFail($id);
        $role->permissions()->detach();
        $role->delete();
    }

    public function permissionsFor(int $id)
    {
        $role = $this->scopedQuery()->findOrFail($id);

        return $role->permissions()
            ->select('permissions.id', 'permissions.name')
            ->get()
            ->makeHidden('pivot');
    }

    public function syncPermissions(int $id, array $permissionIds): void
    {
        $role = $this->scopedQuery()->findOrFail($id);
        $role->permissions()->sync($permissionIds);
    }
}
