<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Cleaning up malformed assignment letter file paths...\n";

try {
    // Get all assignment letters with file paths
    $letters = \App\Models\AssignmentLetter::whereNotNull('file_path')->get();
    
    $fixed = 0;
    $errors = 0;
    
    foreach ($letters as $letter) {
        echo "\nProcessing Letter #{$letter->letter_id} ({$letter->letter_number}):\n";
        echo "  Current path: " . $letter->file_path . "\n";
        
        // Check if path is malformed (JSON format or local path)
        if (str_contains($letter->file_path, '{') || str_starts_with($letter->file_path, 'assignment-letters/')) {
            echo "  🔧 Malformed path detected, clearing...\n";
            
            // Clear the malformed path - user will need to re-upload
            $letter->update(['file_path' => null]);
            $fixed++;
            
            echo "  ✅ Path cleared - user needs to re-upload file\n";
        } else {
            // Check if file exists in MinIO
            $disk = \Illuminate\Support\Facades\Storage::disk('minio');
            if ($disk->exists($letter->file_path)) {
                echo "  ✅ Path is correct and file exists in MinIO\n";
            } else {
                echo "  ⚠️  Path looks correct but file not found in MinIO\n";
                echo "      User may need to re-upload file\n";
            }
        }
    }
    
    echo "\n📊 Summary:\n";
    echo "  - Fixed malformed paths: $fixed\n";
    echo "  - Errors: $errors\n";
    echo "  - Users with cleared paths will need to re-upload their files\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
