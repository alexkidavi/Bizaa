<?php

namespace App\Services;

use App\Repositories\SupplierRepository;

class SupplierService
{
    protected $repo;

    public function __construct(SupplierRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllSuppliers()
    {
        return $this->repo->all();
    }



    public function createSupplier($data)
    {
        return $this->repo->create($data);
    }

 public function getSupplier($id)
    {
        return $this->repo->find($id);
    }
     public function getSales($id)
    {
        return $this->repo->getSales($id);
    }
     public function getPayments($id)
    {
        return $this->repo->getPayments($id);
    }
    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }
 public function updateSupplier($id, array $data)
    {
        return $this->repo->updateSupplier($id, $data);
    }

    public function deleteSupplier($id)
    {
        return $this->repo->delete($id);
    }
}
