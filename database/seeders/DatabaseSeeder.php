<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            
            // Basic data
            DepartmentSeeder::class,
            MainBranchSeeder::class,
            BranchSeeder::class,
            
            // Users and Authentication
            UserSeeder::class,
            AuthSeeder::class,
            
            // Inventory structure
            BriboxesCategorySeeder::class,
            BriboxSeeder::class,
            
            // Devices and assignments
            DeviceSeeder::class,
            DeviceAssignmentSeeder::class,
            AssignmentLetterSeeder::class,
        ]);
    }
}
