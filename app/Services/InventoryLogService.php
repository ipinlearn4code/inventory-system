<?php

namespace App\Services;

use App\Contracts\InventoryLogServiceInterface;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;

class InventoryLogService implements InventoryLogServiceInterface
{
    /**
     * Cache current user PN to avoid multiple auth checks
     */
    private ?string $cachedUserPn = null;

    /**
     * Log device actions (CREATE, UPDATE, DELETE)
     */
    public function logDeviceAction(
        $device, 
        string $actionType, 
        ?array $oldValue = null, 
        ?array $newValue = null,
        ?string $userPn = null
    ): void {
        $this->logInventoryAction(
            'devices',
            $actionType,
            $oldValue,
            $newValue,
            null, // user_affected not needed for device actions
            $userPn
        );
    }

    /**
     * Log device assignment actions
     */
    public function logAssignmentAction(
        $assignment, 
        string $actionType, 
        ?array $oldValue = null, 
        ?array $newValue = null,
        ?string $userPn = null
    ): void {
        // For assignments, user_affected is the user being assigned the device
        $userAffected = null;
        if ($assignment && isset($assignment->user)) {
            $userAffected = $assignment->user->pn ?? null;
        }

        $this->logInventoryAction(
            'device_assignments',
            $actionType,
            $oldValue,
            $newValue,
            $userAffected,
            $userPn
        );
    }

    /**
     * Log general inventory actions
     */
    public function logInventoryAction(
        string $changedFields,
        string $actionType,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $userAffected = null,
        ?string $userPn = null
    ): void {
        try {
            InventoryLog::create([
                'changed_fields' => $changedFields,
                'action_type' => $actionType,
                'old_value' => $oldValue ? json_encode($oldValue) : null,
                'new_value' => $newValue ? json_encode($newValue) : null,
                'user_affected' => $userAffected,
                'created_by' => $userPn ?? $this->getCurrentUserPn(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the main operation
            \Log::error('Failed to create inventory log: ' . $e->getMessage(), [
                'changed_fields' => $changedFields,
                'action_type' => $actionType,
                'user_pn' => $userPn ?? $this->getCurrentUserPn(),
            ]);
        }
    }

    /**
     * Get current user PN for logging with caching for better performance
     */
    public function getCurrentUserPn(): string
    {
        // Return cached value if available
        if ($this->cachedUserPn !== null) {
            return $this->cachedUserPn;
        }

        // Try different authentication contexts
        if (auth()->check()) {
            // For API/web authentication
            $user = auth()->user();
            if ($user && isset($user->pn)) {
                $this->cachedUserPn = $user->pn;
                return $this->cachedUserPn;
            }
        }

        // For Filament admin authentication
        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($user && isset($user->pn)) {
                $this->cachedUserPn = $user->pn;
                return $this->cachedUserPn;
            }
        }

        // For session-based authentication (fallback)
        $sessionUser = session('authenticated_user');
        if ($sessionUser && isset($sessionUser['pn'])) {
            $this->cachedUserPn = $sessionUser['pn'];
            return $this->cachedUserPn;
        }

        // Default fallback
        $this->cachedUserPn = 'system';
        return $this->cachedUserPn;
    }
}
