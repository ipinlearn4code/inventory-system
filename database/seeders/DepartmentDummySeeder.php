<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentDummySeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['department_id' => 'IT01', 'name' => 'Information Technology'],
            ['department_id' => 'HR01', 'name' => 'Human Resources'],
            ['department_id' => 'FN01', 'name' => 'Finance'], // dipakai admin
            ['department_id' => 'FI01', 'name' => 'Finance Department'], // dipakai dummy
            ['department_id' => 'MK01', 'name' => 'Marketing'],
            ['department_id' => 'OP01', 'name' => 'Operations'],
            ['department_id' => 'OS01', 'name' => 'Operations Support'],
        ];

        foreach ($departments as $dept) {
            $existing = Department::firstOrNew(['department_id' => $dept['department_id']]);

            if (!$existing->exists) {
                $existing->name = $dept['name'];
                $existing->save();
            }
        }
    }
}
