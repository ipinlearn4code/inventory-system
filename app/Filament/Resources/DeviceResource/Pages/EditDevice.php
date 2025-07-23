<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDevice extends EditRecord
{
    use HasInventoryLogging;
    
    protected static string $resource = DeviceResource::class;

    // Store original data for logging
    protected array $originalData = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function () {
                    // Log the device deletion
                    $this->logDeviceModelChanges($this->record, 'deleted');
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
        // Get the current user's PN from session or auth
        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn');
        
        $data['updated_by'] = $currentUserPn;
        $data['updated_at'] = now();
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Log the device update
        $this->logDeviceModelChanges($this->record, 'updated', $this->originalData);
    }
}
