<?php

namespace App\Services;

use App\Http\Helpers\PhoneHelper;
use App\Models\User;
use App\Repositories\StaffRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
class StaffService
{
    public function __construct(protected StaffRepository $repo) {}

    // ── List ──────────────────────────────────────────────────────

    public function list(int $perPage = 50): LengthAwarePaginator
    {
        return $this->repo->getAllForBusiness(
            Auth::user()->business_id,
            $perPage
        );
    }

    // ── Find ──────────────────────────────────────────────────────

    public function find(int $id): User
    {
        return $this->repo->findById($id, Auth::user()->business_id);
    }

    // ── Create ────────────────────────────────────────────────────

public function create(array $data): User
{
    $auth = Auth::user();

    $this->guardDuplicateEmail($data['email'], $auth->business_id);
    $password = $data['password'] ?? 'Test@2026';

    return $this->repo->create([
        'name'          => $data['name'],
        'email'         => $data['email'],
        'phone'         => PhoneHelper::normalize($data['phone']) ?? null,
        'password'      => Hash::make($password),
        'role_id'       => $data['role_id'],
        'business_id'   => $auth->business_id,
        'registered_by' => $auth->id,
        'is_active'     => true,
    ]);
}
    // ── Update ────────────────────────────────────────────────────

    public function update(int $id, array $data): User
    {
        $auth  = Auth::user();
        $staff = $this->find($id);

        // Guard duplicate email, excluding this user
        if ($data['email'] !== $staff->email) {
            $this->guardDuplicateEmail($data['email'], $auth->business_id, $id);
        }

        $payload = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'role_id' => $data['role_id'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        return $this->repo->update($staff, $payload);
    }

    // ── Toggle active / inactive ──────────────────────────────────

    public function toggleStatus(int $id): User
    {
        $staff = $this->find($id);
        return $this->repo->toggleActive($staff);
    }

    // ── Delete ────────────────────────────────────────────────────

public function delete(int $id): void
{
    $staff = $this->find($id);

    if (!$staff) {
        throw new \Exception("Staff not found");
    }

    $staff->is_active = 0;
    $staff->save(); 
}

    // ── Private helpers ───────────────────────────────────────────

    private function guardDuplicateEmail(string $email, int $businessId, ?int $excludeId = null): void
    {
        $query = User::where('email', $email)->where('business_id', $businessId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages([
                'email' => ['A staff member with this email already exists in your business.'],
            ]);
        }
    }
}
