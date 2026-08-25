<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessRequest;
use App\Http\Requests\UpdateDigitaxSettingsRequest;
use App\Http\Requests\UpdateMpesaSettingsRequest;
use App\Http\Requests\UpdateTaxSettingsRequest;
use App\Services\BusinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function __construct(protected BusinessService $service)
    {
    }

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->service->getSettings(Auth::user())]);
    }

    /**
     * Fetch-on-reveal: returns a masked preview of one saved secret, never
     * the full value. Called only when the owner clicks "Show" on a field.
     */
    public function revealSecret(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', Rule::in(BusinessService::SECRET_FIELDS)],
        ]);

        $masked = $this->service->revealSecret(Auth::user(), $validated['field']);

        return response()->json(['field' => $validated['field'], 'masked' => $masked]);
    }

    public function update(UpdateBusinessRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('business_logo')) {
            $data['business_logo'] = $request->file('business_logo')->store('logos', 'public');
        } else {
            unset($data['business_logo']);
        }

        $settings = $this->service->updateBusiness(Auth::user(), $data);

        return response()->json(['message' => 'Settings saved successfully.', 'data' => $settings]);
    }

    public function updateTax(UpdateTaxSettingsRequest $request): JsonResponse
    {
        $settings = $this->service->updateTax(Auth::user(), $request->validated());

        return response()->json(['message' => 'Tax settings saved successfully.', 'data' => $settings]);
    }

    public function updateMpesa(UpdateMpesaSettingsRequest $request): JsonResponse
    {
        $settings = $this->service->updateMpesa(Auth::user(), $request->validated());

        return response()->json(['message' => 'M-Pesa settings saved successfully.', 'data' => $settings]);
    }

    public function updateDigitax(UpdateDigitaxSettingsRequest $request): JsonResponse
    {
        $settings = $this->service->updateDigitax(Auth::user(), $request->validated());

        return response()->json(['message' => 'DigiTax settings saved successfully.', 'data' => $settings]);
    }
}
