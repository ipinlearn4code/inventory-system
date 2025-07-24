<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Models\User;
use App\Services\StorageHealthService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

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
            if ($user && isset($data['approver_id']) && $data['approver_id'] == $user->getKey()) {
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
                $data['updated_by'] = $user->getKey();
                $data['updated_at'] = now();
                
                // Handle approver logic: if is_approver toggle is true, set current user as approver
                if (isset($data['is_approver']) && $data['is_approver']) {
                    $data['approver_id'] = $user->getKey();
                }
            }
        }
        
        // Ensure approver_id is always set - if not set by toggle or form, keep the existing value
        $currentRecord = $this->getRecord();
        if (empty($data['approver_id']) && $currentRecord && $currentRecord->getAttribute('approver_id')) {
            $data['approver_id'] = $currentRecord->getAttribute('approver_id');
        }
        
        // Remove the is_approver field as it's not part of the model
        unset($data['is_approver']);
        
        return $data;
    }    protected function afterSave(): void
    {
        try {
            // Handle file upload to MinIO if a new file was uploaded
            $record = $this->getRecord();
            $filePath = $this->data['file_path'] ?? null;
            
            if ($filePath && $record && $filePath !== $record->getOriginal('file_path')) {
                $this->handleFileUploadToMinio($record, $filePath);
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

    /**
     * Handle file upload to MinIO with proper structured path
     */
    private function handleFileUploadToMinio($record, $filePath): void
    {
        // Find the actual uploaded file in public storage
        $tempFilePath = null;
        
        // Handle Filament's file path format - it can be a string or array
        if (is_array($filePath)) {
            // Get the first file if it's an array
            $actualPath = reset($filePath);
        } else {
            $actualPath = $filePath;
        }
        
        // Check multiple possible locations for the temporary file
        $possiblePaths = [
            storage_path('app/public/' . $actualPath),
            public_path('storage/' . $actualPath),
            storage_path('app/public/assignment-letters/' . basename($actualPath)),
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $tempFilePath = $path;
                break;
            }
        }
        
        if (!$tempFilePath) {
            throw new \Exception("Temporary file not found. Tried paths: " . implode(', ', $possiblePaths));
        }

        \Log::info("Processing uploaded file (edit)", [
            'file_path' => $actualPath,
            'temp_file_path' => $tempFilePath,
            'size' => filesize($tempFilePath),
            'mime_type' => mime_content_type($tempFilePath)
        ]);

        // Validate file type
        $mimeType = mime_content_type($tempFilePath);
        $validMimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg'];
        if (!in_array($mimeType, $validMimeTypes)) {
            throw new \Exception("Invalid file type: {$mimeType}. Only PDF and JPG files are accepted.");
        }

        // Delete the old file if it exists
        $record->deleteFile();

        // Create an UploadedFile from the temporary file
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFilePath,
            basename($actualPath),
            $mimeType,
            null,
            true
        );

        // Store the file using the model's method which will use proper MinIO structure
        $minioPath = $record->storeFile($uploadedFile);

        if (!$minioPath) {
            throw new \Exception("Failed to upload file to MinIO storage");
        }

        \Log::info("File updated successfully in MinIO", [
            'minio_path' => $minioPath,
            'record_id' => $record->letter_id
        ]);

        // Clean up temporary file
        @unlink($tempFilePath);
        
        // Also clean up from public storage if it exists
        if (\Storage::disk('public')->exists($actualPath)) {
            \Storage::disk('public')->delete($actualPath);
        }

        // Show success notification
        \Filament\Notifications\Notification::make()
            ->title('File Updated')
            ->body('File has been successfully updated in MinIO storage.')
            ->success()
            ->send();
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.assignment-letters-header');
    }
}
