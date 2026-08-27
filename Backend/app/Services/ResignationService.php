<?php

namespace App\Services;

use App\Models\Resignation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResignationService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number,employment_status', 'exitInterviewer:id,name', 'creator:id,name'];

    public function __construct(protected EmployeeProfileService $employeeProfileService) {}

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Resignation::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('resignation_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): Resignation
    {
        return Resignation::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): Resignation
    {
        $auth = Auth::user();

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['employee_id'] = $data['employee_id'];
        $payload['created_by'] = $auth->id;

        $resignation = Resignation::create($payload);

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($resignation, $data['supporting_documents']);
        }

        // Not just ->load(): $payload never sets `status`, so the in-memory
        // model is missing the DB default until it's actually re-fetched.
        return $resignation->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): Resignation
    {
        $resignation = $this->find($id);
        $resignation->update($this->basePayload($data));

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($resignation, $data['supporting_documents']);
        }

        return $resignation->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Resignations move through submitted -> under_review -> approved/rejected
     * -> completed/cancelled. Reaching "completed" is the one transition with
     * a real side effect: the employee's own employment status flips to
     * "resigned" so the rest of the system (payroll, leave, etc.) sees it.
     */
    public function updateStatus(int $id, string $status, array $data = []): Resignation
    {
        return DB::transaction(function () use ($id, $status, $data) {
            $resignation = $this->find($id);

            $payload = ['status' => $status];
            foreach (['hr_comments', 'manager_comments', 'actual_last_working_day'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }

            $resignation->update($payload);

            if ($status === 'completed') {
                // Delegates rather than mutating the employee directly —
                // this is also where login access gets disabled for a
                // non-active status, and duplicating that here would risk
                // the two implementations drifting apart.
                $this->employeeProfileService->updateStatus($resignation->employee_id, 'resigned', 'Resignation completed');
            }

            return $resignation->fresh(self::RELATIONS);
        });
    }

    public function documentPath(int $id, string $path): string
    {
        $resignation = $this->find($id);
        $exists = collect($resignation->supporting_documents ?? [])->contains(fn ($doc) => $doc['path'] === $path);
        abort_unless($exists, 404, 'Document not found on this resignation.');
        return $path;
    }

    protected function attachDocuments(Resignation $resignation, array $files): void
    {
        $documents = collect($resignation->supporting_documents ?? []);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("resignation-documents/{$resignation->id}", 'local');
            $documents->push([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ]);
        }

        $resignation->update(['supporting_documents' => $documents->values()->all()]);
    }

    protected function basePayload(array $data): array
    {
        return [
            'resignation_date' => $data['resignation_date'] ?? null,
            'notice_date' => $data['notice_date'] ?? null,
            'notice_period_days' => $data['notice_period_days'] ?? null,
            'expected_last_working_day' => $data['expected_last_working_day'] ?? null,
            'reason' => $data['reason'] ?? null,
            'employee_comments' => $data['employee_comments'] ?? null,
            'hr_comments' => $data['hr_comments'] ?? null,
            'manager_comments' => $data['manager_comments'] ?? null,
            'exit_interview_notes' => $data['exit_interview_notes'] ?? null,
            'exit_interview_conducted_by' => $data['exit_interview_conducted_by'] ?? null,
            'exit_interview_date' => $data['exit_interview_date'] ?? null,
        ];
    }
}
