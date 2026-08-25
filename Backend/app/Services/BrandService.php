<?php

namespace App\Services;

use App\Repositories\BrandRepository;

class BrandService
{
    protected $repo;

    public function __construct(BrandRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllBrands()
    {
        return $this->repo->all();
    }

    public function getBrand($id)
    {
        return $this->repo->find($id);
    }



    public function createBrand($data)
    {
        return $this->repo->create($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }
 public function updateBrand($id, array $data)
    {
        return $this->repo->updateBrand($id, $data);
    }

    public function deleteBrand($id)
    {
        return $this->repo->delete($id);
    }
}
