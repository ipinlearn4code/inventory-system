<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

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

    public function create(bool $anotherIsQueued = false): void
    {
        // Wrap the entire creation process in a transaction
        DB::transaction(function () use ($anotherIsQueued) {
            // Call the parent create method which will handle the actual creation
            parent::create($anotherIsQueued);
            
            // Log the device creation - if this fails, the transaction will rollback
            $this->logDeviceModelChanges($this->record, 'created');
        });
    }
}
