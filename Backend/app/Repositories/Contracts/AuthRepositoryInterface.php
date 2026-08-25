<?php

namespace App\Repositories\Contracts;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;

interface AuthRepositoryInterface
{
    public function createBusiness(array $data): Business;

    public function createAdminRoleWithAllPermissions(int $businessId): Role;

    public function createUser(array $data): User;

    public function createDefaultBranch(int $businessId, int $userId): void;

    public function startTrialSubscription(int $businessId): void;

    public function findUserByEmail(string $email): ?User;

    public function updateUser(User $user, array $data): User;
}
