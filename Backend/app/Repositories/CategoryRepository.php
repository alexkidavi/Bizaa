<?php
namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryRepository
{
    public function all()
    {
        $user = Auth::user();
        return Category::with([
            'user:id,name',
            'branches:id,name'
        ])->where('business_id', $user->business_id)->get();
    }

    public function find($id)
    {
        $user = Auth::user();
        return Category::where('id', $id)
            ->where('business_id', $user->business_id)
            ->firstOrFail();
    }

    public function create(array $data)
    {
        $category = Category::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'user_id'     => $data['user_id'],
            'business_id' => $data['business_id'],
        ]);

        if (!empty($data['branch_ids'])) {
            $category->branches()->sync($data['branch_ids']);
        }

        return $category;
    }

    public function updateStatus($id, $status)
    {
        $category            = $this->find($id);
        $category->is_active = $status;
        $category->save();
        return $category;
    }

    public function updateCategory($id, array $data)
    {
        $category = $this->find($id);
        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (isset($data['branch_ids'])) {
            $category->branches()->sync($data['branch_ids']);
        }

        return $category->load('branches');
    }

    public function delete($id)
    {
        $category = $this->find($id);
        return $category->delete();
    }
}
