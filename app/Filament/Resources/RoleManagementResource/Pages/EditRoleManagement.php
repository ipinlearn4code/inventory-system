<?php

namespace App\Filament\Resources\RoleManagementResource\Pages;

use App\Filament\Resources\RoleManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoleManagement extends EditRecord
{
    protected static string $resource = RoleManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
