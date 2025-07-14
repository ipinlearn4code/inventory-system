<?php

namespace App\Filament\Resources\BriboxesCategoryResource\Pages;

use App\Filament\Resources\BriboxesCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBriboxesCategories extends ListRecords
{
    protected static string $resource = BriboxesCategoryResource::class;

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
