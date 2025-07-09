<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $auths = [
            ['pn' => 'USER01', 'password' => Hash::make('password123'), 'role' => 'user'],
            ['pn' => 'USER02', 'password' => Hash::make('password123'), 'role' => 'user'],
            ['pn' => 'ADMIN01', 'password' => Hash::make('admin123'), 'role' => 'admin'],
            ['pn' => 'SUPER01', 'password' => Hash::make('super123'), 'role' => 'superadmin'],
        ];

        foreach ($auths as $auth) {
            Auth::create($auth);
        }
    }
}
