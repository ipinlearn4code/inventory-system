<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class CreateMinioBucket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minio:create-bucket {bucket?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create MinIO bucket if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $bucketName = $this->argument('bucket') ?? config('filesystems.disks.minio.bucket');
            
            $this->info("Creating MinIO bucket: {$bucketName}");
            
            // Get MinIO configuration
            $config = config('filesystems.disks.minio');
            
            // Create S3 client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'endpoint' => $config['endpoint'],
                'use_path_style_endpoint' => $config['use_path_style_endpoint'],
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);
            
            // Check if bucket exists
            if ($s3Client->doesBucketExist($bucketName)) {
                $this->info("Bucket '{$bucketName}' already exists.");
                return 0;
            }
            
            // Create bucket
            $s3Client->createBucket([
                'Bucket' => $bucketName,
            ]);
            
            $this->info("Bucket '{$bucketName}' created successfully!");
            
            // Test basic operations
            $this->info("Testing bucket access...");
            $testResult = Storage::disk('minio')->put('test.txt', 'test content');
            
            if ($testResult) {
                $this->info("✓ Bucket is accessible and writable");
                Storage::disk('minio')->delete('test.txt');
            } else {
                $this->error("✗ Bucket created but not accessible for write operations");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Failed to create bucket: " . $e->getMessage());
            return 1;
        }
    }
}
