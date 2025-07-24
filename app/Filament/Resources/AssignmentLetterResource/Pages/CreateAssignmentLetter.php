<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Models\User;
use App\Services\StorageHealthService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreateAssignmentLetter extends CreateRecord
{
    protected static string $resource = AssignmentLetterResource::class;
    
    protected ?string $tempFilePath = null;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by from authenticated user
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user) {
                $data['created_by'] = $user->getKey();
                
                // Handle approver logic: if is_approver toggle is true, set current user as approver
                if (isset($data['is_approver']) && $data['is_approver']) {
                    $data['approver_id'] = $user->getKey();
                }
            }
        }
        
        // Ensure approver_id is always set - if not set by toggle or form, use created_by user
        if (empty($data['approver_id']) && !empty($data['created_by'])) {
            $data['approver_id'] = $data['created_by'];
        }
        
        // Remove the is_approver field as it's not part of the model
        unset($data['is_approver']);
        
        // Make sure created_at is set
        if (empty($data['created_at'])) {
            $data['created_at'] = now();
        }
        
        // CRITICAL FIX: Don't save file_path to database yet - let our custom logic handle it
        if (isset($data['file_path'])) {
            // Store the temp file path for later processing but don't save to database
            $this->tempFilePath = $data['file_path'];
            unset($data['file_path']); // Remove from data to be saved to database
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        try {
            $record = $this->getRecord();
            $filePath = $this->tempFilePath; // Use the stored temp file path
            
            \Log::info("Create afterCreate debug", [
                'temp_file_path' => $filePath,
                'file_path_type' => gettype($filePath),
                'record_id' => $record->getKey()
            ]);
            
            if ($filePath && $record) {
                // Handle file upload to MinIO with proper structured path
                $this->handleFileUploadToMinio($record, $filePath);
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

    /**
     * Handle file upload to MinIO with proper structured path
     */
    private function handleFileUploadToMinio($record, $filePath): void
    {
        // Find the actual uploaded file in public storage
        $tempFilePath = null;
        
        \Log::info("HandleFileUploadToMinio debug (create)", [
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
            throw new \Exception("Temporary file not found. Tried paths: " . implode(', ', $possiblePaths));
        }

        \Log::info("Processing uploaded file", [
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

        \Log::info("File uploaded successfully to MinIO", [
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
            ->title('File Uploaded')
            ->body('File has been uploaded successfully to MinIO storage.')
            ->success()
            ->send();
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.assignment-letters-header');
    }
}
