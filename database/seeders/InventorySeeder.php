<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\Auth;
use App\Models\MainBranch;
use App\Models\Branch;
use App\Models\BriboxesCategory;
use App\Models\Bribox;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\AssignmentLetter;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments
        $departments = [
            ['department_id' => 'IT01', 'name' => 'Information Technology'],
            ['department_id' => 'HR01', 'name' => 'Human Resources'],
            ['department_id' => 'FN01', 'name' => 'Finance'],
            ['department_id' => 'OP01', 'name' => 'Operations'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // 2. Main Branches
        $mainBranches = [
            ['main_branch_code' => 'HQ01', 'main_branch_name' => 'Head Office Jakarta'],
            ['main_branch_code' => 'SB01', 'main_branch_name' => 'Regional Surabaya'],
            ['main_branch_code' => 'BD01', 'main_branch_name' => 'Regional Bandung'],
        ];

        foreach ($mainBranches as $mainBranch) {
            MainBranch::create($mainBranch);
        }

        // 3. Branches
        $branches = [
            ['branch_code' => 'JKT001', 'unit_name' => 'Jakarta Central', 'main_branch_id' => 1],
            ['branch_code' => 'JKT002', 'unit_name' => 'Jakarta South', 'main_branch_id' => 1],
            ['branch_code' => 'SBY001', 'unit_name' => 'Surabaya Main', 'main_branch_id' => 2],
            ['branch_code' => 'BDG001', 'unit_name' => 'Bandung Central', 'main_branch_id' => 3],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        // 4. Users
        $users = [
            ['pn' => 'USER01', 'name' => 'John Doe', 'department_id' => 'IT01', 'position' => 'IT Manager'],
            ['pn' => 'USER02', 'name' => 'Jane Smith', 'department_id' => 'HR01', 'position' => 'HR Specialist'],
            ['pn' => 'ADMIN01', 'name' => 'Admin User', 'department_id' => 'IT01', 'position' => 'System Administrator'],
            ['pn' => 'SUPER01', 'name' => 'Super Admin', 'department_id' => 'IT01', 'position' => 'Super Administrator'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // 5. Auth records
        $auths = [
            ['pn' => 'USER01', 'password' => bcrypt('password123'), 'role' => 'user'],
            ['pn' => 'USER02', 'password' => bcrypt('password123'), 'role' => 'user'],
            ['pn' => 'ADMIN01', 'password' => bcrypt('admin123'), 'role' => 'admin'],
            ['pn' => 'SUPER01', 'password' => bcrypt('super123'), 'role' => 'superadmin'],
        ];

        foreach ($auths as $auth) {
            Auth::create($auth);
        }

        // 6. Bribox Categories
        $categories = [
            ['category_name' => 'Storage'],
            ['category_name' => 'Display'],
            ['category_name' => 'Accessories'],
        ];

        foreach ($categories as $category) {
            BriboxesCategory::create($category);
        }

        // 7. Briboxes
        $briboxes = [
            ['bribox_id' => 'A1', 'type' => 'Laptop Storage', 'bribox_category_id' => 1],
            ['bribox_id' => 'A2', 'type' => 'Desktop Storage', 'bribox_category_id' => 1],
            ['bribox_id' => 'B1', 'type' => 'Monitor Display', 'bribox_category_id' => 2],
            ['bribox_id' => 'C1', 'type' => 'Accessories Box', 'bribox_category_id' => 3],
        ];

        foreach ($briboxes as $bribox) {
            Bribox::create($bribox);
        }

        // 8. Devices
        $devices = [
            [
                'brand_name' => 'Dell',
                'serial_number' => 'DL001234567',
                'asset_code' => 'AST001',
                'bribox_id' => 'A1',
                'condition' => 'Baik',
                'spec1' => 'Intel i5-10400',
                'spec2' => '8GB RAM',
                'spec3' => '256GB SSD',
                'dev_date' => '2023-01-15',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
            [
                'brand_name' => 'HP',
                'serial_number' => 'HP001234567',
                'asset_code' => 'AST002',
                'bribox_id' => 'A1',
                'condition' => 'Baik',
                'spec1' => 'Intel i7-11700',
                'spec2' => '16GB RAM',
                'spec3' => '512GB SSD',
                'dev_date' => '2023-02-20',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
        ];

        foreach ($devices as $device) {
            Device::create($device);
        }

        // 9. Device Assignments
        // Get user and branch IDs dynamically
        $user1 = User::where('pn', 'USER01')->first();
        $user2 = User::where('pn', 'USER02')->first();
        $branch1 = Branch::where('branch_code', 'JKT001')->first();
        $branch2 = Branch::where('branch_code', 'SBY001')->first();
        
        $assignments = [
            [
                'device_id' => 1,
                'user_id' => $user1 ? $user1->user_id : 1,
                'branch_id' => $branch1 ? $branch1->branch_id : 1,
                'assigned_date' => '2024-01-01',
                'status' => 'Digunakan',
                'notes' => 'Initial assignment',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
            [
                'device_id' => 2,
                'user_id' => $user2 ? $user2->user_id : 2,
                'branch_id' => $branch2 ? $branch2->branch_id : 2,
                'assigned_date' => '2024-01-15',
                'status' => 'Digunakan',
                'notes' => 'Second assignment',
                'created_at' => Carbon::now(),
                'created_by' => 'ADMIN01',
            ],
        ];

        foreach ($assignments as $assignment) {
            DeviceAssignment::create($assignment);
        }

        // 10. Assignment Letters
        // Get the admin user ID dynamically
        $adminUser = User::where('pn', 'ADMIN01')->first();
        $adminUserId = $adminUser ? $adminUser->user_id : 1; // Fallback to first user
        
        $letters = [
            [
                'assignment_id' => 1,
                'letter_type' => 'assignment',
                'letter_number' => 'ASG/2024/001',
                'letter_date' => '2024-01-01',
                'approver_id' => $adminUserId,
                'created_at' => Carbon::now(),
                'created_by' => $adminUserId,
            ],
            [
                'assignment_id' => 2,
                'letter_type' => 'assignment',
                'letter_number' => 'ASG/2024/002',
                'letter_date' => '2024-01-15',
                'approver_id' => $adminUserId,
                'created_at' => Carbon::now(),
                'created_by' => $adminUserId,
            ],
        ];

        foreach ($letters as $letter) {
            AssignmentLetter::create($letter);
        }
    }
}
