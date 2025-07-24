<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking assignment letter files in MinIO...\n";

try {
    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
    
    // Check all files in MinIO
    $allFiles = $disk->allFiles('');
    echo "📄 All files in MinIO (" . count($allFiles) . " files):\n";
    foreach ($allFiles as $file) {
        echo "  - " . $file . "\n";
    }
    
    // Check all directories in MinIO
    $allDirectories = $disk->allDirectories('');
    echo "\n📁 All directories in MinIO (" . count($allDirectories) . " directories):\n";
    foreach ($allDirectories as $dir) {
        echo "  - " . $dir . "\n";
    }
    
    // Check assignment letters in database
    echo "\n📋 Assignment letters in database:\n";
    $letters = \App\Models\AssignmentLetter::select('letter_id', 'letter_number', 'file_path')->get();
    foreach ($letters as $letter) {
        echo "  - Letter #{$letter->letter_id} ({$letter->letter_number}): ";
        if ($letter->file_path) {
            echo "Has file: " . $letter->file_path . "\n";
            
            // Check if file exists in MinIO
            if ($disk->exists($letter->file_path)) {
                echo "    ✅ File exists in MinIO\n";
            } else {
                echo "    ❌ File NOT found in MinIO\n";
            }
        } else {
            echo "No file\n";
        }
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
