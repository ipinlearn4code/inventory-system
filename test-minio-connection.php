<?php

// Bootstrap Laravel properly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing MinIO connection...\n";

try {
    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
    echo "✅ MinIO disk loaded successfully\n";
    
    // Test basic connectivity
    $directories = $disk->directories('');
    echo "📁 Available directories: " . json_encode($directories) . "\n";
    
    // Test writing a simple file
    $testContent = "Test file created at " . date('Y-m-d H:i:s');
    $testPath = 'test-connection.txt';
    
    $result = $disk->put($testPath, $testContent);
    if ($result) {
        echo "✅ Test file written successfully\n";
        
        // Test reading the file
        $content = $disk->get($testPath);
        echo "✅ Test file read successfully: " . $content . "\n";
        
        // Clean up
        $disk->delete($testPath);
        echo "✅ Test file deleted successfully\n";
    } else {
        echo "❌ Failed to write test file\n";
    }
    
} catch(Exception $e) {
    echo "❌ MinIO Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
