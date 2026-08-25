<?php
namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchRepository
{
    public function all()
    {
        $user = Auth::user();
        return Branch::with(['user:id,name'])->where('business_id', $user->business_id)->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Branch::where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        return Branch::create($data);
    }

    public function updateStatus($id, $status)
    {
        $branch            = $this->find($id);
        $branch->is_active = $status;
        $branch->save();
        return $branch;
    }

    public function updateBranch($id, array $data)
    {
        $branch = $this->find($id);
        $branch->update($data);
        return $branch;
    }

    public function delete($id)
    {
        $branch = $this->find($id);
        return $branch->delete();
    }
}
