<?php

namespace App\Services;

use App\Models\Business;
use App\Models\MpesaStk;
use App\Models\SubscriptionPlan;
use App\Models\BusinessSubscription;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class MpesaService
{
    /**
     * Every business supplies its own Daraja app (consumer key/secret) and
     * till/paybill (shortcode/passkey) via Settings → M-Pesa Payments —
     * POS checkout STK pushes must use THAT business's credentials, never
     * a shared/global one, or every business's customer payments would
     * land in the same account. This check gates every POS-facing method
     * below. Platform subscription billing (initiateSubscriptionStkPush)
     * is intentionally exempt — that's Bizaa collecting its own revenue
     * and correctly uses the platform's own Daraja app from config/.env.
     */
    protected function isConfigured(?Business $business): bool
    {
        if (! $business) {
            info('MPESA SKIPPED: no business record');
            return false;
        }

        if (! $business->mpesa_shortcode || ! $business->mpesa_passkey) {
            info('MPESA SKIPPED: shortcode/passkey not saved', ['business_id' => $business->id]);
            return false;
        }

        if (! $business->mpesa_consumer_key || ! $business->mpesa_consumer_secret) {
            info('MPESA SKIPPED: consumer key/secret not saved', ['business_id' => $business->id]);
            return false;
        }

        return true;
    }

    protected function maskSecret(?string $value): string
    {
        if (! $value) {
            return '(empty)';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . '...' . substr($value, -4) . ' (' . strlen($value) . ' chars)';
    }

    protected function accessToken(Business $business): ?string
    {
        $authUrl = config('services.mpesa.auth_url');

        info('MPESA ACCESS TOKEN: requesting', [
            'business_id' => $business->id,
            'auth_url' => $authUrl,
            'consumer_key' => $this->maskSecret($business->mpesa_consumer_key),
        ]);

        $response = Http::withBasicAuth($business->mpesa_consumer_key, $business->mpesa_consumer_secret)
            // TLS verification is only skipped in local/testing (e.g. a
            // sandbox with a self-signed cert during development) — never
            // in an environment holding real M-Pesa credentials.
            ->when(app()->environment('local', 'testing'), fn ($http) => $http->withoutVerifying())
            ->get($authUrl);
        /** @var \Illuminate\Http\Client\Response $response */
        if (! $response->successful()) {
            info('MPESA ACCESS TOKEN FAILED', [
                'business_id' => $business->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Perform an STK Push against the authenticated user's own business
     * credentials — used for POS checkout. Throws with a clear, user-facing
     * message if the business hasn't configured M-Pesa yet, so the
     * controller can surface it instead of a generic 500.
     */
    public function initiateStkPush(array $data): string
    {
        $business = Auth::user()->business;

        if (! $this->isConfigured($business)) {
            throw new \RuntimeException('M-Pesa is not set up for this business yet. Go to Settings → M-Pesa Payments and save your paybill/till, passkey, and Daraja consumer key/secret.');
        }

        $timestamp = now()->format('YmdHis');

        $shortcode = $business->mpesa_shortcode;
        $passkey = $business->mpesa_passkey;
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $accountReference = $data['account_reference'];
        $callbackUrl = config('services.mpesa.callback_url') . '?code=' . config('services.mpesa.callback_secret');
        $phone = $data['phone'];
        $amount = (int) $data['amount'];

        // Paybill vs Till number use different Safaricom transaction types.
        $transactionType = $business->mpesa_shortcode_type === 'till'
            ? 'CustomerBuyGoodsOnline'
            : 'CustomerPayBillOnline';

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $transactionType,
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $data['transaction_desc'] ?? 'POS Sale',
        ];

        info('MPESA STK PUSH: sending request', [
            'business_id' => $business->id,
            'shortcode' => $shortcode,
            'shortcode_type' => $business->mpesa_shortcode_type,
            'endpoint' => config('services.mpesa.stk_url'),
            'payload' => array_merge($payload, ['Password' => $this->maskSecret($password)]),
        ]);

        $response = Http::withToken($this->accessToken($business))
            // TLS verification is only skipped in local/testing (e.g. a
            // sandbox with a self-signed cert during development) — never
            // in an environment holding real M-Pesa credentials.
            ->when(app()->environment('local', 'testing'), fn ($http) => $http->withoutVerifying())
            ->post(config('services.mpesa.stk_url'), $payload);

        $stkData = $response->json() ?? [];

        info('MPESA STK PUSH: response received', [
            'business_id' => $business->id,
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $stkData,
        ]);

        // Store STK push in database, tied to this business
        MpesaStk::create([
            'merchant_request_id' => $stkData['MerchantRequestID'] ?? null,
            'checkout_request_id' => $stkData['CheckoutRequestID'] ?? null,
            'phone_number' => $phone,
            'amount' => $amount,
            'reference' => $accountReference,
            'transaction_desc' => $payload['TransactionDesc'],
            'response_code' => $stkData['ResponseCode'] ?? null,
            'response_description' => $stkData['ResponseDescription'] ?? null,
            'customer_message' => $stkData['CustomerMessage'] ?? null,
            'status' => 0,
            'business_id' => $business->id,
            'raw_response' => $stkData,
        ]);

        if (! $response->successful() || empty($stkData['CheckoutRequestID'])) {
            throw new \RuntimeException($stkData['errorMessage'] ?? $stkData['ResponseDescription'] ?? 'Failed to send STK push. Please try again.');
        }

        return $stkData['CheckoutRequestID'];
    }

 public function handleStkCallback(array $data)
{
    info('M-PESA CALLBACK RECEIVED', $data);

    $stkCallback = data_get($data, 'Body.stkCallback');

    if (! $stkCallback) {
        return;
    }

    $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
    $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
    $resultCode = $stkCallback['ResultCode'] ?? null;
    $resultDesc = $stkCallback['ResultDesc'] ?? null;

    // 🔎 Find STK record
    $order = MpesaStk::where('merchant_request_id', $merchantRequestId)->first();

    if (! $order) {
        info('STK order not found', ['merchant_request_id' => $merchantRequestId]);
        return;
    }

    // ❌ FAILED PAYMENT
    if ($resultCode != 0) {
        $order->update([
            'status' => 2,
            'response_code' => $resultCode,
            'response_description' => $resultDesc,
        ]);
        return;
    }

    // ✅ SUCCESS PAYMENT
    $metadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
    $parsed = $this->parseCallbackMetadata($metadata);

    if (! $parsed['amount'] || ! $parsed['receipt']) {
        info('Invalid callback metadata', $metadata);
        return;
    }

    DB::transaction(function () use ($order, $parsed, $checkoutRequestId, $resultCode, $resultDesc) {

        // 🔐 Prevent duplicate processing
        $alreadyPaid = DB::table('mpesa_payments')
            ->where('checkout_request_id', $checkoutRequestId)
            ->exists();

        if ($alreadyPaid) {
            return;
        }

        // 💾 Save payment record — tied to the business that initiated the
        // STK push, resolved from the MpesaStk row (not a generic/shared row).
        DB::table('mpesa_payments')->insert([
            'business_id'           => $order->business_id,
            'amount'               => $parsed['amount'],
            'mpesa_receipt_number' => $parsed['receipt'],
            'transaction_date'     => now(),
            'phonenumber'          => $parsed['phone'],
            'checkout_request_id'  => $checkoutRequestId,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // 💳 Mark STK success
        $order->update([
            'status'               => 1,
            'response_code'        => $resultCode,
            'response_description' => $resultDesc,
        ]);

        // 🔔 If this is a subscription payment, activate the plan
        if ($order->subscription_plan_id && $order->business_id) {
            $this->activateSubscription(
                $order->business_id,
                $order->subscription_plan_id,
                $parsed['receipt'],
                $parsed['phone'],
                $parsed['amount']
            );
        }
    });
}

    /**
     * Create / extend the subscription after a successful M-Pesa payment.
     */
    protected function activateSubscription(
        int $businessId,
        int $planId,
        string $receiptNumber,
        string $phone,
        float $amount
    ): void {
        $plan = SubscriptionPlan::find($planId);
        if (! $plan) {
            info('Subscription activation failed: plan not found', ['plan_id' => $planId]);
            return;
        }

        // If there is still an active subscription, queue the new one after it
        $current = BusinessSubscription::activeForBusiness($businessId)->first();
        $startsAt = $current ? $current->ends_at : now();
        $endsAt   = (clone $startsAt)->addDays($plan->duration_days);

        BusinessSubscription::create([
            'business_id'       => $businessId,
            'plan_id'           => $planId,
            'starts_at'         => $startsAt,
            'ends_at'           => $endsAt,
            'status'            => 'active',
            'amount_paid'       => $amount,
            'payment_method'    => 'mpesa',
            'payment_reference' => $receiptNumber,
            'notes'             => "Auto-activated via M-Pesa STK. Phone: {$phone}",
        ]);

        info('Subscription activated', [
            'business_id' => $businessId,
            'plan'        => $plan->slug,
            'receipt'     => $receiptNumber,
            'ends_at'     => $endsAt,
        ]);
    }

    /**
     * Initiate an STK push specifically for a subscription payment.
     * Stores subscription_plan_id so the callback can activate the plan.
     */
    public function initiateSubscriptionStkPush(array $data): string
    {
        $timestamp  = now()->format('YmdHis');
        $shortcode  = config('services.mpesa.shortcode');
        $passkey    = config('services.mpesa.passkey');
        $password   = base64_encode($shortcode . $passkey . $timestamp);
        $callbackUrl = config('services.mpesa.callback_url') . '?code=' . config('services.mpesa.callback_secret');

        $phone      = $data['phone'];
        $amount     = (int) $data['amount'];
        $planSlug   = $data['plan_slug'] ?? 'subscription';
        $planId     = $data['plan_id'];
        $businessId = $data['business_id'];
        $accountRef = strtoupper("BRIM-{$planSlug}");

        $response = Http::withToken($this->accessToken())
            // TLS verification is only skipped in local/testing (e.g. a
            // sandbox with a self-signed cert during development) — never
            // in an environment holding real M-Pesa credentials.
            ->when(app()->environment('local', 'testing'), fn ($http) => $http->withoutVerifying())
            ->post(config('services.mpesa.stk_url'), [
                'BusinessShortCode' => $shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => $amount,
                'PartyA'            => $phone,
                'PartyB'            => $shortcode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $callbackUrl,
                'AccountReference'  => $accountRef,
                'TransactionDesc'   => "Bizaa Subscription",
            ]);

        $stkData = $response->json();

        MpesaStk::create([
            'business_id'          => $businessId,
            'subscription_plan_id' => $planId,
            'merchant_request_id'  => $stkData['MerchantRequestID'] ?? null,
            'checkout_request_id'  => $stkData['CheckoutRequestID'] ?? null,
            'phone_number'         => $phone,
            'amount'               => $amount,
            'reference'            => $accountRef,
            'transaction_desc'     => 'Bizaa Subscription',
            'response_code'        => $stkData['ResponseCode'] ?? null,
            'response_description' => $stkData['ResponseDescription'] ?? null,
            'customer_message'     => $stkData['CustomerMessage'] ?? null,
            'status'               => 0,
            'raw_response'         => $stkData,
        ]);

        info('SUBSCRIPTION STK PUSH', [
            'business_id' => $businessId,
            'plan_id'     => $planId,
            'response'    => $stkData,
        ]);

        return $stkData['CheckoutRequestID'] ?? '';
    }
private function parseCallbackMetadata(array $items)
{
    $data = [
        'amount' => null,
        'receipt' => null,
        'phone' => null,
    ];

    foreach ($items as $item) {
        switch ($item['Name']) {
            case 'Amount':
                $data['amount'] = $item['Value'];
                break;
            case 'MpesaReceiptNumber':
                $data['receipt'] = $item['Value'];
                break;
            case 'PhoneNumber':
                $data['phone'] = $item['Value'];
                break;
        }
    }

    return $data;
}
    protected function processPayment(Business $business, array $paymentData): void
    {
        $this->transactionStatusQuery($business, $paymentData['receipt']);
    }

    protected function transactionStatusQuery(Business $business, string $transactionId): array
    {
        // Routed through config() rather than env() — env() returns null
        // for anything not read during the framework's own bootstrap once
        // `config:cache` has run in production, and PartyA/credentials are
        // the tenant's own (matching every other method in this class),
        // not a single shared platform value.
        $token = $this->accessToken($business);

        /** @var Response $response */
        $response = Http::withToken($token)
            ->post(config('services.mpesa.transaction_status_url'), [
                'Initiator' => config('services.mpesa.initiator_name'),
                'SecurityCredential' => config('services.mpesa.security_credential'),
                'CommandID' => 'TransactionStatusQuery',
                'TransactionID' => $transactionId,
                'PartyA' => $business->mpesa_shortcode,
                'IdentifierType' => '4',
                'ResultURL' => config('services.mpesa.result_url'),
                'QueueTimeOutURL' => config('services.mpesa.timeout_url'),
                'Remarks' => 'Direct env query',
                'Occasion' => 'Verification',
            ]);

        logger()->info('MPESA TRANSACTION QUERY RESPONSE', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return $response->json();
    }
   

/**
 * $businessId scopes the lookup to the requesting business — without it,
 * a guessed/reused checkout_request_id from another business would leak
 * that business's transaction status.
 */
public function checkStkStatus(string $checkoutRequestId, ?int $businessId = null)
{
    $query = MpesaStk::where('checkout_request_id', $checkoutRequestId);

    if ($businessId) {
        $query->where('business_id', $businessId);
    }

    $stk = $query->first();

    if (! $stk) {
        return [
            'status' => 'not_found',
            'message' => 'Transaction not found',
        ];
    }

    // The actual receipt number only exists once the callback has landed
    // and written a mpesa_payments row — surface it so the frontend can
    // show/store the real code instead of a blank field.
    $mpesaCode = $stk->status === 1
        ? DB::table('mpesa_payments')->where('checkout_request_id', $checkoutRequestId)->value('mpesa_receipt_number')
        : null;

    return [
        'status' => $this->mapStatus($stk->status),
        'amount' => $stk->amount,
        'phone' => $stk->phone_number,
        'checkout_request_id' => $stk->checkout_request_id,
        'response_code' => $stk->response_code,
        'response_description' => $stk->response_description,
        'mpesa_code' => $mpesaCode,
        'paid' => $stk->status === 1,
        'failed' => $stk->status === 2,
    ];
}

/**
 * Recent confirmed STK payments for this business — powers the POS
 * "Sync Payments" manual-match tab. Scoped by business_id (set on the
 * mpesa_payments row from the originating MpesaStk at callback time).
 */
public function getRecentPayments(int $businessId, int $minutes = 240): array
{
    return DB::table('mpesa_payments')
        ->where('business_id', $businessId)
        ->where('created_at', '>=', now()->subMinutes($minutes))
        ->orderByDesc('created_at')
        ->limit(50)
        ->get()
        ->map(fn ($row) => [
            'id' => $row->id,
            'phone' => $row->phonenumber,
            'amount' => (float) $row->amount,
            'mpesa_code' => $row->mpesa_receipt_number,
            'checkout_request_id' => $row->checkout_request_id,
            'time' => $row->transaction_date ?? $row->created_at,
        ])
        ->all();
}
private function mapStatus($status)
{
    return match ($status) {
        0 => 'pending',
        1 => 'completed',
        2 => 'failed',
        default => 'unknown',
    };
}
}
