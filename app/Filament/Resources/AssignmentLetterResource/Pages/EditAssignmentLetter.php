<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Models\User;
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
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Check if the current user is the approver
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user && isset($data['approver_id']) && $data['approver_id'] == $user->user_id) {
                $data['is_approver'] = true;
            } else {
                $data['is_approver'] = false;
            }
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set updated_by from authenticated user
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user) {
                $data['updated_by'] = $user->user_id;
                $data['updated_at'] = now();
            }
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        try {
            // Handle file upload to MinIO if a new file was uploaded
            $record = $this->getRecord();
            $filePath = $this->data['file_path'] ?? null;
            
            if ($filePath && $record && $filePath !== $record->getOriginal('file_path')) {
                // For public disk uploads
                $tempFile = public_path('storage/' . $filePath);
                
                if (!file_exists($tempFile)) {
                    // Fallback to check other storage paths
                    $tempFile = storage_path('app/public/' . $filePath);
                }
                
                if (file_exists($tempFile)) {
                    // Log file details for debugging
                    $fileSize = filesize($tempFile);
                    $mimeType = mime_content_type($tempFile);
                    
                    \Log::info("Processing uploaded file (edit)", [
                        'file_path' => $filePath,
                        'size' => $fileSize,
                        'mime_type' => $mimeType
                    ]);
                    
                    // Check if file is actually a PDF or JPG
                    $validMimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg'];
                    if (!in_array($mimeType, $validMimeTypes)) {
                        throw new \Exception("Invalid file type: {$mimeType}. Only PDF and JPG files are accepted.");
                    }
                    
                    // Delete the old file if it exists
                    $record->deleteFile();
                    
                    // Create an UploadedFile from the temporary file
                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $tempFile,
                        basename($filePath),
                        $mimeType,
                        null,
                        true
                    );
                    
                    // Store the file using the model's method
                    $path = $record->storeFile($uploadedFile);
                    
                    if (!$path) {
                        throw new \Exception("Failed to upload file to MinIO storage");
                    }
                    
                    // Delete the temporary file
                    @unlink($tempFile);
                    
                    // Show success notification
                    \Filament\Notifications\Notification::make()
                        ->title('File Updated')
                        ->body('File has been successfully updated.')
                        ->success()
                        ->send();
                } else {
                    throw new \Exception("Temporary file not found at {$tempFile}");
                }
            }
        } catch (\Exception $e) {
            \Log::error("File update failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show notification with specific error
            \Filament\Notifications\Notification::make()
                ->title('File Update Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
