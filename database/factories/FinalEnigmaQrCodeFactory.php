<?php

namespace Database\Factories;

use App\Models\FinalEnigma;
use App\Models\FinalEnigmaQrCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinalEnigmaQrCodeFactory extends Factory
{
    protected $model = FinalEnigmaQrCode::class;

    public function definition(): array
    {
        return [
            'final_enigma_id' => FinalEnigma::factory(),
            'letter' => strtoupper(fake()->randomLetter()),
            'order' => fake()->numberBetween(1, 10),
            'qr_code_uuid' => fake()->uuid(),
            'hint_text' => fake()->sentence(),
        ];
    }
}
