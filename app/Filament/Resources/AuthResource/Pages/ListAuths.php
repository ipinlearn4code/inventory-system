<?php

namespace App\Filament\Resources\AuthResource\Pages;

use App\Filament\Resources\AuthResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuths extends ListRecords
{
    protected static string $resource = AuthResource::class;

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
