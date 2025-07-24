<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking related data:\n";
echo "Device Assignments: " . \App\Models\DeviceAssignment::count() . "\n";
echo "Users: " . \App\Models\User::count() . "\n";

if (\App\Models\DeviceAssignment::count() > 0) {
    echo "\nFirst few device assignments:\n";
    $assignments = \App\Models\DeviceAssignment::with(['user', 'device'])->take(3)->get();
    foreach ($assignments as $assignment) {
        echo "  - Assignment #{$assignment->assignment_id}: {$assignment->user->name} - {$assignment->device->asset_code}\n";
    }
}
