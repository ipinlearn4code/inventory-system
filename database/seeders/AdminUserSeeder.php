<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Models\Auth;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create IT department
        // $department = Department::create([
        //     'department_id' => 'IT01',
        //     'name' => 'Information Technology'
        // ]);

        // Create admin user
        $user = User::create([
            'pn' => 'ADM001',
            'name' => 'System Administrator',
            'department_id' => 'IT01',
            'position' => 'System Administrator',
            // 'password' => Hash::make('password123'),
        ]);

        // Create auth record
        Auth::create(attributes: [
            'pn' => 'ADM001',
            'password' => Hash::make('password123'),
            'role' => 'superadmin'
        ]);
    }
}
