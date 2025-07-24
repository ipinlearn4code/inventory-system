<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use League\Flysystem\UnableToWriteFile;
use Exception;

class MinioStorageService
{
    /**
     * Store a file in MinIO following the new simplified directory path
     *
     * @param UploadedFile $file
     * @param int $assignmentId
     * @param string $letterType
     * @return string|null The path to the stored file or null if the operation failed
     */
    public function storeAssignmentLetterFile(
        UploadedFile $file,
        int $assignmentId,
        string $letterType
    ): ?string {
        try {
            // Log file details for debugging
            \Log::info('Storing file in MinIO with new format', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'assignment_id' => $assignmentId,
                'letter_type' => $letterType
            ]);

            // Validate file type
            $validMimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg'];
            if (!in_array($file->getMimeType(), $validMimeTypes)) {
                throw new \Exception("Invalid file type: {$file->getMimeType()}. Only PDF and JPG files are accepted.");
            }

            // Build the directory path following the new structure:
            // {assignment_id}/{letter_type}/
            $directory = "{$assignmentId}/{$letterType}";

            // Get original filename and sanitize it (preserve extension)
            $originalName = $file->getClientOriginalName();
            dd($originalName);
            $pathInfo = pathinfo($originalName);
            $nameWithoutExt = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? '';

            // Slugify the filename but preserve readability
            $sluggedName = $this->slugifyFilename($nameWithoutExt);
            $filename = $sluggedName . ($extension ? '.' . $extension : '');

            // Handle file name collision
            $finalFilename = $this->handleFileCollision($directory, $filename);
            $fullPath = $directory . '/' . $finalFilename;

            \Log::info('Attempting to store file with new format', [
                'directory' => $directory,
                'original_filename' => $originalName,
                'slugged_filename' => $filename,
                'final_filename' => $finalFilename,
                'full_path' => $fullPath
            ]);

            // Store the file in MinIO
            $path = $file->storeAs($directory, $finalFilename, 'minio');

            if (!$path) {
                throw new \Exception('Failed to store file in MinIO storage');
            }

            \Log::info('File stored successfully with new format', ['path' => $path]);
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
     * Slugify filename while preserving readability
     *
     * @param string $filename
     * @return string
     */
    private function slugifyFilename(string $filename): string
    {
        // Replace spaces and special characters with underscores, but keep it readable
        $slugged = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);
        $slugged = preg_replace('/_+/', '_', $slugged); // Replace multiple underscores with single
        $slugged = trim($slugged, '_'); // Remove leading/trailing underscores

        return $slugged ?: 'file'; // Fallback if filename becomes empty
    }

    /**
     * Handle file name collision by appending suffix
     *
     * @param string $directory
     * @param string $filename
     * @return string
     */
    private function handleFileCollision(string $directory, string $filename): string
    {
        $disk = Storage::disk('minio');
        $pathInfo = pathinfo($filename);
        $nameWithoutExt = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        $originalPath = $directory . '/' . $filename;

        // If file doesn't exist, return original filename
        if (!$disk->exists($originalPath)) {
            return $filename;
        }

        // File exists, find available suffix
        $counter = 1;
        do {
            $newFilename = $nameWithoutExt . '-' . $counter . $extension;
            $newPath = $directory . '/' . $newFilename;
            $counter++;
        } while ($disk->exists($newPath) && $counter <= 100); // Limit to prevent infinite loop

        if ($counter > 100) {
            // Use timestamp as last resort
            $newFilename = $nameWithoutExt . '-' . time() . $extension;
        }

        \Log::info('File collision handled', [
            'original' => $filename,
            'new' => $newFilename,
            'attempts' => $counter - 1
        ]);

        return $newFilename;
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
     * Download a file from MinIO to local temporary storage
     *
     * @param string $minioPath
     * @return string|null Path to the temporary local file
     */
    public function downloadToTemp(string $minioPath): ?string
    {
        try {
            $disk = Storage::disk('minio');

            if (!$disk->exists($minioPath)) {
                \Log::warning('File not found in MinIO for download', ['path' => $minioPath]);
                return null;
            }

            // Create a temporary file path
            $tempFileName = 'temp_backup_' . time() . '_' . basename($minioPath);
            $tempPath = storage_path('app/temp/' . $tempFileName);

            // Ensure temp directory exists
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            // Download the file content
            $content = $disk->get($minioPath);

            if (file_put_contents($tempPath, $content) === false) {
                throw new \Exception('Failed to write temporary file');
            }

            \Log::info('File downloaded to temp storage', [
                'minio_path' => $minioPath,
                'temp_path' => $tempPath
            ]);

            return $tempPath;

        } catch (\Exception $e) {
            \Log::error('Failed to download file to temp', [
                'minio_path' => $minioPath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Upload a file from local path to MinIO
     *
     * @param string $localPath
     * @param string $minioPath
     * @return bool
     */
    public function uploadFromLocal(string $localPath, string $minioPath): bool
    {
        try {
            if (!file_exists($localPath)) {
                throw new \Exception('Local file not found: ' . $localPath);
            }

            $content = file_get_contents($localPath);
            $result = Storage::disk('minio')->put($minioPath, $content);

            if ($result) {
                \Log::info('File uploaded from local to MinIO', [
                    'local_path' => $localPath,
                    'minio_path' => $minioPath
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('Failed to upload from local to MinIO', [
                'local_path' => $localPath,
                'minio_path' => $minioPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clean up temporary file
     *
     * @param string $tempPath
     * @return bool
     */
    public function cleanupTempFile(string $tempPath): bool
    {
        try {
            if (file_exists($tempPath)) {
                $result = unlink($tempPath);
                if ($result) {
                    \Log::info('Temporary file cleaned up', ['path' => $tempPath]);
                }
                return $result;
            }
            return true; // File doesn't exist, consider it cleaned
        } catch (\Exception $e) {
            \Log::error('Failed to cleanup temporary file', [
                'path' => $tempPath,
                'error' => $e->getMessage()
            ]);
            return false;
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
            $result = Storage::disk('minio')->delete($path);
            if ($result) {
                \Log::info('File deleted from MinIO', ['path' => $path]);
            }
            return $result;
        } catch (\Exception $e) {
            \Log::error('MinIO file deletion error', [
                'exception' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }

    /**
     * Check if MinIO storage is available and healthy
     *
     * @return array
     */
    public function isHealthy(): array
    {
        try {
            // Test basic connection
            $files = Storage::disk('minio')->files();

            // Test write operation
            $testContent = 'MinIO health check - ' . now();
            $testPath = 'health-check/' . time() . '.txt';

            $writeResult = Storage::disk('minio')->put($testPath, $testContent);

            if ($writeResult) {
                // Test read operation
                $readContent = Storage::disk('minio')->get($testPath);

                // Clean up
                Storage::disk('minio')->delete($testPath);

                if ($readContent === $testContent) {
                    return [
                        'status' => 'healthy',
                        'message' => 'MinIO storage is working properly',
                        'timestamp' => now(),
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => 'MinIO storage read/write test failed',
                'timestamp' => now(),
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'MinIO storage connection failed: ' . $e->getMessage(),
                'timestamp' => now(),
            ];
        }
    }

    /**
     * Check if MinIO is available before operations
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $health = $this->isHealthy();
        return $health['status'] === 'healthy';
    }
}
