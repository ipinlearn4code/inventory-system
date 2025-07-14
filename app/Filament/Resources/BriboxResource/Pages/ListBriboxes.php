<?php

namespace App\Filament\Resources\BriboxResource\Pages;

use App\Filament\Resources\BriboxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBriboxes extends ListRecords
{
    protected static string $resource = BriboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function isTableLazy(): bool
    {
        return true;
    }
}
