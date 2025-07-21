<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Services\StorageHealthService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAssignmentLetters extends ListRecords
{
    protected static string $resource = AssignmentLetterResource::class;

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
