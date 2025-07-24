<?php

namespace App\Filament\Resources\BriboxesCategoryResource\Pages;

use App\Filament\Resources\BriboxesCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBriboxesCategory extends EditRecord
{
    protected static string $resource = BriboxesCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
