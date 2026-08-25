<?php

namespace App\Repositories\Contracts;

interface PermissionRepositoryInterface
{
    /**
     * List permissions assignable to a role (excludes the superadmin-only permission).
     */
    public function all();
}
