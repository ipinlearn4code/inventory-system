<?php

namespace App\Filament\Resources\AuthResource\Pages;

use App\Filament\Resources\AuthResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAuth extends CreateRecord
{
    protected static string $resource = AuthResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The password hashing is handled by the Auth model's setPasswordAttribute
        return $data;
    }
}
