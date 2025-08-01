<?php

namespace Database\Factories;

use App\Models\DeviceAssignment;
use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceAssignmentFactory extends Factory
{
    protected $model = DeviceAssignment::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'assigned_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'created_by' => $this->faker->regexify('[A-Z0-9]{8}'),
            'updated_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'updated_by' => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
        ];
    }
}
