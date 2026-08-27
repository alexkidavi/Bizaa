<?php

namespace App\Services;

use App\Models\CrmSource;
use Illuminate\Support\Facades\Auth;

class CrmSourceService
{
    public function list(bool $activeOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = CrmSource::where('business_id', Auth::user()->business_id)
            ->withCount('contacts')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function find(int $id): CrmSource
    {
        return CrmSource::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): CrmSource
    {
        return CrmSource::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): CrmSource
    {
        $source = $this->find($id);
        $source->update(['name' => $data['name'], 'is_active' => $data['is_active'] ?? $source->is_active]);
        return $source->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    /**
     * Seeded once per business (new registrations + backfilled for
     * existing ones) — an editable starting point, same as Banks and
     * Performance Ratings this session, not hardcoded logic.
     */
    public function seedDefaults(int $businessId): void
    {
        $defaults = ['Referral', 'Walk-in', 'Website', 'Social Media', 'Phone Inquiry', 'Advertisement'];

        foreach ($defaults as $name) {
            CrmSource::firstOrCreate(['business_id' => $businessId, 'name' => $name]);
        }
    }
}
