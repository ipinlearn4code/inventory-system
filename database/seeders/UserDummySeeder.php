<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserDummySeeder extends Seeder
{
    public function run(): void
    {
        $positions = ['Staff', 'Officer', 'Specialist', 'Engineer', 'Supervisor'];
        $departments = ['IT01', 'HR01', 'FN01', 'FI01', 'MK01', 'OP01', 'OS01'];

        $users = [];

        for ($i = 1; $i <= 50; $i++) {
            $users[] = [
                'pn' => 'USR' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'User Dummy ' . $i,
                'department_id' => $departments[array_rand($departments)],
                'branch_id' => rand(1, 4),
                'position' => $positions[array_rand($positions)],
            ];
        }

        User::insert($users);
    }
}
