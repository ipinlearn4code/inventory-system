<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use App\Models\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(LoginRequest $request)
    {
        // Find user by PN
        $user = User::where('pn', $request->pn)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errorCode' => 'ERR_INVALID_CREDENTIALS'
            ], 401);
        }

        // Check auth record
        $auth = Auth::where('pn', $request->pn)->first();
        
        if (!$auth || !Hash::check($request->password, $auth->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errorCode' => 'ERR_INVALID_CREDENTIALS'
            ], 401);
        }

        // Create token
        $token = $user->createToken($request->device_name);

        // Determine role for mobile (superadmin becomes admin)
        $role = $auth->role === 'superadmin' ? 'admin' : $auth->role;

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => [
                    'userId' => $user->user_id,
                    'name' => $user->name,
                    'pn' => $user->pn,
                    'role' => $role
                ]
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string'
        ]);

        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken($request->device_name);

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'expiresIn' => 86400 // 24 hours
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.',
            'errorCode' => null
        ]);
    }

    /**
     * Register for push notifications
     */
    public function registerPush(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'platform' => 'required|string|in:ios,android'
        ]);

        // In a real implementation, you would store the push token
        // For now, we'll just acknowledge the registration
        
        return response()->json([
            'message' => 'Push notification registration successful.',
            'errorCode' => null
        ]);
    }
}
