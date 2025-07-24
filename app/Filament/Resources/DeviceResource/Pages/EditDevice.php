<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Traits\HasInventoryLogging;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditDevice extends EditRecord
{
    use HasInventoryLogging;

    protected static string $resource = DeviceResource::class;

    // Store original data for logging
    protected array $originalData = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function getHeaderActions(): array
    // {
    //     // dd('getHeaderActions called in EditDevice');
    //     return [
    //         Actions\DeleteAction::make()
    //             ->action(function () {
    //                 // Wrap deletion in transaction
    //                 DB::transaction(function () {
    //                     // Store original data before deletion
    //                     $originalData = $this->record->toArray();

    //                     // Delete the record
    //                     $this->record->delete();

    //                     // Log the device deletion with original data - if this fails, the transaction will rollback
    //                     $this->logDeviceModelChanges($this->record, 'deleted', $originalData);
    //                 });
    //             }),
    //     ];
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Store original data for logging
        $this->originalData = $this->record->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Get the current user's PN from session or auth
        $currentUserPn = auth()->user()?->pn ?? session('authenticated_user.pn');

        $data['updated_by'] = $currentUserPn;
        $data['updated_at'] = now();

        return $data;
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        // Wrap the entire save process in a transaction
        DB::transaction(function () use ($shouldRedirect, $shouldSendSavedNotification) {
            // Call the parent save method which will handle the actual update
            parent::save($shouldRedirect, $shouldSendSavedNotification);

            // Log the device update - if this fails, the transaction will rollback
            $this->logDeviceModelChanges($this->record, 'updated', $this->originalData);
        });
    }
}
