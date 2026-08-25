<?php

namespace App\Repositories\Contracts;

interface SaleRepositoryInterface
{
    public function createPosSale(array $data);

    /**
     * @return array{paginator: \Illuminate\Contracts\Pagination\LengthAwarePaginator, stats: array}
     */
    public function getAllSales(array $data): array;

    public function getSale($id);

    public function getSaleByProduct($id);

    public function recordPayment($id, array $data);
}
