<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organization_id' => 'nullable|integer',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'organization_id' => $validated['organization_id'] ?? null,
        ]);

        // Resolve tenant context
        $tenant = app(TenantResolver::class)->current();

        // Create token with tenant in abilities
        $tokenAbilities = $tenant ? ["tenant:{$tenant}"] : ['*'];
        $tokenName = $tenant ? "auth-token-{$tenant}" : 'auth-token';

        $token = $user->createToken($tokenName, $tokenAbilities);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
                'tenant' => $tenant,
            ],
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Resolve tenant context
        $tenant = app(TenantResolver::class)->current();

        // Create token with tenant in abilities
        $tokenAbilities = $tenant ? ["tenant:{$tenant}"] : ['*'];
        $tokenName = $tenant ? "auth-token-{$tenant}" : 'auth-token';

        $token = $user->createToken($tokenName, $tokenAbilities);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
                'tenant' => $tenant,
                'token_abilities' => $tokenAbilities,
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Get user's active tokens
     */
    public function tokens(Request $request)
    {
        $tokens = $request->user()->tokens;

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at,
                        'created_at' => $token->created_at,
                    ];
                }),
            ],
        ]);
    }
}
