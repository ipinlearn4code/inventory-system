<?php

namespace Database\Factories;

use App\Models\BriboxesCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriboxesCategoryFactory extends Factory
{
    protected $model = BriboxesCategory::class;

    public function definition(): array
    {
        return [
            'category_name' => $this->faker->randomElement([
                'Laptop',
                'Desktop',
                'Monitor',
                'Printer',
                'Network'
            ]),
        ];
    }
}
