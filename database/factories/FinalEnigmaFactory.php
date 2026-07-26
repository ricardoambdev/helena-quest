<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\FinalEnigma;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinalEnigmaFactory extends Factory
{
    protected $model = FinalEnigma::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'word' => strtoupper(fake()->word()),
            'description' => fake()->sentence(),
            'max_attempts' => 3,
            'cooldown_minutes' => 5,
            'final_score' => 500,
        ];
    }
}
