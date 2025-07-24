<?php

namespace App\Filament\Resources\BriboxResource\Pages;

use App\Filament\Resources\BriboxResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBribox extends CreateRecord
{
    protected static string $resource = BriboxResource::class;

    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
