<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing assignment letter file paths to match MinIO storage...\n";

try {
    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
    
    // Get all files in MinIO
    $allFiles = $disk->allFiles('');
    echo "📄 Found " . count($allFiles) . " files in MinIO\n";
    
    // Filter out test files
    $assignmentFiles = array_filter($allFiles, function($file) {
        return !str_starts_with($file, 'test/');
    });
    
    echo "📋 Found " . count($assignmentFiles) . " assignment letter files\n";
    
    foreach ($assignmentFiles as $minioPath) {
        echo "\n🔍 Processing: $minioPath\n";
        
        // Extract filename from path
        $filename = basename($minioPath);
        echo "   Extracted filename: $filename\n";
        
        // Find assignment letter with matching filename pattern
        $letters = \App\Models\AssignmentLetter::whereNotNull('file_path')
            ->where('file_path', 'like', '%' . strtoupper($filename) . '%')
            ->orWhere('file_path', 'like', '%' . strtolower($filename) . '%')
            ->get();
            
        if ($letters->count() > 0) {
            foreach ($letters as $letter) {
                echo "   📝 Found matching letter #{$letter->letter_id} ({$letter->letter_number})\n";
                echo "   📂 Current path: {$letter->file_path}\n";
                echo "   🔄 Updating to: $minioPath\n";
                
                $letter->update(['file_path' => $minioPath]);
                echo "   ✅ Updated successfully\n";
            }
        } else {
            echo "   ⚠️  No matching assignment letter found for this file\n";
        }
    }
    
    echo "\n📊 Final verification:\n";
    $letters = \App\Models\AssignmentLetter::whereNotNull('file_path')->get();
    foreach ($letters as $letter) {
        echo "  - Letter #{$letter->letter_id} ({$letter->letter_number}): ";
        if ($disk->exists($letter->file_path)) {
            echo "✅ File exists in MinIO\n";
        } else {
            echo "❌ File NOT found in MinIO - Path: {$letter->file_path}\n";
        }
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
