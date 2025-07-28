<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    /**
     * Authenticate user with credentials
     */
    public function authenticate(string $pn, string $password): ?User;

    /**
     * Create authentication token for user
     */
    public function createToken(User $user, string $deviceName): array;

    /**
     * Refresh authentication token
     */
    public function refreshToken(User $user, string $deviceName): array;

    /**
     * Revoke user's current token
     */
    public function revokeCurrentToken(User $user): void;

    /**
     * Register device for push notifications
     */
    public function registerPushNotification(User $user, array $data): bool;

    /**
     * Transform user role for mobile API
     */
    public function transformRoleForMobile(string $role): string;
}
