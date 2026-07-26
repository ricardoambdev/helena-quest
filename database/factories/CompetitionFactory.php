<?php

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Gincana',
            'year' => (string) fake()->year(),
            'date' => fake()->date(),
            'start_time' => fake()->dateTime(),
            'end_time' => fake()->dateTime(),
            'status' => 'ongoing',
        ];
    }
}
