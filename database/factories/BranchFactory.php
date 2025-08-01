<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\MainBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'branch_code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'unit_name' => $this->faker->company(),
            'main_branch_id' => MainBranch::factory(),
        ];
    }
}
