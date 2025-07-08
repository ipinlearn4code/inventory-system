<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the current user's PN from session or auth
        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn');
        
        $data['created_by'] = $currentUserPn;
        $data['created_at'] = now();
        
        return $data;
    }
}
