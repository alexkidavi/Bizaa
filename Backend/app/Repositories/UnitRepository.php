<?php
namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitRepository
{
    public function all()
    {
        $user = Auth::user();
        return Unit::with([
            'user:id,name',
            'branches:id,name'
        ])->where('business_id', $user->business_id)->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Unit::where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $unit = Unit::create([
            'name'         => $data['name'],
            'abbreviation' => $data['abbreviation'] ?? null,
            'description'  => $data['description'] ?? null,
            'user_id'      => $data['user_id'],
            'business_id'  => $data['business_id'],
        ]);

        if (!empty($data['branch_ids'])) {
            $unit->branches()->sync($data['branch_ids']);
        }

        return $unit;
    }

    public function updateStatus($id, $status)
    {
        $unit            = $this->find($id);
        $unit->is_active = $status;
        $unit->save();
        return $unit;
    }

    public function updateUnit($id, array $data)
    {
        $unit = $this->find($id);
        $unit->update([
            'abbreviation' => $data['abbreviation'] ?? null,
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
        ]);

        if (isset($data['branch_ids'])) {
            $unit->branches()->sync($data['branch_ids']);
        }

        return $unit->load('branches');
    }

    public function delete($id)
    {
        $unit = $this->find($id);
        return $unit->delete();
    }
}
