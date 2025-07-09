<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Auth;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $auths = [
            ['pn' => 'USER01', 'password' => bcrypt('password123'), 'role' => 'user'],
            ['pn' => 'USER02', 'password' => bcrypt('password123'), 'role' => 'user'],
            ['pn' => 'ADMIN01', 'password' => bcrypt('admin123'), 'role' => 'admin'],
            ['pn' => 'SUPER01', 'password' => bcrypt('super123'), 'role' => 'superadmin'],
        ];

        foreach ($auths as $auth) {
            Auth::create($auth);
        }
    }
}
