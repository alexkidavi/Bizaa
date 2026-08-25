<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function all()
    {
        return Permission::where('name', 'not like', 'superadmin.%')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
