<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BrokerVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected BrokerVerificationService $brokerVerificationService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
            'role' => ['required', 'in:client,owner'],
            'broker_license_number' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'broker_license_number' => $validated['broker_license_number'] ?? null,
            'broker_verification_status' => $this->brokerVerificationService->verify(
                $validated['broker_license_number'] ?? null
            ),
        ]);

        $user->assignRole($validated['role']);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 201);
    }

   public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $credentials['email'])->first();

    // Always run one bcrypt check, even for unknown e-mails, so response time does not
    // reveal whether an account exists.
    $passwordMatches = Hash::check(
        $credentials['password'],
        $user?->password ?? '$2y$12$MqV9LpTOrK4WTjpsjPySb.8R3s5DgWTMtCL5G/7spgXrMcQHBvDpq'
    );

    if (! $user || ! $passwordMatches) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    if (! $user->is_active) {
        throw ValidationException::withMessages([
            'email' => ['Your account has been deactivated. Please contact support.'],
        ]);
    }

    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'user' => $user->load('roles'),
        'token' => $token,
    ]);
}
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
