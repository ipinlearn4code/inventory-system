<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing file upload fix for assignment letter #6...\n";

try {
    // Get letter #6 that was just updated
    $letter = \App\Models\AssignmentLetter::where('letter_id', 6)->first();
    
    if (!$letter) {
        echo "❌ Letter #6 not found\n";
        exit(1);
    }
    
    echo "📋 Current letter #6 details:\n";
    echo "   - Letter Number: {$letter->letter_number}\n";
    echo "   - Letter Type: {$letter->letter_type}\n";
    echo "   - Assignment ID: {$letter->assignment_id}\n";
    echo "   - Letter Date: {$letter->letter_date}\n";
    echo "   - Current file_path: " . ($letter->file_path ?? 'NULL') . "\n";
    
    // Check if file exists in MinIO
    if ($letter->file_path) {
        $disk = \Illuminate\Support\Facades\Storage::disk('minio');
        if ($disk->exists($letter->file_path)) {
            echo "   ✅ File exists in MinIO\n";
            echo "   📁 File size: " . number_format($disk->size($letter->file_path) / 1024, 2) . " KB\n";
        } else {
            echo "   ❌ File NOT found in MinIO\n";
            
            // Try to find it in public storage
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($publicDisk->exists($letter->file_path)) {
                echo "   ⚠️  File found in public storage instead\n";
            } else {
                echo "   ❌ File not found in public storage either\n";
            }
        }
    } else {
        echo "   ⚠️  No file path set\n";
    }
    
    // Check file URL generation
    if ($letter->hasFile()) {
        echo "   🔗 Preview URL: " . $letter->getFileUrl() . "\n";
    }
    
    echo "\n🔍 Checking for problematic file paths in database:\n";
    $problemLetters = \App\Models\AssignmentLetter::whereNotNull('file_path')
        ->where('file_path', 'like', 'assignment-letters/%')
        ->get();
        
    foreach ($problemLetters as $probLetter) {
        echo "   ❌ Letter #{$probLetter->letter_id}: {$probLetter->file_path}\n";
    }
    
    if ($problemLetters->count() === 0) {
        echo "   ✅ No problematic file paths found!\n";
    }
    
    echo "\n✅ Test completed successfully!\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
