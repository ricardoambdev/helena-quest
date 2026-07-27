<?php

namespace Database\Factories;

use App\Models\BonusOnus;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusOnusFactory extends Factory
{
    protected $model = BonusOnus::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'type' => fake()->randomElement(['bonus', 'onus']),
            'qr_code_uuid' => fake()->uuid(),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'points' => fake()->numberBetween(10, 100),
        ];
    }

    public function bonus(): static
    {
        return $this->state(fn () => ['type' => 'bonus', 'points' => 50]);
    }

    public function onus(): static
    {
        return $this->state(fn () => ['type' => 'onus', 'points' => -30]);
    }
}
