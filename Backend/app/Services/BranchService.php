<?php

namespace App\Services;

use App\Repositories\BranchRepository;

class BranchService
{
    protected $repo;

    public function __construct(BranchRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllBranches()
    {
        return $this->repo->all();
    }

    public function getBranch($id)
    {
        return $this->repo->find($id);
    }



    public function createBranch($data)
    {
        return $this->repo->create($data);
    }

    public function updateStatus($id, $status)
    {
        return $this->repo->updateStatus($id, $status);
    }
 public function updateBranch($id, array $data)
    {
        return $this->repo->updateBranch($id, $data);
    }

    public function deleteBranch($id)
    {
        return $this->repo->delete($id);
    }
}
