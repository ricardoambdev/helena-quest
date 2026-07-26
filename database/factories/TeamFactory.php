<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'name' => fake()->unique()->colorName() . ' Team',
            'username' => fake()->unique()->userName(),
            'password_hash' => Hash::make('secret'),
            'color_hex' => fake()->hexColor(),
            'status' => 'active',
        ];
    }
}
