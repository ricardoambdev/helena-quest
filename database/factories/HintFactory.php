<?php

namespace Database\Factories;

use App\Models\Hint;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class HintFactory extends Factory
{
    protected $model = Hint::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'hint_text' => fake()->sentence(),
            'price' => fake()->numberBetween(10, 50),
            'order' => 1,
        ];
    }
}
