<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'department_id' => $this->faker->unique()->regexify('[A-Z]{4}'),
            'name' => $this->faker->randomElement([
                'IT Department',
                'Finance Department', 
                'HR Department',
                'Operations Department',
                'Marketing Department'
            ]),
        ];
    }
}
