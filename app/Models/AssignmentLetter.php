<?php

namespace App\Models;

use App\Services\MinioStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentLetter extends Model
{
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
     * Store an assignment letter file to MinIO
     *
     * @param UploadedFile $file The uploaded file
     * @return string|null The stored file path
     */
    public function storeFile(UploadedFile $file): ?string
    {
        $storageService = app(MinioStorageService::class);
        
        // Store file with the structured directory path
        $path = $storageService->storeAssignmentLetterFile(
            $file,
            $this->letter_type,
            $this->assignment_id,
            $this->letter_date instanceof \DateTimeInterface ? $this->letter_date->format('Y-m-d') : (string) $this->letter_date,
            $this->letter_number
        );
        
        if ($path) {
            $this->update(['file_path' => $path]);
        }
        
        return $path;
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
        }
        
        return $deleted;
    }

    /**
     * Get the URL for the assignment letter file
     *
     * @return string|null URL to access the file
     */
    public function getFileUrl(): ?string
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
