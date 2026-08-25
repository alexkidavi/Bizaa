<?php

namespace App\Services;

use App\Repositories\EcommerceStoreRepository;

class EcommerceStoreService
{
    public function __construct(protected EcommerceStoreRepository $repo) {}

    public function getAllStores()
    {
        return $this->repo->all();
    }

    public function getStore($id)
    {
        return $this->repo->find($id);
    }

    public function createStore(array $data)
    {
        return $this->repo->create($data);
    }

    public function updateStore($id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteStore($id): void
    {
        $this->repo->delete($id);
    }

    public function getStoreProducts($storeId): array
    {
        return $this->repo->getStoreProducts($storeId);
    }

    public function toggleProductListing($storeId, $productBranchId, bool $isListed)
    {
        return $this->repo->toggleProductListing($storeId, $productBranchId, $isListed);
    }

    public function bulkToggleListing($storeId, array $productBranchIds, bool $isListed): int
    {
        return $this->repo->bulkToggleListing($storeId, $productBranchIds, $isListed);
    }
}
