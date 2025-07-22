<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth;
use Spatie\Permission\Models\Role;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $auths = [
            ['pn' => 'USER01', 'password' => Hash::make('password123'), 'role' => 'user'],
            ['pn' => 'USER02', 'password' => Hash::make('password123'), 'role' => 'user'],
            ['pn' => 'ADMIN01', 'password' => Hash::make('admin123'), 'role' => 'admin'],
            ['pn' => 'SUPER01', 'password' => Hash::make('super123'), 'role' => 'superadmin'],
            ['pn' => '1', 'password' => Hash::make('1'), 'role' => 'guest'],
            ['pn' => '2', 'password' => Hash::make('2'), 'role' => 'superadmin'], // Assuming '2' is a test user
        ];

        foreach ($auths as $authData) {
            $auth = Auth::create($authData);
            
            // Assign Spatie role to the auth model
            $role = Role::where('name', $authData['role'])->first();
            if ($role) {
                $auth->assignRole($role);
            }
        }
    }
}
