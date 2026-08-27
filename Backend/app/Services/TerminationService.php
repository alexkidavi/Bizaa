<?php

namespace App\Services;

use App\Models\Termination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerminationService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number,employment_status', 'terminationType:id,name', 'approver:id,name', 'creator:id,name'];

    public function __construct(protected EmployeeProfileService $employeeProfileService) {}

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Termination::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('termination_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): Termination
    {
        return Termination::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): Termination
    {
        $auth = Auth::user();

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['employee_id'] = $data['employee_id'];
        $payload['created_by'] = $auth->id;

        $termination = Termination::create($payload);

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($termination, $data['supporting_documents']);
        }

        // Not just ->load(): $payload never sets `status`, so the in-memory
        // model is missing the DB default until it's actually re-fetched.
        return $termination->fresh(self::RELATIONS);
    }

    public function update(int $id, array $data): Termination
    {
        $termination = $this->find($id);
        $termination->update($this->basePayload($data));

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($termination, $data['supporting_documents']);
        }

        return $termination->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * pending -> approved -> completed (or cancelled at any point before
     * completion). "approved" stamps who/when; "completed" is the one
     * transition that flips the employee's own employment status.
     */
    public function updateStatus(int $id, string $status, array $data = []): Termination
    {
        return DB::transaction(function () use ($id, $status, $data) {
            $termination = $this->find($id);
            $auth = Auth::user();

            $payload = ['status' => $status];
            foreach (['hr_comments', 'manager_comments', 'final_settlement_amount', 'final_settlement_notes'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                }
            }
            if ($status === 'approved') {
                $payload['approved_by'] = $auth->id;
                $payload['approved_at'] = now();
            }

            $termination->update($payload);

            if ($status === 'completed') {
                // Delegates rather than mutating the employee directly —
                // this is also where login access gets disabled for a
                // non-active status, and duplicating that here would risk
                // the two implementations drifting apart.
                $this->employeeProfileService->updateStatus($termination->employee_id, 'terminated', 'Termination completed');
            }

            return $termination->fresh(self::RELATIONS);
        });
    }

    public function documentPath(int $id, string $path): string
    {
        $termination = $this->find($id);
        $exists = collect($termination->supporting_documents ?? [])->contains(fn ($doc) => $doc['path'] === $path);
        abort_unless($exists, 404, 'Document not found on this termination.');
        return $path;
    }

    protected function attachDocuments(Termination $termination, array $files): void
    {
        $documents = collect($termination->supporting_documents ?? []);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("termination-documents/{$termination->id}", 'local');
            $documents->push([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ]);
        }

        $termination->update(['supporting_documents' => $documents->values()->all()]);
    }

    protected function basePayload(array $data): array
    {
        return [
            'termination_type_id' => $data['termination_type_id'] ?? null,
            'termination_date' => $data['termination_date'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
            'reason' => $data['reason'] ?? null,
            'detailed_explanation' => $data['detailed_explanation'] ?? null,
            'notice_given' => $data['notice_given'] ?? false,
            'notice_period_days' => $data['notice_period_days'] ?? null,
            'hr_comments' => $data['hr_comments'] ?? null,
            'manager_comments' => $data['manager_comments'] ?? null,
            'final_settlement_amount' => $data['final_settlement_amount'] ?? null,
            'final_settlement_notes' => $data['final_settlement_notes'] ?? null,
        ];
    }
}
