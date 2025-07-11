<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\MinioStorageService;
use Illuminate\Support\Facades\File;

/**
 * Step-by-step file upload testing script
 * Run this with: php artisan tinker
 * Then paste each section one by one
 */

// Step 1: Test basic local storage
echo "=== Step 1: Testing Local Storage ===\n";

// Create a dummy file for testing
$testContent = "This is a test PDF content for validation";
$tempPath = storage_path('app/test-file.pdf');
file_put_contents($tempPath, $testContent);

// Test local disk storage
try {
    $localPath = Storage::disk('public')->put('temp-uploads', new \Illuminate\Http\File($tempPath));
    echo "✓ Local storage works: {$localPath}\n";
    
    // Check if file exists
    if (Storage::disk('public')->exists($localPath)) {
        echo "✓ File exists in local storage\n";
    } else {
        echo "✗ File NOT found in local storage\n";
    }
    
    // Clean up
    Storage::disk('public')->delete($localPath);
    unlink($tempPath);
    
} catch (Exception $e) {
    echo "✗ Local storage failed: " . $e->getMessage() . "\n";
}

// Step 2: Test MinIO connection
echo "\n=== Step 2: Testing MinIO Connection ===\n";

try {
    // Test if MinIO disk is configured
    $minioConfig = config('filesystems.disks.minio');
    echo "MinIO Config:\n";
    echo "- Endpoint: " . $minioConfig['endpoint'] . "\n";
    echo "- Bucket: " . $minioConfig['bucket'] . "\n";
    echo "- Key: " . $minioConfig['key'] . "\n";
    
    // Test connection by listing files (this will fail if MinIO is not running)
    $files = Storage::disk('minio')->files();
    echo "✓ MinIO connection successful\n";
    
} catch (Exception $e) {
    echo "✗ MinIO connection failed: " . $e->getMessage() . "\n";
    echo "Note: Make sure MinIO server is running on http://localhost:9000\n";
}

// Step 3: Test MinioStorageService
echo "\n=== Step 3: Testing MinioStorageService ===\n";

try {
    // Create a test PDF file
    $testPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj xref 0 4 0000000000 65535 f 0000000009 00000 n 0000000058 00000 n 0000000115 00000 n trailer<</Size 4/Root 1 0 R>>startxref 165 %%EOF";
    $testPdfPath = storage_path('app/test-assignment.pdf');
    file_put_contents($testPdfPath, $testPdfContent);
    
    // Create UploadedFile
    $uploadedFile = new UploadedFile(
        $testPdfPath,
        'test-assignment.pdf',
        'application/pdf',
        null,
        true
    );
    
    $service = new MinioStorageService();
    $result = $service->storeAssignmentLetterFile(
        $uploadedFile,
        'assignment',
        1,
        now(),
        'TEST-001'
    );
    
    if ($result) {
        echo "✓ MinioStorageService works: {$result}\n";
        
        // Test URL generation
        $url = $service->getTemporaryUrl($result);
        if ($url) {
            echo "✓ Temporary URL generated: {$url}\n";
        } else {
            echo "✗ Failed to generate temporary URL\n";
        }
        
        // Clean up
        $service->deleteFile($result);
    } else {
        echo "✗ MinioStorageService failed\n";
    }
    
    // Clean up
    unlink($testPdfPath);
    
} catch (Exception $e) {
    echo "✗ MinioStorageService error: " . $e->getMessage() . "\n";
}

// Step 4: Test file validation
echo "\n=== Step 4: Testing File Validation ===\n";

try {
    // Test PDF validation
    $validPdf = storage_path('app/valid-test.pdf');
    file_put_contents($validPdf, $testPdfContent);
    
    $validator = \Validator::make(
        ['file' => new UploadedFile($validPdf, 'test.pdf', 'application/pdf', null, true)],
        ['file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg']]
    );
    
    if ($validator->passes()) {
        echo "✓ PDF validation passed\n";
    } else {
        echo "✗ PDF validation failed: " . implode(', ', $validator->errors()->all()) . "\n";
    }
    
    unlink($validPdf);
    
    // Test invalid file type
    $invalidFile = storage_path('app/invalid-test.txt');
    file_put_contents($invalidFile, "This is a text file");
    
    $validator = \Validator::make(
        ['file' => new UploadedFile($invalidFile, 'test.txt', 'text/plain', null, true)],
        ['file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg']]
    );
    
    if ($validator->fails()) {
        echo "✓ Invalid file type correctly rejected\n";
    } else {
        echo "✗ Invalid file type was accepted (should be rejected)\n";
    }
    
    unlink($invalidFile);
    
} catch (Exception $e) {
    echo "✗ Validation test error: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Complete ===\n";
echo "Check the output above to identify which component is failing.\n";
