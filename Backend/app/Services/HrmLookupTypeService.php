<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared CRUD for the simple, business-scoped "type" lookup tables
 * (AwardType, WarningType, TerminationType) — identical business logic
 * across all three, so it's written once and parametrized by model
 * class rather than tripled into three near-identical services.
 */
class HrmLookupTypeService
{
    public function __construct(protected string $modelClass) {}

    public function list(): Collection
    {
        return $this->modelClass::where('business_id', Auth::user()->business_id)
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): Model
    {
        return $this->modelClass::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): Model
    {
        return $this->modelClass::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): Model
    {
        $type = $this->find($id);
        $type->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $type;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}
