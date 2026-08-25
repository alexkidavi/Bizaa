<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
class StaffRepository
{
public function __construct(protected User $model) {}
   

public function getAllForBusiness(int $businessId, int $perPage = 50): LengthAwarePaginator
{
    return $this->model
        ->forBusiness($businessId)
        ->staffOnly()
        ->where('is_active', true)
        ->where('id', '!=', Auth::id())
        ->with(['role', 'registeredBy:id,name'])
        ->latest()
        ->paginate($perPage);
}

    public function findById(int $id, int $businessId): User
    {
        return $this->model
            ->forBusiness($businessId)
            ->with(['role', 'registeredBy:id,name'])
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh()->load(['role', 'registeredBy:id,name']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->refresh();
    }
}