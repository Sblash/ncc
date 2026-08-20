<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Round>
 */
class RoundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'letter' => $this->faker->randomLetter,
            'category' => $this->faker->randomElement(['Nomi', 'Cose', 'Citta']),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(10),
            'status' => 'active',
        ];
    }
}
