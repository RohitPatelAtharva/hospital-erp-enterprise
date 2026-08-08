<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication foundation (Laravel Sanctum).
 *
 * Phase-1 token auth against the stock users store. The authoritative identity
 * and OIDC flows are delivered by the IAM module in a later phase
 * (docs/06-AUTHENTICATION.md).
 */
final class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null || ! Hash::check((string) $request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth');

        return ApiResponse::data([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'facilityId' => $user->facility_id,
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        return ApiResponse::data([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'facilityId' => $user->facility_id,
            'roles' => $user->roles ?? [],
            'permissions' => $user->permissions(),
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('sanctum')->user()?->currentAccessToken()?->delete();

        return ApiResponse::noContent();
    }
}
