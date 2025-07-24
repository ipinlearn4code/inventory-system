<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Migrating existing assignment letter files to new format...\n";
echo "New format: {assignment_id}/{letter_type}/{original_filename.ext}\n\n";

try {
    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
    $storageService = app(\App\Services\MinioStorageService::class);
    
    // Get all assignment letters with file paths
    $letters = \App\Models\AssignmentLetter::whereNotNull('file_path')->get();
    
    if ($letters->count() === 0) {
        echo "No assignment letters with files found.\n";
        exit(0);
    }
    
    echo "Found {$letters->count()} assignment letters with files to migrate.\n\n";
    
    foreach ($letters as $letter) {
        echo "🔄 Migrating Letter #{$letter->letter_id} ({$letter->letter_type}):\n";
        echo "   Current path: {$letter->file_path}\n";
        
        // Skip if already in new format
        if (preg_match('/^\d+\/[a-z]+\/.*\.(pdf|jpg|jpeg)$/i', $letter->file_path)) {
            echo "   ✅ Already in new format, skipping\n\n";
            continue;
        }
        
        // Check if file exists in MinIO
        if (!$disk->exists($letter->file_path)) {
            echo "   ❌ File not found in MinIO, skipping\n\n";
            continue;
        }
        
        try {
            // Extract original filename from current path
            $currentFilename = basename($letter->file_path);
            
            // Ensure we have a proper extension
            $pathInfo = pathinfo($currentFilename);
            $extension = $pathInfo['extension'] ?? 'pdf';
            
            // Create a meaningful filename if it's just a UUID
            if (preg_match('/^[0-9a-f-]{36}$/i', $pathInfo['filename'])) {
                // Use letter type and number for UUID filenames
                $newName = $letter->letter_type . '_' . str_replace(['/', ' ', '-'], '_', $letter->letter_number);
            } else {
                $newName = $pathInfo['filename'];
            }
            
            // Slugify the filename
            $slugged = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $newName);
            $slugged = preg_replace('/_+/', '_', $slugged);
            $slugged = trim($slugged, '_') ?: 'file';
            
            $newFilename = $slugged . '.' . $extension;
            
            // Generate new path
            $newDirectory = "{$letter->assignment_id}/{$letter->letter_type}";
            
            // Handle potential filename collision
            $counter = 1;
            $finalFilename = $newFilename;
            while ($disk->exists($newDirectory . '/' . $finalFilename)) {
                $pathInfo = pathinfo($newFilename);
                $finalFilename = $pathInfo['filename'] . '-' . $counter . '.' . $pathInfo['extension'];
                $counter++;
            }
            
            $newPath = $newDirectory . '/' . $finalFilename;
            
            echo "   📁 New path: {$newPath}\n";
            
            // Download existing file content
            $fileContent = $disk->get($letter->file_path);
            
            // Store in new location
            $stored = $disk->put($newPath, $fileContent);
            
            if (!$stored) {
                echo "   ❌ Failed to store file in new location\n\n";
                continue;
            }
            
            // Update database
            $letter->update(['file_path' => $newPath]);
            
            // Delete old file
            $disk->delete($letter->file_path);
            
            echo "   ✅ Migration successful\n";
            echo "   🗑️  Old file deleted\n\n";
            
        } catch (\Exception $e) {
            echo "   ❌ Migration failed: " . $e->getMessage() . "\n\n";
            continue;
        }
    }
    
    echo "📊 Final verification:\n";
    $migratedLetters = \App\Models\AssignmentLetter::whereNotNull('file_path')->get();
    $newFormatCount = 0;
    $oldFormatCount = 0;
    
    foreach ($migratedLetters as $letter) {
        if (preg_match('/^\d+\/[a-z]+\/.*\.(pdf|jpg|jpeg)$/i', $letter->file_path)) {
            $newFormatCount++;
            echo "  ✅ Letter #{$letter->letter_id}: {$letter->file_path}\n";
        } else {
            $oldFormatCount++;
            echo "  ❌ Letter #{$letter->letter_id}: {$letter->file_path} (still old format)\n";
        }
    }
    
    echo "\n📈 Migration Summary:\n";
    echo "  - Files in new format: {$newFormatCount}\n";
    echo "  - Files still in old format: {$oldFormatCount}\n";
    echo "  - Total files: " . $migratedLetters->count() . "\n";
    
    if ($oldFormatCount === 0) {
        echo "\n🎉 All files successfully migrated to new format!\n";
    } else {
        echo "\n⚠️  Some files still need manual attention.\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
