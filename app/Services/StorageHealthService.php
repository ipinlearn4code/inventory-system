<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class StorageHealthService
{
    private const CACHE_KEY_PREFIX = 'storage_health_';
    private const CACHE_DURATION = 300; // 5 minutes

    /**
     * Check MinIO storage connection health
     *
     * @return array
     */
    public function checkMinioHealth(): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . 'minio';
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            return $this->performMinioHealthCheck();
        });
    }

    /**
     * Check all storage connections health
     *
     * @return array
     */
    public function checkAllStorageHealth(): array
    {
        return [
            'minio' => $this->checkMinioHealth(),
            'public' => $this->checkPublicStorageHealth(),
            'overall' => $this->getOverallStorageStatus(),
        ];
    }

    /**
     * Get storage status for dashboard widget
     *
     * @return array
     */
    public function getStorageStatusSummary(): array
    {
        $allHealth = $this->checkAllStorageHealth();
        
        return [
            'status' => $allHealth['overall']['status'],
            'message' => $allHealth['overall']['message'],
            'details' => $allHealth,
            'last_checked' => now(),
        ];
    }

    /**
     * Force refresh storage health check (bypass cache)
     *
     * @return array
     */
    public function refreshStorageHealth(): array
    {
        // Clear cache
        Cache::forget(self::CACHE_KEY_PREFIX . 'minio');
        Cache::forget(self::CACHE_KEY_PREFIX . 'public');
        
        return $this->checkAllStorageHealth();
    }

    /**
     * Check if MinIO is configured and connection is healthy
     *
     * @return bool
     */
    public function isMinioHealthy(): bool
    {
        $health = $this->checkMinioHealth();
        return $health['status'] === 'healthy';
    }

    /**
     * Perform actual MinIO health check
     *
     * @return array
     */
    private function performMinioHealthCheck(): array
    {
        try {
            // Check if MinIO disk is configured
            $config = config('filesystems.disks.minio');
            if (!$config) {
                return [
                    'status' => 'error',
                    'message' => 'MinIO not configured',
                    'details' => 'MinIO disk configuration not found',
                    'checked_at' => now(),
                ];
            }

            // Test basic connection by trying to list files
            $startTime = microtime(true);
            $files = Storage::disk('minio')->files('', true);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Test write operation with a small test file
            $testFileName = 'health-check-' . time() . '.txt';
            $testContent = 'MinIO health check test - ' . now();
            
            $uploaded = Storage::disk('minio')->put($testFileName, $testContent);
            
            if ($uploaded) {
                // Test read operation
                $retrievedContent = Storage::disk('minio')->get($testFileName);
                
                // Clean up test file
                Storage::disk('minio')->delete($testFileName);
                
                if ($retrievedContent === $testContent) {
                    return [
                        'status' => 'healthy',
                        'message' => 'MinIO connection is healthy',
                        'details' => [
                            'endpoint' => $config['endpoint'],
                            'bucket' => $config['bucket'],
                            'response_time_ms' => $responseTime,
                            'file_count' => count($files),
                            'read_write_test' => 'passed',
                        ],
                        'checked_at' => now(),
                    ];
                } else {
                    return [
                        'status' => 'warning',
                        'message' => 'MinIO read/write test failed',
                        'details' => 'File content mismatch after upload',
                        'checked_at' => now(),
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'MinIO write test failed',
                    'details' => 'Unable to upload test file',
                    'checked_at' => now(),
                ];
            }

        } catch (Exception $e) {
            Log::error('MinIO health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => 'MinIO connection failed',
                'details' => $e->getMessage(),
                'checked_at' => now(),
            ];
        }
    }

    /**
     * Check public storage health
     *
     * @return array
     */
    private function checkPublicStorageHealth(): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . 'public';
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () {
            try {
                $testFileName = 'temp/health-check-' . time() . '.txt';
                $testContent = 'Public storage health check - ' . now();
                
                $uploaded = Storage::disk('public')->put($testFileName, $testContent);
                
                if ($uploaded) {
                    Storage::disk('public')->delete($testFileName);
                    
                    return [
                        'status' => 'healthy',
                        'message' => 'Public storage is healthy',
                        'checked_at' => now(),
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'Public storage write failed',
                        'checked_at' => now(),
                    ];
                }
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'message' => 'Public storage connection failed',
                    'details' => $e->getMessage(),
                    'checked_at' => now(),
                ];
            }
        });
    }

    /**
     * Get overall storage status
     *
     * @return array
     */
    private function getOverallStorageStatus(): array
    {
        $minioHealth = $this->checkMinioHealth();
        $publicHealth = $this->checkPublicStorageHealth();

        // Determine overall status
        if ($minioHealth['status'] === 'healthy' && $publicHealth['status'] === 'healthy') {
            $status = 'healthy';
            $message = 'All storage systems are healthy';
        } elseif ($minioHealth['status'] === 'error' || $publicHealth['status'] === 'error') {
            $status = 'error';
            $message = 'One or more storage systems have errors';
        } else {
            $status = 'warning';
            $message = 'Some storage systems have warnings';
        }

        return [
            'status' => $status,
            'message' => $message,
            'checked_at' => now(),
        ];
    }

    /**
     * Get storage health color for UI
     *
     * @param string $status
     * @return string
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            'healthy' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get storage health icon for UI
     *
     * @param string $status
     * @return string
     */
    public static function getStatusIcon(string $status): string
    {
        return match ($status) {
            'healthy' => 'heroicon-o-check-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'error' => 'heroicon-o-x-circle',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
