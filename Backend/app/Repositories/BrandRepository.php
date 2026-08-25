<?php
namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;

class BrandRepository
{
    public function all()
    {
        $user = Auth::user();
        return Brand::with([
            'user:id,name',
            'branches:id,name'
        ])->where('business_id', $user->business_id)->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Brand::where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $brand = Brand::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'user_id'     => $data['user_id'],
            'business_id' => $data['business_id'],
        ]);

        if (!empty($data['branch_ids'])) {
            $brand->branches()->sync($data['branch_ids']);
        }

        return $brand;
    }

    public function updateStatus($id, $status)
    {
        $brand            = $this->find($id);
        $brand->is_active = $status;
        $brand->save();
        return $brand;
    }

    public function updateBrand($id, array $data)
    {
        $brand = $this->find($id);
        $brand->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (isset($data['branch_ids'])) {
            $brand->branches()->sync($data['branch_ids']);
        }

        return $brand->load('branches');
    }

    public function delete($id)
    {
        $brand = $this->find($id);
        return $brand->delete();
    }
}
