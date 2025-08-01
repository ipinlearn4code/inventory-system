<?php

namespace Database\Factories;

use App\Models\Bribox;
use App\Models\BriboxesCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriboxFactory extends Factory
{
    protected $model = Bribox::class;

    public function definition(): array
    {
        return [
            'bribox_id' => $this->faker->unique()->randomElement(['LP', 'DT', 'MN', 'PR', 'NT']),
            'type' => $this->faker->randomElement(['Laptop', 'Desktop Computer', 'Monitor', 'Printer', 'Network Device']),
            'bribox_category_id' => BriboxesCategory::factory(),
        ];
    }
}
