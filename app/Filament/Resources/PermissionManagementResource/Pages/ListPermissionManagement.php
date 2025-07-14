<?php

namespace App\Filament\Resources\PermissionManagementResource\Pages;

use App\Filament\Resources\PermissionManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissionManagement extends ListRecords
{
    protected static string $resource = PermissionManagementResource::class;

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
