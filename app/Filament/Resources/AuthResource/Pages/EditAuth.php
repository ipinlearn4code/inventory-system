<?php

namespace App\Filament\Resources\AuthResource\Pages;

use App\Filament\Resources\AuthResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuth extends EditRecord
{
    protected static string $resource = AuthResource::class;

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only hash password if it's being changed
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }
}
