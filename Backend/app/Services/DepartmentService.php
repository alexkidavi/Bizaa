<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentService
{
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return Department::where('business_id', Auth::user()->business_id)
            ->withCount('staff')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Department
    {
        return Department::create([
            'business_id' => Auth::user()->business_id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => Auth::id(),
        ]);
    }

    public function update(int $id, array $data): Department
    {
        $dept = $this->find($id);
        $dept->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return $dept->fresh()->loadCount('staff');
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): Department
    {
        return Department::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
