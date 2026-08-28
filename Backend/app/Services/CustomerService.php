<?php

namespace App\Services;

use App\Repositories\CustomerRepository;

class CustomerService
{
    protected $repo;

    public function __construct(CustomerRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getAllCustomers()
    {
        return $this->repo->all();
    }

    public function searchForPos(?string $search)
    {
        return $this->repo->searchForPos($search);
    }



    public function createCustomer($data)
    {
        return $this->repo->create($data);
    }

 public function getCustomer($id)
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
 public function updateCustomer($id, array $data)
    {
        return $this->repo->updateCustomer($id, $data);
    }

    public function deleteCustomer($id)
    {
        return $this->repo->delete($id);
    }
}
