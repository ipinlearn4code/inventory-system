<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['branch_code' => 'JKT001', 'unit_name' => 'Jakarta Central', 'main_branch_id' => 1],
            ['branch_code' => 'JKT002', 'unit_name' => 'Jakarta South', 'main_branch_id' => 1],
            ['branch_code' => 'SBY001', 'unit_name' => 'Surabaya Main', 'main_branch_id' => 2],
            ['branch_code' => 'BDG001', 'unit_name' => 'Bandung Central', 'main_branch_id' => 3],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
