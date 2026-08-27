<?php

namespace App\Services;

use App\Models\CrmTag;
use Illuminate\Support\Facades\Auth;

class CrmTagService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return CrmTag::where('business_id', Auth::user()->business_id)
            ->withCount('contacts')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): CrmTag
    {
        return CrmTag::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }

    public function create(array $data): CrmTag
    {
        return CrmTag::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#229DE7',
        ]);
    }

    public function update(int $id, array $data): CrmTag
    {
        $tag = $this->find($id);
        $tag->update(['name' => $data['name'], 'color' => $data['color'] ?? $tag->color]);
        return $tag->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}
