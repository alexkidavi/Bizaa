<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPointTransaction;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public function __construct(protected LoyaltySettingService $settingsService)
    {
    }

    /**
     * Credits points for a completed sale to a registered customer, based
     * on the business's earn rate (earn_points_awarded per every
     * earn_amount_spent of the sale total). No-op if loyalty is disabled,
     * the earn rate isn't configured, or the sale has no customer — a
     * walk-in sale never earns points, per the feature's own scope.
     */
    public function earnForSale(Sale $sale): void
    {
        if (!$sale->customer_id) {
            return;
        }

        $settings = $this->settingsService->getOrCreate();
        $spendThreshold = (float) $settings->earn_amount_spent;
        if (!$settings->is_enabled || $spendThreshold <= 0) {
            return;
        }

        $pointsEarned = (int) floor((float) $sale->total / $spendThreshold) * $settings->earn_points_awarded;
        if ($pointsEarned <= 0) {
            return;
        }

        $this->applyLedgerEntry(
            customerId: $sale->customer_id,
            businessId: $sale->business_id,
            type: 'earn',
            pointsDelta: $pointsEarned,
            saleId: $sale->id,
            note: "Earned from sale {$sale->reference}"
        );
    }

    /**
     * Checks whether a customer can cover a given cash amount with points
     * without changing anything — used by the POS "Pay with Points" UI to
     * show live feedback before charging, and by redeemForSale() to
     * validate before it commits.
     */
    public function previewRedeem(int $customerId, float $amount): array
    {
        $customer = Customer::where('id', $customerId)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();

        $settings = $this->settingsService->getOrCreate();
        $balance = $customer->loyalty_points_balance;
        $pointValue = (float) $settings->point_value;
        $availableCash = round($balance * $pointValue, 2);
        $pointsRequired = $pointValue > 0 && $amount > 0 ? (int) ceil($amount / $pointValue) : 0;
        $meetsMinimum = $balance >= $settings->min_redeem_points;

        return [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'enabled' => $settings->is_enabled,
            'balance' => $balance,
            'point_value' => $pointValue,
            'available_cash' => $availableCash,
            'min_redeem_points' => $settings->min_redeem_points,
            'meets_minimum' => $meetsMinimum,
            'points_required' => $pointsRequired,
            'can_cover' => $settings->is_enabled && $meetsMinimum && $amount > 0 && $balance >= $pointsRequired,
        ];
    }

    /**
     * Redeems exactly enough points to cover $amount in cash and records
     * both the ledger entry and the resulting SalePayment row. Runs
     * inside the caller's existing transaction — SaleRepository::createPosSale()
     * already wraps the whole sale in DB::transaction(), and this needs
     * to roll back with it if the customer can't actually cover the
     * amount. That "can they cover it" check IS the "convert to cash if
     * enough" boundary: enough → redeem and pay; not enough → throw with
     * a message naming exactly how short they are.
     */
    public function redeemForSale(?int $customerId, float $amount, int $saleId, int $businessId, int $userId): SalePayment
    {
        $settings = $this->settingsService->getOrCreate();

        if (!$settings->is_enabled) {
            throw ValidationException::withMessages(['payment_method' => ['Loyalty points are not enabled for this business.']]);
        }

        if (!$customerId) {
            throw ValidationException::withMessages(['customer_id' => ['A registered customer is required to pay with points.']]);
        }

        $pointValue = (float) $settings->point_value;
        if ($pointValue <= 0) {
            throw ValidationException::withMessages(['payment_method' => ['Loyalty point value is not configured — set it under Settings > Loyalty.']]);
        }

        // Locked for the rest of this transaction so two concurrent
        // checkouts for the same customer can't both read the same
        // balance and both redeem against it — same discipline as
        // SaleRepository::createPosSale()'s ProductBranch stock lock.
        $customer = Customer::where('id', $customerId)
            ->where('business_id', $businessId)
            ->lockForUpdate()
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages(['customer_id' => ['Customer not found for this business.']]);
        }

        if ($customer->loyalty_points_balance < $settings->min_redeem_points) {
            throw ValidationException::withMessages(['payment_method' => [
                "{$customer->name} has {$customer->loyalty_points_balance} points, below the minimum of {$settings->min_redeem_points} required to redeem.",
            ]]);
        }

        $pointsRequired = (int) ceil($amount / $pointValue);
        $availableCash = round($customer->loyalty_points_balance * $pointValue, 2);

        if ($customer->loyalty_points_balance < $pointsRequired) {
            throw ValidationException::withMessages(['payment_method' => [
                "{$customer->name} only has {$customer->loyalty_points_balance} points (worth KES " . number_format($availableCash, 2)
                    . "), not enough to cover KES " . number_format($amount, 2) . ".",
            ]]);
        }

        $newBalance = $customer->loyalty_points_balance - $pointsRequired;
        $customer->update(['loyalty_points_balance' => $newBalance]);

        LoyaltyPointTransaction::create([
            'business_id' => $businessId,
            'customer_id' => $customer->id,
            'sale_id' => $saleId,
            'type' => 'redeem',
            'points' => -$pointsRequired,
            'balance_after' => $newBalance,
            'note' => "Redeemed as payment on sale #{$saleId}",
            'created_by' => $userId,
        ]);

        return SalePayment::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'sale_id' => $saleId,
            'reference' => "LOYALTY-{$pointsRequired}PTS",
            'method' => 'points',
            'amount' => $amount,
            'note' => "{$pointsRequired} points redeemed",
        ]);
    }

    /**
     * Manual staff adjustment — award or deduct points with a reason,
     * from the CRM Loyalty page. $pointsDelta may be negative.
     */
    public function adjust(int $customerId, int $pointsDelta, string $note): LoyaltyPointTransaction
    {
        if ($pointsDelta === 0) {
            throw ValidationException::withMessages(['points' => ['Enter a non-zero number of points.']]);
        }

        return $this->applyLedgerEntry(
            customerId: $customerId,
            businessId: Auth::user()->business_id,
            type: 'adjustment',
            pointsDelta: $pointsDelta,
            saleId: null,
            note: $note
        );
    }

    protected function applyLedgerEntry(int $customerId, int $businessId, string $type, int $pointsDelta, ?int $saleId, string $note): LoyaltyPointTransaction
    {
        return DB::transaction(function () use ($customerId, $businessId, $type, $pointsDelta, $saleId, $note) {
            $customer = Customer::where('id', $customerId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $newBalance = $customer->loyalty_points_balance + $pointsDelta;
            if ($newBalance < 0) {
                throw ValidationException::withMessages(['points' => [
                    "This would take {$customer->name}'s balance below zero (currently {$customer->loyalty_points_balance} points).",
                ]]);
            }

            $customer->update(['loyalty_points_balance' => $newBalance]);

            return LoyaltyPointTransaction::create([
                'business_id' => $businessId,
                'customer_id' => $customer->id,
                'sale_id' => $saleId,
                'type' => $type,
                'points' => $pointsDelta,
                'balance_after' => $newBalance,
                'note' => $note,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function listCustomersWithBalances(array $filters = []): LengthAwarePaginator
    {
        $query = Customer::where('business_id', Auth::user()->business_id)
            ->orderByDesc('loyalty_points_balance');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function history(int $customerId, int $perPage = 20): LengthAwarePaginator
    {
        return LoyaltyPointTransaction::where('business_id', Auth::user()->business_id)
            ->where('customer_id', $customerId)
            ->with(['sale:id,reference', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
