<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceAssignment;
use App\Models\User;
use App\Models\Branch;
use Carbon\Carbon;

class DeviceAssignmentSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
