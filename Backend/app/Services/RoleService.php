<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $repo)
    {
    }

    public function getAll()
    {
        return $this->repo->all();
    }

    public function getPermissionsFor(int $roleId)
    {
        return $this->repo->permissionsFor($roleId);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }

    public function assignPermissions(int $roleId, array $permissionIds): void
    {
        $this->repo->syncPermissions($roleId, $permissionIds);
    }

    public function findForAuthorization(int $id)
    {
        return $this->repo->find($id);
    }
}
