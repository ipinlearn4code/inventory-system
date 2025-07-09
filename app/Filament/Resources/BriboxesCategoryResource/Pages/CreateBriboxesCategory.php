<?php

namespace App\Filament\Resources\BriboxesCategoryResource\Pages;

use App\Filament\Resources\BriboxesCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBriboxesCategory extends CreateRecord
{
    protected static string $resource = BriboxesCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
