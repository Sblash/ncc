<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Round;
use App\Models\User;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Word>
 */
class WordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'word' => $this->faker->word,
            'is_valid' => null,
        ];
    }
}
