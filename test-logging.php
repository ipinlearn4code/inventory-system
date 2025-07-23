<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Testing inventory logging...\n";

// Get the service
$service = $app->make(\App\Contracts\InventoryLogServiceInterface::class);

echo "Service loaded: " . get_class($service) . "\n";

// Test logging
try {
    $service->logInventoryAction('test_field', 'CREATE', null, ['test' => 'data']);
    echo "Log created successfully\n";
    
    // Check count
    $count = \App\Models\InventoryLog::count();
    echo "Total logs in database: " . $count . "\n";
    
    // Get latest log
    $latest = \App\Models\InventoryLog::latest('created_at')->first();
    if ($latest) {
        echo "Latest log: " . json_encode($latest->toArray()) . "\n";
    } else {
        echo "No logs found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
