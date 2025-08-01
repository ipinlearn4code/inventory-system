<?php

namespace Database\Factories;

use App\Models\AssignmentLetter;
use App\Models\DeviceAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentLetterFactory extends Factory
{
    protected $model = AssignmentLetter::class;

    public function definition(): array
    {
        return [
            'assignment_id' => DeviceAssignment::factory(),
            'letter_type' => $this->faker->randomElement(['assignment', 'return']),
            'letter_number' => $this->faker->unique()->regexify('ASG/[0-9]{4}/[0-9]{3}'),
            'letter_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'approver_id' => User::factory(),
            'file_path' => null, // Will be set by tests if needed
            'created_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
