<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use App\Services\DeviceAssignmentService;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceAssignment extends EditRecord
{
    use HasInventoryLogging;
    
    protected static string $resource = DeviceAssignmentResource::class;
    protected array $originalData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $this->logAssignmentModelChanges($this->record, 'deleted', $this->record->toArray());
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
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
        try {
            // Only allow updating certain fields (follow PATCH endpoint constraints)
            $allowedFields = ['notes', 'assigned_date'];
            $updateData = array_intersect_key($this->data, array_flip($allowedFields));
            
            if (!empty($updateData)) {
                $assignmentService = app(\App\Services\DeviceAssignmentService::class);
                $assignmentService->updateAssignment($this->record->assignment_id, $updateData);
                
                // Log the change
                $this->logAssignmentModelChanges($this->record, 'updated', $this->originalData);
            }
            
            \Filament\Notifications\Notification::make()
                ->title('Assignment Updated')
                ->body('Device assignment has been updated successfully.')
                ->success()
                ->send();
                
            if ($shouldRedirect) {
                $this->redirect($this->getRedirectUrl());
            }
            
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Update Failed')
                ->body('Failed to update assignment: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
