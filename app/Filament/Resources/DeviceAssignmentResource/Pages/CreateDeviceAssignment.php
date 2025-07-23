<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use App\Traits\HasInventoryLogging;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateDeviceAssignment extends CreateRecord
{
    use HasInventoryLogging;
    
    protected static string $resource = DeviceAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $auth = session('authenticated_user');
        $data['created_at'] = now();
        $data['created_by'] = $auth['pn'] ?? 'system';
        
        // Set status if not provided
        if (empty($data['status'])) {
            $data['status'] = 'Digunakan';
        }
        
        return $data;
    }

    public function create(bool $anotherIsQueued = false): void
    {
        // Wrap the entire creation process in a transaction
        DB::transaction(function () use ($anotherIsQueued) {
            // Call the parent create method which will handle the actual creation
            parent::create($anotherIsQueued);
            
            // Log the assignment creation - if this fails, the transaction will rollback
            $this->logAssignmentModelChanges($this->record, 'created');
        });
    }
}
