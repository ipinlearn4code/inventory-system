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
    
    protected ?string $tempFilePath = null;

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
        
        // CRITICAL FIX: Handle file path for edit operations
        if (isset($data['file_path'])) {
            $currentRecord = $this->getRecord();
            $originalFilePath = $currentRecord ? $currentRecord->getOriginal('file_path') : null;
            
            // Check if this is a new file upload (different from original)
            if ($data['file_path'] !== $originalFilePath) {
                // This is a new file upload - store temp path and remove from database save
                $this->tempFilePath = $data['file_path'];
                unset($data['file_path']); // Don't save to database yet
            }
        }
        
        return $data;
    }    protected function afterSave(): void
    {
        try {
            // Handle file upload to MinIO if a new file was uploaded
            $record = $this->getRecord();
            $filePath = $this->tempFilePath; // Use the stored temp file path
            
            \Log::info("Edit afterSave debug", [
                'temp_file_path' => $filePath,
                'file_path_type' => gettype($filePath),
                'original_file_path' => $record->getOriginal('file_path'),
                'record_id' => $record->getKey()
            ]);
            
            // Only process if we have a temp file path (indicating new upload)
            if ($filePath && $record) {
                // This is a new file upload
                $this->handleFileUploadToMinio($record, $filePath);
            }
        } catch (\Exception $e) {
            \Log::error("File update failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't show error for cases where user just saved without uploading new file
            if (!str_contains($e->getMessage(), 'Temporary file not found')) {
                \Filament\Notifications\Notification::make()
                    ->title('File Update Error')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            } else {
                \Log::info("Skipping error notification for missing temp file (likely no new upload)");
            }
        }
    }

    /**
     * Handle file upload to MinIO with proper structured path
     */
    private function handleFileUploadToMinio($record, $filePath): void
    {
        // Find the actual uploaded file in public storage
        $tempFilePath = null;
        
        \Log::info("HandleFileUploadToMinio debug", [
            'raw_file_path' => $filePath,
            'file_path_type' => gettype($filePath),
            'is_array' => is_array($filePath),
            'record_id' => $record->getKey()
        ]);
        
        // Handle Filament's file path format - it can be a string or array
        if (is_array($filePath)) {
            // Get the first file if it's an array
            $actualPath = reset($filePath);
            \Log::info("File path is array, extracted: " . $actualPath);
        } else {
            $actualPath = $filePath;
            \Log::info("File path is string: " . $actualPath);
        }
        
        // If the path looks like it's already a structured MinIO path, skip processing
        if (!str_contains($actualPath, 'assignment-letters/') && !str_contains($actualPath, '{')) {
            \Log::info("Path already looks like structured MinIO path, skipping upload");
            return;
        }
        
        // Check multiple possible locations for the temporary file
        $possiblePaths = [
            storage_path('app/public/' . $actualPath),
            public_path('storage/' . $actualPath),
            storage_path('app/public/assignment-letters/' . basename($actualPath)),
            storage_path('app/livewire-tmp/' . basename($actualPath)), // Livewire temp directory
            storage_path('app/' . $actualPath), // Direct storage path
        ];
        
        \Log::info("Searching for temp file in paths", ['paths' => $possiblePaths]);
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $tempFilePath = $path;
                \Log::info("Found temp file at: " . $path);
                break;
            }
        }
        
        if (!$tempFilePath) {
            // Check if file is already in MinIO (maybe it's an existing file being re-saved)
            $disk = \Illuminate\Support\Facades\Storage::disk('minio');
            if ($disk->exists($actualPath)) {
                \Log::info("File already exists in MinIO, skipping upload: " . $actualPath);
                return;
            }
            
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
