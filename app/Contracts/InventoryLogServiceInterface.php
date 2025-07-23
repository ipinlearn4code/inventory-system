<?php

namespace App\Contracts;

interface InventoryLogServiceInterface
{
    /**
     * Log device actions (CREATE, UPDATE, DELETE)
     */
    public function logDeviceAction(
        $device, 
        string $actionType, 
        ?array $oldValue = null, 
        ?array $newValue = null,
        ?string $userPn = null
    ): void;

    /**
     * Log device assignment actions
     */
    public function logAssignmentAction(
        $assignment, 
        string $actionType, 
        ?array $oldValue = null, 
        ?array $newValue = null,
        ?string $userPn = null
    ): void;

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
    ): void;

    /**
     * Get current user PN for logging
     */
    public function getCurrentUserPn(): string;
}
