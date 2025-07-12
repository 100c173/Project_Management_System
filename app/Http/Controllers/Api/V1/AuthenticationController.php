<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticationRequest\LoginUserRequest;
use App\Http\Requests\AuthenticationRequest\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    /**
     * Register new user
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function Register(StoreUserRequest $request):JsonResponse
    {
        // Validate with strong password rules
        $validated = $request->validate();

        // Create user with hashed password
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Generate limited-scope token
        $token = $user->createToke('auth_token')->plainTextToken;

        return response()->json([
            'message' => "Registered Successfully",
            'access_token' => $token,
            'user' => $user->only('id', 'name', 'email'),
        ], 201);
    }

    /**
     * Authenticate user
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function login(LoginUserRequest $request):JsonResponse
    {
        $credentials  = $request->validate();

        $user = User::where('email', $credentials['email'])->first();

        // Verify credentials and account status
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect'],
            ]);
        }

        // Generate new token with expiration
        $token = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(5) // Token expiration
        )->plainTextToken;

        return response()->json([
            'message' => "Login successful",
            'token'   => $token,
            'token_type' => 'Bearer',
            'expires_in' => '5 hours',
        ]);
    }

    /**
     * Revoke current access token
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request):JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user data
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request):JsonResponse
    {
        // Return only essential user information
        return response()->json([
            'user' => $request->user()->only('id', 'name', 'email')
        ]);
    }
}
