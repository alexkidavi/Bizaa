<?php

namespace App\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function all();

    public function find(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): void;

    public function permissionsFor(int $id);

    public function syncPermissions(int $id, array $permissionIds): void;
}
