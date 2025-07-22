<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['pn' => 'USER01', 'name' => 'John Doe', 'department_id' => 'IT01', 'branch_id' => 1, 'position' => 'IT Manager'],
            ['pn' => 'USER02', 'name' => 'Jane Smith', 'department_id' => 'HR01', 'branch_id' => 2, 'position' => 'HR Specialist'],
            ['pn' => 'ADMIN01', 'name' => 'Admin User', 'department_id' => 'IT01', 'branch_id' => 1, 'position' => 'System Administrator'],
            ['pn' => 'SUPER01', 'name' => 'Super Admin', 'department_id' => 'IT01', 'branch_id' => 1, 'position' => 'Super Administrator'],
            ['pn' => '1', 'name' => 'Guest User', 'department_id' => 'IT01', 'branch_id' => 1, 'position' => 'Guest'],
            ['pn' => '2', 'name' => 'Test User', 'department_id' => 'IT01', 'branch_id' => 1, 'position' => 'Nizam Engineer'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
