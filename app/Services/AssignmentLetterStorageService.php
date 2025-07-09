<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignmentLetterStorageService
{
    protected string $disk;
    protected array $config;

    public function __construct()
    {
        $this->config = config('filestorage.assignment_letters');
        $this->disk = $this->config['driver'];
        
        // Configure the storage disk dynamically
        $this->configureDisk();
    }

    /**
     * Store an assignment letter file
     */
    public function store(UploadedFile $file, string $letterNumber): string
    {
        $filename = $this->generateFilename($file, $letterNumber);
        $path = $file->storeAs('assignment-letters', $filename, $this->disk);
        
        return $path;
    }

    /**
     * Delete an assignment letter file
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the URL for an assignment letter file
     */
    public function url(string $path): string
    {
        if ($this->disk === 'local') {
            return Storage::disk($this->disk)->url($path);
        } elseif ($this->disk === 'minio') {
            return Storage::disk($this->disk)->url($path);
        }
        
        return '';
    }

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Generate a unique filename for the assignment letter
     */
    protected function generateFilename(UploadedFile $file, string $letterNumber): string
    {
        $extension = $file->getClientOriginalExtension();
        $sanitizedLetterNumber = Str::slug($letterNumber);
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "{$sanitizedLetterNumber}_{$timestamp}.{$extension}";
    }

    /**
     * Configure the storage disk based on the driver
     */
    protected function configureDisk(): void
    {
        $driverConfig = $this->config['drivers'][$this->disk];
        
        if ($this->disk === 'minio') {
            // Configure MinIO as S3-compatible storage
            config([
                "filesystems.disks.{$this->disk}" => [
                    'driver' => 's3',
                    'key' => $driverConfig['key'],
                    'secret' => $driverConfig['secret'],
                    'region' => $driverConfig['region'],
                    'bucket' => $driverConfig['bucket'],
                    'endpoint' => $driverConfig['endpoint'],
                    'use_path_style_endpoint' => $driverConfig['use_path_style_endpoint'],
                    'throw' => $driverConfig['throw'],
                ]
            ]);
        } elseif ($this->disk === 'local') {
            // Configure local storage
            config([
                "filesystems.disks.{$this->disk}" => [
                    'driver' => 'local',
                    'root' => $driverConfig['root'],
                    'url' => $driverConfig['url'],
                    'visibility' => $driverConfig['visibility'],
                ]
            ]);
        }
    }

    /**
     * Get storage configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get current storage driver
     */
    public function getDriver(): string
    {
        return $this->disk;
    }
}
