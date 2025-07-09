<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MainBranch;

class MainBranchSeeder extends Seeder
{
    public function run(): void
    {
        $mainBranches = [
            ['main_branch_code' => 'HQ01', 'main_branch_name' => 'Head Office Jakarta'],
            ['main_branch_code' => 'SB01', 'main_branch_name' => 'Regional Surabaya'],
            ['main_branch_code' => 'BD01', 'main_branch_name' => 'Regional Bandung'],
        ];

        foreach ($mainBranches as $mainBranch) {
            MainBranch::create($mainBranch);
        }
    }
}
