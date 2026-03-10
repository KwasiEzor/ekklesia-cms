<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user.
     *
     * Register a new user account and obtain an initial access token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'tenant_id' => tenant('id'),
        ]);

        $token = $user->createToken($request->validated('device_name'));

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token->plainTextToken,
        ], 201);
    }

    /**
     * Login user.
     *
     * Authenticate a user by their email and password to receive an access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($request->validated('device_name'));

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Logout user.
     *
     * Revoke the current access token, logging the user out of the current device.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()->currentAccessToken();

        $token?->delete();

        return response()->json(null, 204);
    }

    /**
     * User profile.
     *
     * Retrieve the currently authenticated user's profile details.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * List access tokens.
     *
     * Retrieve a list of all active personal access tokens for the authenticated user.
     */
    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->select(
            'id',
            'name',
            'abilities',
            'last_used_at',
            'expires_at',
            'created_at',
        )->get();

        return response()->json(['data' => $tokens]);
    }

    /**
     * Revoke specific token.
     *
     * Delete a specific personal access token by its ID.
     *
     * @urlParam tokenId string The ID of the token to revoke.
     */
    public function revokeToken(Request $request, string $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if ($deleted === 0) {
            return response()->json(['message' => __('auth.token_not_found')], 404);
        }

        return response()->json(null, 204);
    }

    /**
     * Revoke all tokens.
     *
     * Log out of all devices by deleting all personal access tokens for the authenticated user.
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(null, 204);
    }
}
