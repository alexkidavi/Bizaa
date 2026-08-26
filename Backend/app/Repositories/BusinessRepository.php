<?php

namespace App\Repositories;

use App\Models\Business;
use Illuminate\Support\Facades\Auth;

class BusinessRepository
{
    /**
     * Scoped to the caller's own business as defense-in-depth. Nothing in
     * the app currently calls this with a client-supplied ID (BusinessService
     * always resolves via $user->business), but it shouldn't be a landmine
     * for future callers.
     */
    public function find(int $id): Business
    {
        return Business::where('id', Auth::user()->business_id)->findOrFail($id);
    }

    /**
     * Update a business record. Any of the "secret" fields (tax_api_key,
     * mpesa_passkey, mpesa_consumer_key, mpesa_consumer_secret) are only
     * overwritten when a non-empty value is present in $data — an absent
     * or blank secret means "leave the stored value untouched", so the
     * frontend never has to resend (or receive back) a saved credential.
     */
    public function update(Business $business, array $data): Business
    {
        $secretFields = ['tax_api_key', 'mpesa_passkey', 'mpesa_consumer_key', 'mpesa_consumer_secret', 'digitax_api_key'];

        foreach ($secretFields as $field) {
            // The global ConvertEmptyStringsToNull middleware turns a blank
            // submitted value into null before it gets here, so both '' and
            // null must be treated as "leave this secret untouched".
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                unset($data[$field]);
            }
        }

        $business->update($data);

        return $business;
    }
}
