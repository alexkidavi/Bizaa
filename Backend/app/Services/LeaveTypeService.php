<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;

class LeaveTypeService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return LeaveType::where('business_id', Auth::user()->business_id)
            ->withCount('leaves')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create([
            'business_id'  => Auth::user()->business_id,
            'name'         => $data['name'],
            'days_allowed' => $data['days_allowed'],
            'description'  => $data['description'] ?? null,
        ]);
    }

    public function update(int $id, array $data): LeaveType
    {
        $lt = $this->find($id);
        $lt->update([
            'name'         => $data['name'],
            'days_allowed' => $data['days_allowed'],
            'description'  => $data['description'] ?? null,
        ]);
        return $lt->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): LeaveType
    {
        return LeaveType::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
