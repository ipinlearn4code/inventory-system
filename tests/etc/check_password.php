<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Auth;
use Illuminate\Support\Facades\Hash;

$auth = Auth::where('pn', 'ADMIN01')->first();
if ($auth) {
    echo "Testing passwords for ADMIN01:\n";
    echo "password123: " . (Hash::check('password123', $auth->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "admin: " . (Hash::check('admin', $auth->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "123456: " . (Hash::check('123456', $auth->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "password: " . (Hash::check('password', $auth->password) ? 'MATCH' : 'NO MATCH') . "\n";
    
    // Update password to known value
    echo "\nUpdating password to 'password123'...\n";
    $auth->password = bcrypt('password123');
    $auth->save();
    echo "✅ Password updated!\n";
} else {
    echo "ADMIN01 not found\n";
}
