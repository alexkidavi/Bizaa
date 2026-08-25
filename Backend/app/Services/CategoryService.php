<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    protected $repo;

    public function __construct(CategoryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllCategories()
    {
        return $this->repo->all();
    }

    public function getCategory($id)
    {
        return $this->repo->find($id);
    }



    public function createCategory($data)
    {
        return $this->repo->create($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }
 public function updateCategory($id, array $data)
    {
        return $this->repo->updateCategory($id, $data);
    }

    public function deleteCategory($id)
    {
        return $this->repo->delete($id);
    }
}
