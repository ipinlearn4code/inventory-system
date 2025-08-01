<?php

namespace Database\Factories;

use App\Models\MainBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MainBranchFactory extends Factory
{
    protected $model = MainBranch::class;

    public function definition(): array
    {
        return [
            'main_branch_code' => $this->faker->unique()->regexify('[A-Z]{4}'),
            'main_branch_name' => $this->faker->city(),
        ];
    }
}
