<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditDeviceAssignment extends EditRecord
{
    use HasInventoryLogging;
    
    protected static string $resource = DeviceAssignmentResource::class;

    // Store original data for logging
    protected array $originalData = [];

    protected function getHeaderActions(): array
    {
        
        return [
            Actions\DeleteAction::make()
                ->action(function () {
                    // Wrap deletion in transaction
                    DB::transaction(function () {
                        // Store original data before deletion
                        $originalData = $this->record->toArray();
                        
                        // Delete the record
                        $this->record->delete();
                        
                        // Log the assignment deletion with original data - if this fails, the transaction will rollback
                        $this->logAssignmentModelChanges($this->record, 'deleted', $originalData);
                    });
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Store original data for logging
        $this->originalData = $this->record->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $auth = session('authenticated_user');
        $data['updated_at'] = now();
        $data['updated_by'] = $auth['pn'] ?? 'system';
        
        return $data;
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        // Wrap the entire save process in a transaction
        DB::transaction(function () use ($shouldRedirect, $shouldSendSavedNotification) {
            // Call the parent save method which will handle the actual update
            parent::save($shouldRedirect, $shouldSendSavedNotification);
            
            // Log the assignment update - if this fails, the transaction will rollback
            $this->logAssignmentModelChanges($this->record, 'updated', $this->originalData);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
