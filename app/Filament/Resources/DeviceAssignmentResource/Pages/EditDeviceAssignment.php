<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
                ->after(function () {
                    // Log the assignment deletion
                    $this->logAssignmentModelChanges($this->record, 'deleted');
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

    protected function afterSave(): void
    {
        // Log the assignment update
        $this->logAssignmentModelChanges($this->record, 'updated', $this->originalData);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
