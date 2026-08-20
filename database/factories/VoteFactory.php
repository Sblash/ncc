<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vote;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'word_id' => Word::factory(),
            'user_id' => User::factory(),
            'is_valid' => $this->faker->boolean,
        ];
    }
}
