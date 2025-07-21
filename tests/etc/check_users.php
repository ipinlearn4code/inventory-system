<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Auth;

echo "=== Users and Their Auth/Roles ===\n";
$users = User::with('auth')->get(['user_id', 'name', 'pn']);

foreach ($users as $user) {
    $auth = $user->auth;
    $roles = $auth ? $auth->getRoleNames()->join(', ') : 'No auth';
    echo "PN: {$user->pn} | Name: {$user->name} | Roles: {$roles}\n";
}

echo "\n=== Creating Test Admin User ===\n";

// Try to find or create an admin user
$adminUser = User::where('pn', 'ADMIN01')->first();

if (!$adminUser) {
    echo "Creating ADMIN01 user...\n";
    $adminUser = User::create([
        'name' => 'Test Admin',
        'pn' => 'ADMIN01',
        'department_id' => 1, // Assuming department 1 exists
        'branch_id' => 1, // Assuming branch 1 exists
        'position' => 'Administrator',
    ]);
    echo "✅ ADMIN01 user created\n";
} else {
    echo "✅ ADMIN01 user already exists\n";
}

// Create auth record
$auth = Auth::where('pn', 'ADMIN01')->first();
if (!$auth) {
    echo "Creating ADMIN01 auth record...\n";
    $auth = Auth::create([
        'pn' => 'ADMIN01',
        'password' => bcrypt('password123'),
        'role' => 'admin',
    ]);
    echo "✅ ADMIN01 auth record created\n";
} else {
    echo "✅ ADMIN01 auth record already exists\n";
}

// Assign admin role using Spatie
if (!$auth->hasRole('admin')) {
    $auth->assignRole('admin');
    echo "✅ Admin role assigned to ADMIN01\n";
} else {
    echo "✅ ADMIN01 already has admin role\n";
}

echo "\n=== Final User Check ===\n";
$auth->refresh();
$roles = $auth->getRoleNames()->join(', ');
echo "ADMIN01 - Roles: {$roles}\n";

echo "\n✅ Setup complete! You can now test with PN: ADMIN01, Password: password123\n";
