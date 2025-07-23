<?php

namespace App\Traits;

use App\Contracts\InventoryLogServiceInterface;

trait HasInventoryLogging
{
    protected function getInventoryLogService(): InventoryLogServiceInterface
    {
        return app(InventoryLogServiceInterface::class);
    }

    /**
     * Log device model changes
     */
    protected function logDeviceModelChanges($record, string $action, ?array $oldData = null): void
    {
        $logService = $this->getInventoryLogService();
        
        switch ($action) {
            case 'created':
                $logService->logDeviceAction($record, 'CREATE', null, $record->toArray());
                break;
            case 'updated':
                $logService->logDeviceAction($record, 'UPDATE', $oldData, $record->toArray());
                break;
            case 'deleted':
                $logService->logDeviceAction($record, 'DELETE', $record->toArray(), null);
                break;
        }
    }

    /**
     * Log assignment model changes
     */
    protected function logAssignmentModelChanges($record, string $action, ?array $oldData = null): void
    {
        $logService = $this->getInventoryLogService();
        
        switch ($action) {
            case 'created':
                $logService->logAssignmentAction($record, 'CREATE', null, $record->toArray());
                break;
            case 'updated':
                $logService->logAssignmentAction($record, 'UPDATE', $oldData, $record->toArray());
                break;
            case 'deleted':
                $logService->logAssignmentAction($record, 'DELETE', $record->toArray(), null);
                break;
        }
    }

    /**
     * Log general model changes
     */
    protected function logGeneralModelChanges(string $modelType, $record, string $action, ?array $oldData = null): void
    {
        $logService = $this->getInventoryLogService();
        
        switch ($action) {
            case 'created':
                $logService->logInventoryAction($modelType, 'CREATE', null, $record->toArray());
                break;
            case 'updated':
                $logService->logInventoryAction($modelType, 'UPDATE', $oldData, $record->toArray());
                break;
            case 'deleted':
                $logService->logInventoryAction($modelType, 'DELETE', $record->toArray(), null);
                break;
        }
    }
}
