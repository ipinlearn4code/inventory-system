<?php

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test DeviceAssignment properties
$assignment = App\Models\DeviceAssignment::with([
    'device', 
    'user.branch', 
    'branch', 
    'assignmentLetters.approver', 
    'assignmentLetters.creator', 
    'assignmentLetters.updater'
])->first();

if ($assignment) {
    echo "Assignment ID: " . $assignment->assignment_id . "\n";
    echo "Status: " . $assignment->status . "\n";
    echo "Notes: " . ($assignment->notes ?? 'null') . "\n";
    echo "Device Asset Code: " . $assignment->device->asset_code . "\n";
    echo "User Name: " . $assignment->user->name . "\n";
    
    $letter = $assignment->assignmentLetters->first();
    if ($letter) {
        echo "Letter ID: " . $letter->letter_id . "\n";
        echo "Letter Type: " . $letter->letter_type . "\n";
        echo "Approver: " . ($letter->approver->name ?? 'null') . "\n";
    } else {
        echo "No assignment letter found.\n";
    }
} else {
    echo "No assignments found.\n";
}
