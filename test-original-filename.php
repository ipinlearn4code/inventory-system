<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing original filename preservation...\n";

// Create a test file with known name
$testFileName = 'test-original-filename.pdf';
$testContent = 'Test PDF content for filename preservation test';
$testFilePath = storage_path('app/public/test-files/' . $testFileName);

// Ensure directory exists
$dir = dirname($testFilePath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Create test file
file_put_contents($testFilePath, $testContent);

try {
    // Create a mock UploadedFile with original name
    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $testFilePath,
        $testFileName, // This should be preserved
        'application/pdf',
        null,
        true
    );
    
    echo "✅ Created test UploadedFile with original name: {$uploadedFile->getClientOriginalName()}\n";
    
    // Test our MinioStorageService
    $storageService = app(\App\Services\MinioStorageService::class);
    
    // Mock assignment data
    $assignmentId = 1;
    $letterType = 'test';
    
    echo "🧪 Testing storeAssignmentLetterFile with:\n";
    echo "  - Assignment ID: {$assignmentId}\n";
    echo "  - Letter Type: {$letterType}\n";
    echo "  - Original filename: {$uploadedFile->getClientOriginalName()}\n";
    
    // This should now preserve the original filename
    $storedPath = $storageService->storeAssignmentLetterFile($uploadedFile, $assignmentId, $letterType);
    
    if ($storedPath) {
        echo "✅ File stored successfully at: {$storedPath}\n";
        
        // Check if the stored path contains the original filename (slugified)
        $expectedFilename = 'test-original-filename.pdf';
        if (str_contains($storedPath, 'test-original-filename')) {
            echo "✅ Original filename preserved in path!\n";
        } else {
            echo "❌ Original filename NOT preserved. Path: {$storedPath}\n";
        }
        
        // Verify file exists in MinIO
        $disk = \Illuminate\Support\Facades\Storage::disk('minio');
        if ($disk->exists($storedPath)) {
            echo "✅ File exists in MinIO\n";
            
            // Clean up test file
            $disk->delete($storedPath);
            echo "🗑️  Test file cleaned up from MinIO\n";
        } else {
            echo "❌ File NOT found in MinIO\n";
        }
    } else {
        echo "❌ File storage failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} finally {
    // Clean up local test file
    if (file_exists($testFilePath)) {
        unlink($testFilePath);
        echo "🗑️  Local test file cleaned up\n";
    }
}
