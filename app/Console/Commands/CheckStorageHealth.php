<?php

namespace App\Console\Commands;

use App\Services\StorageHealthService;
use Illuminate\Console\Command;

class CheckStorageHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:health-check {--refresh : Force refresh cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of all storage systems (MinIO, Public storage)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $healthService = app(\App\Services\StorageHealthService::class);
        
        $this->info('🔍 Checking storage health...');
        $this->newLine();
        
        // Check if refresh flag is set
        if ($this->option('refresh')) {
            $this->info('🔄 Refreshing health check cache...');
            $storageStatus = $healthService->refreshStorageHealth();
        } else {
            $storageStatus = $healthService->checkAllStorageHealth();
        }
        
        // Display overall status
        $overallStatus = $storageStatus['overall'];
        $this->displayStatus('Overall Status', $overallStatus);
        
        $this->newLine();
        
        // Display MinIO status
        $minioStatus = $storageStatus['minio'];
        $this->displayStatus('MinIO Storage', $minioStatus);
        
        if (isset($minioStatus['details']) && is_array($minioStatus['details'])) {
            $this->displayMinioDetails($minioStatus['details']);
        }
        
        $this->newLine();
        
        // Display Public Storage status
        $publicStatus = $storageStatus['public'];
        $this->displayStatus('Public Storage', $publicStatus);
        
        $this->newLine();
        
        // Show recommendations
        $this->showRecommendations($storageStatus);
        
        return $overallStatus['status'] === 'healthy' ? 0 : 1;
    }
    
    private function displayStatus(string $title, array $status): void
    {
        $icon = match ($status['status']) {
            'healthy' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            default => '❓',
        };
        
        $this->line("<options=bold>{$icon} {$title}:</>");
        $this->line("   Status: <fg=" . $this->getStatusColor($status['status']) . ">" . ucfirst($status['status']) . "</>");
        $this->line("   Message: {$status['message']}");
        
        if (isset($status['details']) && is_string($status['details'])) {
            $this->line("   Details: {$status['details']}");
        }
        
        if (isset($status['checked_at'])) {
            $this->line("   Checked: {$status['checked_at']->format('Y-m-d H:i:s')}");
        }
    }
    
    private function displayMinioDetails(array $details): void
    {
        if (isset($details['endpoint'])) {
            $this->line("   Endpoint: {$details['endpoint']}");
        }
        if (isset($details['bucket'])) {
            $this->line("   Bucket: {$details['bucket']}");
        }
        if (isset($details['response_time_ms'])) {
            $this->line("   Response Time: {$details['response_time_ms']}ms");
        }
        if (isset($details['file_count'])) {
            $this->line("   Files in Bucket: {$details['file_count']}");
        }
    }
    
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'healthy' => 'green',
            'warning' => 'yellow',
            'error' => 'red',
            default => 'gray',
        };
    }
    
    private function showRecommendations(array $storageStatus): void
    {
        $this->info('💡 Recommendations:');
        
        if ($storageStatus['minio']['status'] === 'error') {
            $this->warn('- Check if MinIO server is running on the configured endpoint');
            $this->warn('- Verify MinIO credentials in .env file');
            $this->warn('- Ensure the bucket exists and is accessible');
        }
        
        if ($storageStatus['public']['status'] === 'error') {
            $this->warn('- Check file system permissions for public storage');
            $this->warn('- Verify storage directory exists and is writable');
        }
        
        if ($storageStatus['overall']['status'] === 'healthy') {
            $this->info('- All storage systems are working properly! 🎉');
        }
    }
}
