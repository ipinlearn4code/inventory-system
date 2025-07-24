<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing assignment letter file preview URLs...\n";

try {
    // Get assignment letters with files
    $letters = \App\Models\AssignmentLetter::whereNotNull('file_path')->get();
    
    foreach ($letters as $letter) {
        echo "\n📋 Testing Letter #{$letter->letter_id} ({$letter->letter_number}):\n";
        echo "   File path: {$letter->file_path}\n";
        
        // Test if file exists in MinIO
        $disk = \Illuminate\Support\Facades\Storage::disk('minio');
        if ($disk->exists($letter->file_path)) {
            echo "   ✅ File exists in MinIO\n";
            
            // Test URL generation
            $previewUrl = $letter->getFileUrl();
            echo "   🔗 Preview URL: $previewUrl\n";
            
            // Test PdfPreviewService
            $pdfService = app(\App\Services\PdfPreviewService::class);
            $previewData = $pdfService->getPreviewData($letter);
            
            echo "   📊 Preview Data:\n";
            echo "      - Has File: " . ($previewData['hasFile'] ? 'Yes' : 'No') . "\n";
            echo "      - Preview URL: " . $previewData['previewUrl'] . "\n";
            echo "      - Download URL: " . $previewData['downloadUrl'] . "\n";
            echo "      - File Name: " . $previewData['fileName'] . "\n";
            echo "      - File Size: " . $previewData['fileSize'] . "\n";
            
        } else {
            echo "   ❌ File NOT found in MinIO\n";
        }
    }
    
    echo "\n🧪 Testing route generation:\n";
    $testLetter = $letters->first();
    if ($testLetter) {
        echo "   Route preview: " . route('assignment-letter.preview', ['letter' => $testLetter->letter_id]) . "\n";
        echo "   Route download: " . route('assignment-letter.download', ['letter' => $testLetter->letter_id]) . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
