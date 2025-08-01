<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Bribox;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'brand' => $this->faker->randomElement(['LENOVO', 'HP', 'DELL', 'ASUS', 'ACER']),
            'brand_name' => $this->faker->randomElement(['ThinkPad', 'EliteBook', 'Latitude', 'ZenBook', 'Aspire']),
            'serial_number' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'asset_code' => $this->faker->unique()->regexify('AST[0-9]{6}'),
            'bribox_id' => Bribox::factory(),
            'condition' => $this->faker->randomElement(['Baik', 'Rusak', 'Perlu Pengecekan']),
            'spec1' => $this->faker->optional()->randomElement(['Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5']),
            'spec2' => $this->faker->optional()->randomElement(['8GB RAM', '16GB RAM', '32GB RAM']),
            'spec3' => $this->faker->optional()->randomElement(['256GB SSD', '512GB SSD', '1TB SSD']),
            'spec4' => $this->faker->optional()->text(50),
            'spec5' => $this->faker->optional()->text(50),
            'dev_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'),
            'created_at' => now(),
            'created_by' => $this->faker->regexify('[A-Z0-9]{8}'),
            'updated_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'updated_by' => $this->faker->optional()->regexify('[A-Z0-9]{8}'),
        ];
    }
}
