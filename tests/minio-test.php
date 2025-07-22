<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\MinioStorageService;

/**
 * MinIO API Test Script
 * 
 * This script tests the MinIO file upload/download functionality
 * Run this with: php artisan tinker
 * Then: include 'tests/minio-test.php';
 */

echo "=== MinIO API Test Script ===\n";

// Test MinIO service health
echo "\n1. Testing MinIO Health Check...\n";
$minioService = app(MinioStorageService::class);
$health = $minioService->isHealthy();
echo "Health Status: " . $health['status'] . "\n";
echo "Message: " . $health['message'] . "\n";

if ($health['status'] !== 'healthy') {
    echo "❌ MinIO is not healthy. Please check your configuration.\n";
    return;
}

echo "✅ MinIO is healthy!\n";

// Test file upload (simulated)
echo "\n2. Testing File Upload Simulation...\n";
try {
    // Create a test file content
    $testContent = "This is a test file for MinIO upload - " . now();
    $testPath = 'test-uploads/test-file-' . time() . '.txt';
    
    // Store using Laravel Storage
    $stored = Storage::disk('minio')->put($testPath, $testContent);
    
    if ($stored) {
        echo "✅ Test file uploaded successfully!\n";
        echo "Path: " . $testPath . "\n";
        
        // Test file retrieval
        echo "\n3. Testing File Download...\n";
        $retrieved = Storage::disk('minio')->get($testPath);
        
        if ($retrieved === $testContent) {
            echo "✅ File downloaded and content matches!\n";
        } else {
            echo "❌ File content mismatch!\n";
        }
        
        // Test temporary URL generation
        echo "\n4. Testing Temporary URL Generation...\n";
        $tempUrl = $minioService->getTemporaryUrl($testPath);
        
        if ($tempUrl) {
            echo "✅ Temporary URL generated successfully!\n";
            echo "URL: " . substr($tempUrl, 0, 100) . "...\n";
        } else {
            echo "❌ Failed to generate temporary URL!\n";
        }
        
        // Clean up
        echo "\n5. Cleaning up test file...\n";
        $deleted = Storage::disk('minio')->delete($testPath);
        
        if ($deleted) {
            echo "✅ Test file cleaned up successfully!\n";
        } else {
            echo "❌ Failed to clean up test file!\n";
        }
        
    } else {
        echo "❌ Failed to upload test file!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
}

echo "\n=== API Endpoint Information ===\n";
echo "Base URL: " . config('app.url') . "/api/v1/admin/files\n";
echo "\nAvailable Endpoints:\n";
echo "• POST   /assignment-letters     - Upload assignment letter\n";
echo "• GET    /assignment-letters/{id}/download - Download assignment letter\n";
echo "• GET    /assignment-letters/{id}/url - Get temporary URL\n";
echo "• POST   /upload                 - Upload general file\n";
echo "• POST   /download               - Download general file\n";
echo "• DELETE /delete                 - Delete file\n";
echo "• GET    /health                 - Health check\n";

echo "\n=== cURL Test Examples ===\n";
echo "Replace 'your-token-here' with actual Bearer token\n\n";

echo "Health Check:\n";
echo "curl -X GET \\\n";
echo "  " . config('app.url') . "/api/v1/admin/files/health \\\n";
echo "  -H 'Authorization: Bearer your-token-here'\n\n";

echo "Upload General File:\n";
echo "curl -X POST \\\n";
echo "  " . config('app.url') . "/api/v1/admin/files/upload \\\n";
echo "  -H 'Authorization: Bearer your-token-here' \\\n";
echo "  -F 'file=@/path/to/your/file.pdf' \\\n";
echo "  -F 'directory=test' \\\n";
echo "  -F 'filename=test-file.pdf'\n\n";

echo "=== Test Complete ===\n";
