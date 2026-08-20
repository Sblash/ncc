<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'creator_id' => User::factory(),
            'max_players' => 8,
            'current_round_id' => null,
            'status' => 'waiting',
            'settings' => [
                'letters' => ['A', 'B', 'C', 'D', 'E'],
                'categories' => ['Nomi', 'Cose', 'Citta'],
                'rounds' => 5,
                'round_duration' => 60,
            ],
        ];
    }
}
