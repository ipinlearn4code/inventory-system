<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use League\Flysystem\UnableToWriteFile;

class MinioStorageService
{
    /**
     * Store a file in MinIO following the structured directory path
     *
     * @param UploadedFile $file
     * @param string $letterType
     * @param int $assignmentId
     * @param string $letterDate
     * @param string $letterNumber
     * @return string|null The path to the stored file or null if the operation failed
     */
    public function storeAssignmentLetterFile(
        UploadedFile $file,
        string $letterType,
        int $assignmentId,
        string $letterDate,
        string $letterNumber
    ): ?string {
        try {
            // Format the letter date if it's a Carbon instance or a date string
            if ($letterDate instanceof Carbon) {
                $formattedDate = $letterDate->format('Y-m-d');
            } else {
                $formattedDate = Carbon::parse($letterDate)->format('Y-m-d');
            }
            
            // Clean letter number to be safe for directory/file names
            $safeLetterNumber = Str::slug($letterNumber);
            
            // Build the directory path following the structure:
            // assignment-letter/{letter-type}/{assignment-id}/{letter-date}/{letter-number}
            $directory = "{$letterType}/{$assignmentId}/{$formattedDate}/{$safeLetterNumber}";
            
            // Get original filename and sanitize it
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . 
                '.' . $file->getClientOriginalExtension();
                
            // Store the file in MinIO
            $path = $file->storeAs($directory, $filename, 'minio');
            
            if (!$path) {
                \Log::error('Failed to store file in MinIO', [
                    'file' => $file->getClientOriginalName(),
                    'directory' => $directory,
                    'disk' => 'minio'
                ]);
                return null;
            }
            
            return $path;
        } catch (UnableToWriteFile $e) {
            \Log::error('MinIO write failure', [
                'exception' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            return null;
        } catch (\Exception $e) {
            \Log::error('MinIO storage error', [
                'exception' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            return null;
        }
    }
    
    /**
     * Get a temporary URL to access a file in MinIO
     *
     * @param string $path
     * @param int $expirationMinutes
     * @return string|null
     */
    public function getTemporaryUrl(string $path, int $expirationMinutes = 60): ?string
    {
        try {
            if (Storage::disk('minio')->exists($path)) {
                return Storage::disk('minio')->temporaryUrl(
                    $path,
                    now()->addMinutes($expirationMinutes)
                );
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('MinIO temporary URL generation error', [
                'exception' => $e->getMessage(),
                'path' => $path
            ]);
            return null;
        }
    }
    
    /**
     * Delete a file from MinIO
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('minio')->delete($path);
        } catch (\Exception $e) {
            \Log::error('MinIO file deletion error', [
                'exception' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }
}
