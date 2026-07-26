<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Proof;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProofFactory extends Factory
{
    protected $model = Proof::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'name' => fake()->word() . ' Proof',
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 10),
            'color_hex' => fake()->hexColor(),
        ];
    }
}
