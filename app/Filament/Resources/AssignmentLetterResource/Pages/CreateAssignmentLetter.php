<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignmentLetter extends CreateRecord
{
    protected static string $resource = AssignmentLetterResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by from authenticated user
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user) {
                $data['created_by'] = $user->user_id;
            }
        }
        
        // Make sure created_at is set
        if (empty($data['created_at'])) {
            $data['created_at'] = now();
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        try {
            // For now, just test basic file upload without MinIO
            $record = $this->getRecord();
            $filePath = $this->data['file_path'] ?? null;
            
            if ($filePath && $record) {
                \Log::info("File uploaded successfully to local storage", [
                    'file_path' => $filePath,
                    'record_id' => $record->letter_id
                ]);
                
                // For now, just save the local path to database
                $record->update(['file_path' => $filePath]);
                
                // Show success notification
                \Filament\Notifications\Notification::make()
                    ->title('File Uploaded')
                    ->body('File has been uploaded successfully to local storage.')
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            \Log::error("File upload failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show notification with specific error
            \Filament\Notifications\Notification::make()
                ->title('File Upload Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
