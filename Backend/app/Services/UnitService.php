<?php

namespace App\Services;

use App\Repositories\UnitRepository;

class UnitService
{
    protected $repo;

    public function __construct(UnitRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllUnits()
    {
        return $this->repo->all();
    }

    public function getUnit($id)
    {
        return $this->repo->find($id);
    }



    public function createUnit($data)
    {
        return $this->repo->create($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }
 public function updateUnit($id, array $data)
    {
        return $this->repo->updateUnit($id, $data);
    }

    public function deleteUnit($id)
    {
        return $this->repo->delete($id);
    }
}
