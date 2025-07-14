<?php

namespace App\Filament\Resources\RoleManagementResource\Pages;

use App\Filament\Resources\RoleManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoleManagement extends ListRecords
{
    protected static string $resource = RoleManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordAction(): ?string
    {
        return 'view';
    }
}
