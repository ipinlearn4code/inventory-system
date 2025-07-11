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
            // Log file details for debugging
            \Log::info('Storing file in MinIO', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]);
            
            // Validate file type
            $validMimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg'];
            if (!in_array($file->getMimeType(), $validMimeTypes)) {
                throw new \Exception("Invalid file type: {$file->getMimeType()}. Only PDF and JPG files are accepted.");
            }
            
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
                
            \Log::info('Attempting to store file', [
                'directory' => $directory,
                'filename' => $filename,
                'disk' => 'minio'
            ]);
            
            // Store the file in MinIO
            $path = $file->storeAs($directory, $filename, 'minio');
            
            if (!$path) {
                throw new \Exception('Failed to store file in MinIO storage');
            }
            
            \Log::info('File stored successfully', ['path' => $path]);
            return $path;
            
        } catch (UnableToWriteFile $e) {
            $errorMsg = 'MinIO write failure: ' . $e->getMessage();
            \Log::error($errorMsg, [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception($errorMsg, 0, $e);
            
        } catch (\Exception $e) {
            $errorMsg = 'MinIO storage error: ' . $e->getMessage();
            \Log::error($errorMsg, [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception($errorMsg, 0, $e);
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
