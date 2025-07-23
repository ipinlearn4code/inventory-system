<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDevice extends CreateRecord
{
    use HasInventoryLogging;
    
    protected static string $resource = DeviceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the current user's PN from session or auth
        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn');
        
        $data['created_by'] = $currentUserPn;
        $data['created_at'] = now();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Log the device creation
        $this->logDeviceModelChanges($this->record, 'created');
    }
}
