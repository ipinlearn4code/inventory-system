<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use App\Services\DeviceAssignmentService;
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
        
        return $data;
    }

    public function create(bool $anotherIsQueued = false): void
    {
        try {
            $assignmentService = app(DeviceAssignmentService::class);
            $result = $assignmentService->createAssignment($this->data);
            
            // Set the record for proper redirect functionality
            $this->record = $result['assignment'];
            
            // Show success notification
            \Filament\Notifications\Notification::make()
                ->title('Assignment Created')
                ->body('Device assignment has been created successfully.')
                ->success()
                ->send();
                
            $this->getResource()::getUrl('index');
            
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Assignment Failed')
                ->body('Failed to create assignment: ' . $e->getMessage())
                ->danger()
                ->send();
                
            $this->halt();
        }
    }
}
