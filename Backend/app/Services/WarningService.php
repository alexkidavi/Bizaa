<?php

namespace App\Services;

use App\Models\Warning;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class WarningService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number', 'warningType:id,name', 'issuer:id,name', 'creator:id,name'];

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Warning::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('warning_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): Warning
    {
        return Warning::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): Warning
    {
        $auth = Auth::user();

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['employee_id'] = $data['employee_id'];
        $payload['issued_by'] = $data['issued_by'] ?? $auth->id;
        $payload['created_by'] = $auth->id;

        $warning = Warning::create($payload);

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($warning, $data['supporting_documents']);
        }

        return $warning->load(self::RELATIONS);
    }

    public function update(int $id, array $data): Warning
    {
        $warning = $this->find($id);
        $warning->update($this->basePayload($data));

        if (!empty($data['supporting_documents'])) {
            $this->attachDocuments($warning, $data['supporting_documents']);
        }

        return $warning->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function documentPath(int $id, string $path): string
    {
        $warning = $this->find($id);
        $exists = collect($warning->supporting_documents ?? [])->contains(fn ($doc) => $doc['path'] === $path);
        abort_unless($exists, 404, 'Document not found on this warning.');
        return $path;
    }

    protected function attachDocuments(Warning $warning, array $files): void
    {
        $documents = collect($warning->supporting_documents ?? []);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $file->store("warning-documents/{$warning->id}", 'local');
            $documents->push([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ]);
        }

        $warning->update(['supporting_documents' => $documents->values()->all()]);
    }

    protected function basePayload(array $data): array
    {
        return [
            'warning_type_id' => $data['warning_type_id'] ?? null,
            'warning_date' => $data['warning_date'] ?? null,
            'severity' => $data['severity'] ?? 'low',
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'employee_response' => $data['employee_response'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ];
    }
}
