<?php

namespace App\Support\Auth\Services;

use App\Modules\Users\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticationService
{
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Keep unknown accounts on the password-hashing path as well.
            Hash::make($password);
        }

        if (! $user || ! Hash::check($password, $user->password) || $user->status !== 'active') {
            Log::notice('auth.login_failed');
            throw new AuthenticationException;
        }

        $context = app(TenantContext::class);

        try {
            $context->resolveFor($user);

            $result = DB::transaction(function () use ($user, $password): array {
                if (Hash::needsRehash($user->password)) {
                    $user->password = $password;
                }

                $user->last_login_at = now();
                $user->save();
                $expiresAt = now()->startOfSecond()->addMinutes(config('sanctum.expiration'));
                $token = $user->createToken('api', ['*'], $expiresAt);

                return [
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $expiresAt->toISOString(),
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                    ],
                ];
            });

            Log::info('auth.login_succeeded', ['user_id' => $user->id, 'tenant_id' => $context->tenant()->id]);

            return $result;
        } catch (AccessDeniedHttpException $exception) {
            Log::notice('auth.login_denied', ['user_id' => $user->id]);
            throw $exception;
        } finally {
            $context->clear();
        }
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            throw new AuthenticationException;
        }

        $token->delete();
        Log::info('auth.logout', ['user_id' => $user->id]);
    }
}
