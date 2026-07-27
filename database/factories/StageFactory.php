<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'name' => 'Etapa ' . fake()->word(),
            'stage_type' => 'charada',
            'order' => fake()->numberBetween(1, 10),
            'narrative_text' => fake()->paragraph(),
            'correct_answer' => (string) fake()->randomNumber(5, true),
            'secret_number' => (string) fake()->randomNumber(4, true),
            'qr_code_uuid' => fake()->uuid(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'radius' => 30,
            'score' => 50,
            'penalty' => 0,
        ];
    }
}
