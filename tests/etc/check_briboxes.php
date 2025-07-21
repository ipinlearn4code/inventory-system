<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Checking briboxes table ===\n";
    $briboxes = DB::table('briboxes')->get();
    echo "Briboxes found: " . $briboxes->count() . "\n";
    
    foreach($briboxes as $b) {
        echo "ID: {$b->bribox_id}\n";
    }
    
} catch(Exception $e) {
    echo "Error with briboxes table: " . $e->getMessage() . "\n";
    
    // Try branches table instead
    echo "\n=== Checking branches table ===\n";
    try {
        $branches = DB::table('branches')->get();
        echo "Branches found: " . $branches->count() . "\n";
        
        foreach($branches as $b) {
            $id = $b->branch_id ?? $b->bribox_id ?? $b->id ?? 'no-id';
            echo "ID: {$id}\n";
        }
    } catch(Exception $e2) {
        echo "Error with branches table: " . $e2->getMessage() . "\n";
    }
}
