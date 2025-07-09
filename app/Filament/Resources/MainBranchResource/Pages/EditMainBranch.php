<?php

namespace App\Filament\Resources\MainBranchResource\Pages;

use App\Filament\Resources\MainBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMainBranch extends EditRecord
{
    protected static string $resource = MainBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
