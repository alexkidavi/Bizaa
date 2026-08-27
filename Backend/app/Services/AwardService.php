<?php

namespace App\Services;

use App\Models\Award;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AwardService
{
    protected const RELATIONS = ['employee:id,first_name,middle_name,last_name,employee_number', 'awardType:id,name', 'issuer:id,name', 'creator:id,name'];

    public function list(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Award::where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->orderByDesc('award_date');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['award_type_id'])) {
            $query->where('award_type_id', $filters['award_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): Award
    {
        return Award::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->with(self::RELATIONS)
            ->firstOrFail();
    }

    public function create(array $data): Award
    {
        $auth = Auth::user();

        $payload = $this->basePayload($data);
        $payload['business_id'] = $auth->business_id;
        $payload['employee_id'] = $data['employee_id'];
        $payload['issued_by'] = $data['issued_by'] ?? $auth->id;
        $payload['created_by'] = $auth->id;

        if (!empty($data['supporting_document']) && $data['supporting_document'] instanceof UploadedFile) {
            $payload['supporting_document'] = $data['supporting_document']->store('award-documents', 'local');
        }

        return Award::create($payload)->load(self::RELATIONS);
    }

    public function update(int $id, array $data): Award
    {
        $award = $this->find($id);
        $payload = $this->basePayload($data);

        if (!empty($data['supporting_document']) && $data['supporting_document'] instanceof UploadedFile) {
            if ($award->supporting_document) {
                Storage::disk('local')->delete($award->supporting_document);
            }
            $payload['supporting_document'] = $data['supporting_document']->store('award-documents', 'local');
        }

        $award->update($payload);
        return $award->fresh(self::RELATIONS);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function documentPath(int $id): ?string
    {
        return $this->find($id)->supporting_document;
    }

    protected function basePayload(array $data): array
    {
        return [
            'award_type_id' => $data['award_type_id'] ?? null,
            'award_date' => $data['award_date'] ?? null,
            'reason' => $data['reason'] ?? null,
            'monetary_value' => $data['monetary_value'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'issued',
            'notes' => $data['notes'] ?? null,
        ];
    }
}
