<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignmentLetter;
use App\Models\User;
use Carbon\Carbon;

class AssignmentLetterSeeder extends Seeder
{
    public function run(): void
    {
        // Get the admin user ID dynamically
        $adminUserId = User::where('user_id', 'ADMIN01')->first();
        $adminUserPn = $adminUserId ? $adminUserId->pn : 'SYS_Init'; // Fallback to first user

        $letters = [
            [
                'assignment_id' => 1,
                'letter_type' => 'assignment',
                'letter_number' => 'ASG/2024/001',
                'letter_date' => '2024-01-01',
                'approver_id' => $adminUserId ?? 4, // Fallback to 'SYS_Init' if admin user not found
                'created_at' => Carbon::now(),
                'created_by' => $adminUserPn,
            ],
            [
                'assignment_id' => 2,
                'letter_type' => 'assignment',
                'letter_number' => 'ASG/2024/002',
                'letter_date' => '2024-01-15',
                'approver_id' => $adminUserId ?? 4,
                'created_at' => Carbon::now(),
                'created_by' => $adminUserPn,
            ],
        ];

        foreach ($letters as $letter) {
            AssignmentLetter::create($letter);
        }
    }
}
