<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(protected DashboardRepository $repo) {
         $this->repo = $repo;
    }

public function getDashboardData(string $filter, int $businessId, ?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
{
    $this->repo->setCustomRange($startDate, $endDate);

    return [
        'stats'               => $this->repo->getStats($filter, $businessId, $branchId),
        'chart'               => $this->repo->getChartData($filter, $businessId, $branchId),
        'top_products'        => $this->repo->getTopProducts($filter, $businessId, $branchId),
        'recent_transactions' => $this->repo->getRecentTransactions($businessId, $branchId),
        'low_stock'           => $this->repo->getLowStock($businessId, $branchId),
        'today_summary'        => $this->repo->getTodaySummary($businessId, $branchId),
        'top_branches'         => $this->repo->getTopBranches($filter, $businessId),
        'outstanding_balance'  => $this->repo->getOverallOutstanding($businessId, $branchId),
        'onboarding'           => $this->repo->getOnboardingStatus($businessId),
    ];
}
}