<?php

namespace App\Filament\Resources\DeviceAssignmentResource\Pages;

use App\Filament\Resources\DeviceAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceAssignments extends ListRecords
{
    protected static string $resource = DeviceAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
