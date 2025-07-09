<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentLetter extends EditRecord
{
    protected static string $resource = AssignmentLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
