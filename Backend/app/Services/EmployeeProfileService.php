<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeProfileService
{
    protected const RELATIONS = ['user:id,name,email,phone,role_id,is_active', 'user.role:id,name', 'department:id,name', 'branch:id,name', 'manager:id,first_name,last_name,employee_number', 'creator:id,name'];

    public function __construct(protected StaffService $staffService) {}

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = EmployeeProfile::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderBy('first_name');

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): EmployeeProfile
    {
        return EmployeeProfile::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    /**
     * Staff Profiles is now the single place a new staff member gets
     * created — the login (users row) and the HR record are created
     * together in one submit, instead of a separate "create the login
     * first, then come back and link it" flow. `user_id` is still
     * accepted for linking a pre-existing, not-yet-profiled login
     * (legacy data, or a login created directly via /hrm/staff).
     */
    public function create(array $data): EmployeeProfile
    {
        $auth = Auth::user();
        $tempPassword = null;

        if (!empty($data['user_id'])) {
            $userId = $data['user_id'];
        } else {
            $user = $this->staffService->create([
                'name' => $this->composeName($data),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role_id' => $data['role_id'],
                'password' => $data['password'] ?? null,
            ]);
            $userId = $user->id;
            $tempPassword = $user->temp_password ?? null;
        }

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['user_id'] = $userId;
        $payload['employee_number'] = $data['employee_number'] ?? $this->generateEmployeeNumber($auth->business_id);
        $payload['employment_status'] = $data['employment_status'] ?? 'active';
        $payload['created_by'] = $auth->id;

        if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
            $payload['photo'] = $data['photo']->store('employee-photos', 'public');
        }

        $employee = EmployeeProfile::create($payload);

        if (!empty($data['documents'])) {
            $this->attachDocuments($employee, $data['documents']);
        }

        $employee = $employee->fresh(self::RELATIONS);
        if ($tempPassword) {
            $employee->temp_password = $tempPassword;
        }

        return $employee;
    }

    public function update(int $id, array $data): EmployeeProfile
    {
        $employee = $this->find($id);
        $payload = $this->basePayload($data);

        if (array_key_exists('employee_number', $data) && $data['employee_number']) {
            $payload['employee_number'] = $data['employee_number'];
        }

        if (!empty($data['photo']) && $data['photo'] instanceof UploadedFile) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $payload['photo'] = $data['photo']->store('employee-photos', 'public');
        }

        $employee->update($payload);

        // Login fields (role/email/phone/password) live on `users`, edited
        // from this same form — keep the login in sync rather than sending
        // the user back to a separate Staff screen for these. StaffService
        // ::update() expects a full payload (no field is optional there
        // except password), so merge onto the login's current values
        // rather than sending just whatever this form happened to include.
        if ($employee->user && $this->wantsLoginSync($data)) {
            $this->staffService->update($employee->user_id, [
                'name' => $this->composeName($data) ?? $employee->user->name,
                'email' => $data['email'] ?? $employee->user->email,
                'phone' => $data['phone'] ?? $employee->user->phone,
                'role_id' => $data['role_id'] ?? $employee->user->role_id,
                'password' => $data['password'] ?? null,
            ]);
        }

        if (!empty($data['documents'])) {
            $this->attachDocuments($employee, $data['documents']);
        }

        return $employee->fresh(self::RELATIONS);
    }

    /**
     * Only an "active" employee keeps system access — any other status
     * (on_leave, suspended, resigned, terminated, retired) disables the
     * linked login. Resignation/Termination completing route through
     * here too, so this is the one place that enforcement lives.
     */
    public function updateStatus(int $id, string $status, ?string $reason = null): EmployeeProfile
    {
        $employee = $this->find($id);
        $from = $employee->employment_status;

        // The trait's own `updated` hook would also log this as a generic
        // "updated" entry — suppress it here so the explicit, reason-carrying
        // "status_changed" entry below is the only record of the transition.
        EmployeeProfile::withoutEvents(function () use ($employee, $status) {
            $employee->update(['employment_status' => $status]);
        });
        $employee->logHrmAction('status_changed', ['employment_status' => [$from, $status]], $reason);

        if ($employee->user) {
            $employee->user->update(['is_active' => $status === 'active']);
        }

        return $employee->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function history(int $id): Collection
    {
        return $this->find($id)->auditLogs()->with('user:id,name')->get();
    }

    public function removeDocument(int $id, string $path): EmployeeProfile
    {
        $employee = $this->find($id);
        $documents = collect($employee->documents ?? [])->reject(fn ($doc) => $doc['path'] === $path)->values()->all();

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $employee->update(['documents' => $documents]);
        $employee->logHrmAction('document_removed', ['documents' => [null, $path]]);

        return $employee->fresh(self::RELATIONS);
    }

    public function documentPath(int $id, string $path): string
    {
        $employee = $this->find($id);
        $exists = collect($employee->documents ?? [])->contains(fn ($doc) => $doc['path'] === $path);

        if (!$exists) {
            throw ValidationException::withMessages(['document' => ['Document not found on this employee.']]);
        }

        return $path;
    }

    protected function attachDocuments(EmployeeProfile $employee, array $files): void
    {
        $documents = collect($employee->documents ?? []);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("employee-documents/{$employee->id}", 'local');
            $documents->push([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ]);
        }

        $employee->update(['documents' => $documents->values()->all()]);
        $employee->logHrmAction('document_added', []);
    }

    protected function generateEmployeeNumber(int $businessId): string
    {
        $count = EmployeeProfile::where('business_id', $businessId)->withTrashed()->count();

        do {
            $count++;
            $number = 'EMP-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } while (EmployeeProfile::where('business_id', $businessId)->where('employee_number', $number)->exists());

        return $number;
    }

    protected function wantsLoginSync(array $data): bool
    {
        foreach (['email', 'phone', 'role_id', 'password', 'first_name', 'last_name'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
                return true;
            }
        }

        return false;
    }

    protected function composeName(array $data): ?string
    {
        if (empty($data['first_name']) && empty($data['last_name'])) {
            return null;
        }

        return trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    }

    /**
     * Explicit field-by-field mapping (not array_filter) so a blank
     * optional field — nulled by the app's ConvertEmptyStringsToNull
     * middleware — actually clears the stored value instead of being
     * silently dropped and leaving the old value in place.
     */
    protected function basePayload(array $data): array
    {
        return [
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'address' => $data['address'] ?? null,
            'employment_date' => $data['employment_date'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'designation' => $data['designation'] ?? null,
            // Not-nullable DB columns with defaults — coalesce rather than
            // pass an explicit null, which would violate the column.
            'employment_type' => $data['employment_type'] ?? 'full_time',
            'branch_id' => $data['branch_id'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'basic_salary' => $data['basic_salary'] ?? 0,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_branch' => $data['bank_branch'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
            'kra_pin' => $data['kra_pin'] ?? null,
            'nssf_number' => $data['nssf_number'] ?? null,
            'shif_number' => $data['shif_number'] ?? null,
            'housing_benefit' => $data['housing_benefit'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
