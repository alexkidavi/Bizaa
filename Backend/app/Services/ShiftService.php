<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Support\Facades\Auth;

class ShiftService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return Shift::where('business_id', Auth::user()->business_id)
            ->withCount('staff')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Shift
    {
        return Shift::create([
            'business_id' => Auth::user()->business_id,
            'name'        => $data['name'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(int $id, array $data): Shift
    {
        $shift = $this->find($id);
        $shift->update([
            'name'        => $data['name'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'description' => $data['description'] ?? null,
        ]);
        return $shift->fresh()->loadCount('staff');
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Shift
    {
        return Shift::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
