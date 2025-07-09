<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['department_id' => 'IT01', 'name' => 'Information Technology'],
            ['department_id' => 'HR01', 'name' => 'Human Resources'],
            ['department_id' => 'FN01', 'name' => 'Finance'],
            ['department_id' => 'OP01', 'name' => 'Operations'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
