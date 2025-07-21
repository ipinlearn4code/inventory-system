<?php

use Illuminate\Support\Facades\Storage;
use App\Services\MinioStorageService;

// Test MinIO bucket creation and connection
try {
    echo "Testing MinIO connection...\n";
    
    // Step 1: Check MinIO configuration
    $config = config('filesystems.disks.minio');
    echo "MinIO Config:\n";
    echo "- Endpoint: " . $config['endpoint'] . "\n";
    echo "- Bucket: " . $config['bucket'] . "\n";
    echo "- Access Key: " . $config['key'] . "\n";
    
    // Step 2: Test basic file operations
    $testContent = "Test file content for MinIO";
    $testFileName = 'test-file-' . time() . '.txt';
    
    // Put file
    $result = Storage::disk('minio')->put($testFileName, $testContent);
    if ($result) {
        echo "File uploaded successfully: {$testFileName}\n";
    } else {
        echo "Failed to upload file!\n";
    }
    
    // Check if file exists
    if (Storage::disk('minio')->exists($testFileName)) {
        echo "File exists in MinIO storage!\n";
        
        // Get file
        $retrievedContent = Storage::disk('minio')->get($testFileName);
        if ($retrievedContent === $testContent) {
            echo "File retrieved successfully and content matches!\n";
        } else {
            echo "File retrieved but content doesn't match!\n";
        }
        
        // Delete file
        Storage::disk('minio')->delete($testFileName);
        echo "File deleted successfully!\n";
    } else {
        echo "File does not exist in MinIO storage!\n";
    }
    
    echo "MinIO connection test completed successfully!\n";
    
} catch (Exception $e) {
    echo "MinIO connection test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
