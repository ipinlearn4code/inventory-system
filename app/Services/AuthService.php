<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Models\User;
use App\Models\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     * Authenticate user with credentials
     */
    public function authenticate(string $pn, string $password): ?User
    {
        // Find user by PN
        $user = User::where('pn', $pn)->first();
        
        if (!$user) {
            return null;
        }

        // Check auth record
        $auth = Auth::where('pn', $pn)->first();
        
        if (!$auth || !Hash::check($password, $auth->password)) {
            return null;
        }

        // Attach auth data to user for role checking
        $user->setRelation('auth', $auth);
        
        return $user;
    }

    /**
     * Create authentication token for user
     */
    public function createToken(User $user, string $deviceName): array
    {
        $token = $user->createToken($deviceName);
        
        return [
            'token' => $token->plainTextToken,
            'expiresIn' => 86400 // 24 hours
        ];
    }

    /**
     * Refresh authentication token
     */
    public function refreshToken(User $user, string $deviceName): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
        
        // Create new token
        return $this->createToken($user, $deviceName);
    }

    /**
     * Revoke user's current token
     */
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Register device for push notifications
     */
    public function registerPushNotification(User $user, array $data): bool
    {
        // In a real implementation, you would store the push token
        // For now, we'll just acknowledge the registration
        return true;
    }

    /**
     * Transform user role for mobile API
     */
    public function transformRoleForMobile(string $role): string
    {
        // Convert superadmin to admin for mobile API access
        return $role === 'superadmin' ? 'admin' : $role;
    }
}
