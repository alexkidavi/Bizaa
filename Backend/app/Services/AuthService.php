<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected AuthRepositoryInterface $repo,
        protected BankService $bankService,
        protected PerformanceRatingService $performanceRatingService,
        protected PayrollTaxBandService $payrollTaxBandService,
        protected PayrollStatutoryRateService $payrollStatutoryRateService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function register(array $data): array
    {
        $data['phone'] = '254' . substr(preg_replace('/\D/', '', $data['phone']), -9);
        $data['business_phone'] = '254' . substr(preg_replace('/\D/', '', $data['business_phone']), -9);

        DB::transaction(function () use ($data) {
            $business = $this->repo->createBusiness($data);
            $role = $this->repo->createAdminRoleWithAllPermissions($business->id);

            $user = $this->repo->createUser([
                'name'        => $data['name'],
                'role_id'     => $role->id,
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'password'    => Hash::make($data['password']),
                'business_id' => $business->id,
            ]);

            $this->repo->createDefaultBranch($business->id, $user->id);
            $this->repo->startTrialSubscription($business->id);
            $this->bankService->seedDefaults($business->id);
            $this->performanceRatingService->seedDefaults($business->id);
            $this->payrollTaxBandService->seedDefaults($business->id);
            $this->payrollStatutoryRateService->seedDefaults($business->id);
        });

        return [
            'code' => 201,
            'data' => ['message' => 'Registration successful. Your 14-day free trial has started.'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(array $data, string $ip): array
    {
        $email    = strtolower($data['email']);
        $ipKey    = 'login_attempts:ip:' . $ip;
        $emailKey = 'login_attempts:email:' . $email;

        if (Cache::get($ipKey, 0) >= 3) {
            return ['code' => 429, 'data' => ['message' => 'Too many failed attempts from this IP. Try again later.']];
        }

        if (Cache::get($emailKey, 0) >= 10) {
            return ['code' => 429, 'data' => ['message' => 'Too many failed attempts for this account. Try again later.']];
        }

        // PIN login path
        if (!empty($data['pin']) && empty($data['password'])) {
            $user = $this->repo->findUserByEmail($email);

            if (!$user || !$user->login_pin || !Hash::check($data['pin'], $user->login_pin)) {
                $this->registerFailedAttempt($ipKey, $emailKey);

                return ['code' => 401, 'data' => ['message' => 'Invalid credentials']];
            }

            if (!$user->is_active) {
                return ['code' => 403, 'data' => ['message' => 'This account has been deactivated. Contact your business admin for access.']];
            }

            try {
                $token = JWTAuth::fromUser($user);
            } catch (JWTException $e) {
                return ['code' => 500, 'data' => ['message' => 'Could not create token']];
            }

            Cache::forget($ipKey);
            Cache::forget($emailKey);
            $this->repo->updateUser($user, ['last_login_at' => now()]);

            return ['code' => 200, 'data' => $this->tokenResponse($token, $user)];
        }

        // Password login path
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $data['password']])) {
                $this->registerFailedAttempt($ipKey, $emailKey);

                return ['code' => 401, 'data' => ['message' => 'Invalid credentials']];
            }

            $user = JWTAuth::setToken($token)->toUser();
        } catch (JWTException $e) {
            return ['code' => 500, 'data' => ['message' => 'Could not create token']];
        }

        if (!$user->is_active) {
            JWTAuth::setToken($token)->invalidate();

            return ['code' => 403, 'data' => ['message' => 'This account has been deactivated. Contact your business admin for access.']];
        }

        Cache::forget($ipKey);
        Cache::forget($emailKey);
        $this->repo->updateUser($user, ['last_login_at' => now()]);

        return ['code' => 200, 'data' => $this->tokenResponse($token, $user)];
    }

    protected function registerFailedAttempt(string $ipKey, string $emailKey): void
    {
        // Cache::add() + increment() instead of get-then-put: two concurrent
        // failed logins reading the same count and each writing back
        // "count + 1" would lose an attempt under the old approach.
        foreach ([$ipKey, $emailKey] as $key) {
            Cache::add($key, 0, now()->addMinutes(5));
            Cache::increment($key);
        }
    }

    protected function tokenResponse(string $token, User $user): array
    {
        return [
            'status'       => 'success',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Session management
    |--------------------------------------------------------------------------
    */

    public function logout(): array
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return ['code' => 200, 'data' => ['message' => 'Logged out successfully']];
        } catch (JWTException $e) {
            return ['code' => 500, 'data' => ['message' => 'Could not log out']];
        }
    }

    public function refresh(): array
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return [
                'code' => 200,
                'data' => [
                    'status'       => 'success',
                    'access_token' => $newToken,
                    'token_type'   => 'Bearer',
                    'expires_in'   => 3600,
                ],
            ];
        } catch (JWTException $e) {
            return ['code' => 401, 'data' => ['message' => 'Token refresh failed']];
        }
    }

    public function verifyToken(): array
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Called on every app load/navigation, so a deactivated account
            // (e.g. a completed termination) gets cut off on its next check
            // rather than staying signed in until the token naturally expires.
            if (!$user->is_active) {
                return ['code' => 403, 'data' => ['valid' => false, 'message' => 'This account has been deactivated.']];
            }

            return [
                'code' => 200,
                'data' => [
                    'valid' => true,
                    'user'  => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ];
        } catch (JWTException $e) {
            return ['code' => 401, 'data' => ['valid' => false, 'message' => 'Invalid or missing token']];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    public function updateProfile(User $user, array $data): array
    {
        $user = $this->repo->updateUser($user, [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        return [
            'code' => 200,
            'data' => [
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Password / PIN management
    |--------------------------------------------------------------------------
    */

    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return ['code' => 422, 'data' => ['message' => 'Current password is incorrect']];
        }

        $this->repo->updateUser($user, ['password' => Hash::make($newPassword)]);

        return ['code' => 200, 'data' => ['message' => 'Password changed successfully']];
    }

    public function setPin(User $user, string $pin, string $currentPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return ['code' => 422, 'data' => ['message' => 'Current password is incorrect']];
        }

        $this->repo->updateUser($user, ['login_pin' => Hash::make($pin)]);

        return ['code' => 200, 'data' => ['message' => 'PIN set successfully']];
    }

    public function removePin(User $user, string $currentPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return ['code' => 422, 'data' => ['message' => 'Current password is incorrect']];
        }

        $this->repo->updateUser($user, ['login_pin' => null]);

        return ['code' => 200, 'data' => ['message' => 'PIN removed successfully']];
    }
}
