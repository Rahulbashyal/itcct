<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Mobile Login - Returns Sanctum Token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // Verify email check for mobile too?
            if (!$user->hasVerifiedEmail()) {
                 return response()->json(['message' => 'Email not verified.'], 403);
            }

            // Create token
            $token = $user->createToken($credentials['device_name'])->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'role' => $user->role,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Mobile Logout - Revoke Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get User Info
     */
    public function user(Request $request)
    {
        return $request->user();
    }
}
