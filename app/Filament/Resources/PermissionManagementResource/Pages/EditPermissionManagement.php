<?php

namespace App\Filament\Resources\PermissionManagementResource\Pages;

use App\Filament\Resources\PermissionManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermissionManagement extends EditRecord
{
    protected static string $resource = PermissionManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
