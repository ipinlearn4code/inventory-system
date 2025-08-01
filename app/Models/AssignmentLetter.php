<?php

namespace App\Models;

use App\Services\MinioStorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentLetter extends Model
{
    use HasFactory;
    protected $primaryKey = 'letter_id';
    public $timestamps = false; // Using custom created_at/updated_at fields

    protected $fillable = [
        'assignment_id',
        'letter_type',
        'letter_number',
        'letter_date',
        'approver_id',
        'file_path',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'letter_id';
    }

    public function assignment()
    {
        return $this->belongsTo(DeviceAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

        /**
     * Store a new file for this assignment letter
     *
     * @param UploadedFile $file The uploaded file
     * @return string|null The stored file path
     */
    public function storeFile(UploadedFile $file): ?string
    {
        $storageService = app(MinioStorageService::class);
        
        // Store file with the new simplified directory path format
        $path = $storageService->storeAssignmentLetterFile(
            $file,
            $this->assignment_id,
            $this->letter_type
        );
        
        if ($path) {
            $this->update(['file_path' => $path]);
        }
        
        return $path;
    }

    /**
     * Update the file for this assignment letter with rollback support
     *
     * @param UploadedFile $file The new uploaded file
     * @return array Result array with status, message, and details
     */
    public function updateFile(UploadedFile $file): array
    {
        $storageService = app(MinioStorageService::class);
        $tempBackupPath = null;
        $oldFilePath = $this->file_path;
        
        try {
            // Step 1: Download existing file to temp storage if it exists
            if ($oldFilePath) {
                \Log::info('Backing up existing file before update', [
                    'letter_id' => $this->letter_id,
                    'old_path' => $oldFilePath
                ]);
                
                $tempBackupPath = $storageService->downloadToTemp($oldFilePath);
                if (!$tempBackupPath) {
                    return [
                        'success' => false,
                        'message' => 'Failed to backup existing file',
                        'details' => 'Could not download current file to temporary storage'
                    ];
                }
            }
            
            // Step 2: Delete old file from MinIO if it exists
            if ($oldFilePath) {
                $deleteResult = $storageService->deleteFile($oldFilePath);
                if (!$deleteResult) {
                    // Cleanup temp file and abort
                    if ($tempBackupPath) {
                        $storageService->cleanupTempFile($tempBackupPath);
                    }
                    return [
                        'success' => false,
                        'message' => 'Failed to delete existing file from MinIO',
                        'details' => 'Old file could not be removed'
                    ];
                }
            }
            
            // Step 3: Upload new file
            $newPath = $storageService->storeAssignmentLetterFile(
                $file,
                $this->assignment_id,
                $this->letter_type
            );
            
            if (!$newPath) {
                // Rollback: restore old file if we had one
                if ($oldFilePath && $tempBackupPath) {
                    \Log::warning('New file upload failed, attempting rollback', [
                        'letter_id' => $this->letter_id
                    ]);
                    
                    $rollbackResult = $storageService->uploadFromLocal($tempBackupPath, $oldFilePath);
                    if (!$rollbackResult) {
                        \Log::error('Rollback failed - data loss risk', [
                            'letter_id' => $this->letter_id,
                            'lost_file' => $oldFilePath
                        ]);
                    }
                }
                
                // Cleanup temp file
                if ($tempBackupPath) {
                    $storageService->cleanupTempFile($tempBackupPath);
                }
                
                return [
                    'success' => false,
                    'message' => 'Failed to upload new file',
                    'details' => 'New file could not be stored in MinIO'
                ];
            }
            
            // Step 4: Update database with new path
            $this->update(['file_path' => $newPath]);
            
            // Step 5: Cleanup temp backup file
            if ($tempBackupPath) {
                $storageService->cleanupTempFile($tempBackupPath);
            }
            
            \Log::info('File updated successfully', [
                'letter_id' => $this->letter_id,
                'old_path' => $oldFilePath,
                'new_path' => $newPath
            ]);
            
            return [
                'success' => true,
                'message' => 'File updated successfully',
                'details' => [
                    'old_path' => $oldFilePath,
                    'new_path' => $newPath
                ]
            ];
            
        } catch (\Exception $e) {
            // Emergency rollback attempt
            if ($oldFilePath && $tempBackupPath) {
                \Log::error('Exception during file update, attempting emergency rollback', [
                    'letter_id' => $this->letter_id,
                    'error' => $e->getMessage()
                ]);
                
                $storageService->uploadFromLocal($tempBackupPath, $oldFilePath);
            }
            
            // Cleanup temp file
            if ($tempBackupPath) {
                $storageService->cleanupTempFile($tempBackupPath);
            }
            
            return [
                'success' => false,
                'message' => 'File update failed due to exception',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete the assignment letter file from MinIO
     *
     * @return bool Success status
     */
    public function deleteFile(): bool
    {
        if (!$this->file_path) {
            return true;
        }

        $storageService = app(MinioStorageService::class);
        $deleted = $storageService->deleteFile($this->file_path);
        
        if ($deleted) {
            $this->update(['file_path' => null]);
            \Log::info('Assignment letter file deleted', [
                'letter_id' => $this->letter_id,
                'deleted_path' => $this->file_path
            ]);
        }
        
        return $deleted;
    }

    /**
     * Handle model deletion - also remove file from MinIO
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($letter) {
            // Delete the associated file when the letter is deleted
            if ($letter->hasFile()) {
                $result = $letter->deleteFile();
                \Log::info('Assignment letter deleted, file cleanup result', [
                    'letter_id' => $letter->letter_id,
                    'file_deleted' => $result
                ]);
            }
        });
    }

    /**
     * Get the URL for the assignment letter file (proxy through Laravel)
     *
     * @return string|null URL to access the file
     */
    public function getFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        // Use Laravel proxy URL instead of direct MinIO URL for security and consistency
        return route('assignment-letter.preview', ['letter' => $this->letter_id]);
    }

    /**
     * Get direct MinIO temporary URL (for internal use only)
     *
     * @return string|null Direct MinIO URL
     */
    public function getDirectMinioUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $storageService = app(MinioStorageService::class);
        return $storageService->getTemporaryUrl($this->file_path);
    }

    /**
     * Check if the assignment letter has a file
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }
}
