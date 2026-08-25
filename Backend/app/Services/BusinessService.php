<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\BusinessRepository;

class BusinessService
{
    public function __construct(protected BusinessRepository $repo)
    {
    }

    /**
     * The list of secret fields that can be revealed via revealSecret() —
     * kept in one place so the controller can validate against it too.
     */
    public const SECRET_FIELDS = [
        'mpesa_passkey',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'digitax_api_key',
    ];

    /**
     * Masked settings shape for the frontend — secret fields are never
     * returned in full, only whether one is currently set. The full value
     * is never shipped on page load; revealSecret() below returns a
     * masked preview only, fetched on demand when the owner clicks "Show".
     */
    public function getSettings(User $user): array
    {
        $business = $user->business;

        if (!$business) {
            return [];
        }

        return [
            'id' => $business->id,
            'business_name' => $business->business_name,
            'business_email' => $business->business_email,
            'business_phone' => $business->business_phone,
            'business_website' => $business->business_website,
            'business_logo' => $business->business_logo,
            'business_industry' => $business->business_industry,
            'business_location' => $business->business_location,
            'business_code' => $business->business_code,

            'tax_rate' => $business->tax_rate,
            'kra_pin' => $business->kra_pin,

            'mpesa_shortcode' => $business->mpesa_shortcode,
            'mpesa_shortcode_type' => $business->mpesa_shortcode_type,
            'mpesa_passkey_set' => !empty($business->mpesa_passkey),
            'mpesa_consumer_key_set' => !empty($business->mpesa_consumer_key),
            'mpesa_consumer_secret_set' => !empty($business->mpesa_consumer_secret),

            'digitax_api_key_set' => !empty($business->digitax_api_key),
            'digitax_environment' => $business->digitax_environment,
            'digitax_enabled' => (bool) $business->digitax_enabled,
        ];
    }

    /**
     * Masked preview of one saved secret — e.g. "KEN_...aGjG (49 chars)".
     * Never the full value. Lets the owner confirm which key is stored
     * (catch "saved the wrong one") without it ever crossing the wire
     * intact, on page load or on demand.
     */
    public function revealSecret(User $user, string $field): ?string
    {
        if (!in_array($field, self::SECRET_FIELDS, true)) {
            return null;
        }

        $business = $user->business;
        $value = $business?->{$field};

        if (empty($value)) {
            return null;
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . '...' . substr($value, -4) . ' (' . strlen($value) . ' chars)';
    }

    public function updateBusiness(User $user, array $data): array
    {
        return $this->applyUpdate($user, $data);
    }

    public function updateTax(User $user, array $data): array
    {
        return $this->applyUpdate($user, $data);
    }

    public function updateMpesa(User $user, array $data): array
    {
        return $this->applyUpdate($user, $data);
    }

    public function updateDigitax(User $user, array $data): array
    {
        return $this->applyUpdate($user, $data);
    }

    protected function applyUpdate(User $user, array $data): array
    {
        $business = $user->business;
        $this->repo->update($business, $data);

        return $this->getSettings($user->fresh());
    }
}
