<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeviceAssignment extends CreateRecord
{
    protected static string $resource = DeviceAssignmentResource::class;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
