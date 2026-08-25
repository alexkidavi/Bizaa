<?php

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;

class PermissionService
{
    public function __construct(protected PermissionRepositoryInterface $repo)
    {
    }

    public function getAll()
    {
        return $this->repo->all();
    }
}
