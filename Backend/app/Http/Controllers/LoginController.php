<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RemovePinRequest;
use App\Http\Requests\SetPinRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(protected AuthService $service)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated(), $request->ip());

        return response()->json($result['data'], $result['code']);
    }

    public function logout(): JsonResponse
    {
        $result = $this->service->logout();

        return response()->json($result['data'], $result['code']);
    }

    public function refresh(): JsonResponse
    {
        $result = $this->service->refresh();

        return response()->json($result['data'], $result['code']);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $result = $this->service->changePassword(
            $request->user(),
            $request->validated()['current_password'],
            $request->validated()['new_password']
        );

        return response()->json($result['data'], $result['code']);
    }

    public function setPin(SetPinRequest $request): JsonResponse
    {
        $result = $this->service->setPin(
            $request->user(),
            $request->validated()['pin'],
            $request->validated()['current_password']
        );

        return response()->json($result['data'], $result['code']);
    }

    public function removePin(RemovePinRequest $request): JsonResponse
    {
        $result = $this->service->removePin(
            $request->user(),
            $request->validated()['current_password']
        );

        return response()->json($result['data'], $result['code']);
    }

    public function verifyToken(): JsonResponse
    {
        $result = $this->service->verifyToken();

        return response()->json($result['data'], $result['code']);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->service->updateProfile($request->user(), $request->validated());

        return response()->json($result['data'], $result['code']);
    }
}
