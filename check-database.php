<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking database assignment letters:\n";

$letters = \App\Models\AssignmentLetter::all();
echo "Total assignment letters: " . $letters->count() . "\n\n";

foreach($letters as $letter) {
    echo "Letter #{$letter->letter_id}:\n";
    echo "  - Type: {$letter->letter_type}\n";
    echo "  - Number: {$letter->letter_number}\n";
    echo "  - Assignment ID: {$letter->assignment_id}\n";
    echo "  - File Path: " . ($letter->file_path ?? 'NULL') . "\n\n";
}
