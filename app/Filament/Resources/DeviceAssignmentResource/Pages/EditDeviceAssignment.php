<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceAssignment extends EditRecord
{
    protected static string $resource = DeviceAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $auth = session('authenticated_user');
        $data['updated_at'] = now();
        $data['updated_by'] = $auth['pn'] ?? 'system';
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
