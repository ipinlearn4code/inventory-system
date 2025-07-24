<?php

namespace App\Filament\Resources\BriboxResource\Pages;

use App\Filament\Resources\BriboxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBribox extends EditRecord
{
    protected static string $resource = BriboxResource::class;

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
