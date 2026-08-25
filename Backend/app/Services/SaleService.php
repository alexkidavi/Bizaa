<?php

namespace App\Services;

use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\DigitaxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class SaleService
{
    public function __construct(protected SaleRepositoryInterface $repo, protected DigitaxService $digitax)
    {
    }

    public function createPosSale(array $data)
    {
        $data['user_id'] = Auth::user()->id;
        $data['business_id'] = Auth::user()->business_id;

        $sale = $this->repo->createPosSale($data);
        $this->digitax->syncSale($sale);

        return $sale->fresh(['items', 'payments']);
    }

    public function getAllSales(array $data): array
    {
        $result = $this->repo->getAllSales($data);
        $paginator = $result['paginator'];

        $transformed = collect($paginator->items())->map(function ($sale) {
            $paid = $sale->paid_amount ?? 0;
            $balance = $sale->total - $paid;

            $status = match (true) {
                $paid == 0 => 'unpaid',
                $paid == $sale->total => 'paid',
                $paid < $sale->total => 'partial',
                $paid > $sale->total => 'overpaid',
            };

            $sale->sale_id = Crypt::encryptString($sale->id);
            $sale->paid_amount = round($paid, 2);
            $sale->balance = round($balance, 2);
            $sale->status = $status;

            return $sale;
        });

        return [
            'data' => $transformed,
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'stats' => $result['stats'],
        ];
    }

    public function getSale($id)
    {
        return $this->repo->getSale($id);
    }

    public function getSaleByProduct($id)
    {
        return $this->repo->getSaleByProduct($id);
    }

    public function recordPayment($id, array $data)
    {
        return $this->repo->recordPayment($id, $data);
    }
}
