<?php

namespace App\Services;

use App\Models\User;

class AuthenticationService
{
    /**
     * Get the current authenticated user's ID
     */
    public function getCurrentUserId(): ?int
    {
        $auth = session('authenticated_user');
        
        if (!$auth) {
            return null;
        }
        
        $user = User::where('pn', $auth['pn'])->first();
        
        return $user?->user_id;
    }

    /**
     * Get the current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        $auth = session('authenticated_user');
        
        if (!$auth) {
            return null;
        }
        
        return User::where('pn', $auth['pn'])->first();
    }

    /**
     * Check if the current user is the approver
     */
    public function isCurrentUserApprover(int $approverId): bool
    {
        $currentUserId = $this->getCurrentUserId();
        
        return $currentUserId === $approverId;
    }
}
