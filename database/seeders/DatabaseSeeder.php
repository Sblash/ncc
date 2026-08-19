<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Game;
use App\Models\Round;
use App\Models\Word;
use App\Models\Vote;
use App\Models\PlayerGame;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default categories
        $categories = [
            'Nomi',
            'Cose',
            'Città',
            'Animali',
            'Piante',
            'Cibi',
            'Mestieri',
            'Film',
            'Libri',
            'Personaggi famosi'
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }

        // Create test users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => 'Utente ' . $i,
                'email' => 'utente' . $i . '@esempio.it',
                'password' => Hash::make('password'),
                'stats' => [
                    'games_played' => 0,
                    'total_score' => 0,
                    'avg_score' => 0,
                    'wins' => 0
                ]
            ]);
            $users[] = $user;
        }

        // Create a test game
        $game = Game::create([
            'name' => 'Partita di Test',
            'creator_id' => $users[0]->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => [
                'letters' => ['A', 'B', 'C', 'D', 'E'],
                'categories' => [1, 2, 3],
                'rounds' => 3,
                'round_duration' => 60
            ]
        ]);

        // Add players to the game
        foreach ($users as $user) {
            PlayerGame::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'score' => 0,
                'status' => 'joined'
            ]);
        }

        // Create a round
        $round = Round::create([
            'game_id' => $game->id,
            'letter' => 'A',
            'category_id' => 1,
            'round_number' => 1,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(1)
        ]);

        // Update game current round
        $game->update(['current_round_id' => $round->id, 'status' => 'started']);

        // Create some words
        $words = [
            ['user_id' => $users[0]->id, 'category_id' => 1, 'word' => 'Alberto'],
            ['user_id' => $users[0]->id, 'category_id' => 2, 'word' => 'Albero'],
            ['user_id' => $users[0]->id, 'category_id' => 3, 'word' => 'Aosta'],
            ['user_id' => $users[1]->id, 'category_id' => 1, 'word' => 'Anna'],
            ['user_id' => $users[1]->id, 'category_id' => 2, 'word' => 'Aereo'],
            ['user_id' => $users[1]->id, 'category_id' => 3, 'word' => 'Ancona'],
        ];

        foreach ($words as $wordData) {
            Word::create([
                'round_id' => $round->id,
                'user_id' => $wordData['user_id'],
                'category_id' => $wordData['category_id'],
                'word' => $wordData['word'],
                'is_valid' => null
            ]);
        }

        // Create some votes
        $word = Word::where('round_id', $round->id)->where('user_id', $users[0]->id)->first();
        if ($word) {
            Vote::create([
                'word_id' => $word->id,
                'user_id' => $users[1]->id,
                'is_valid' => true
            ]);
        }

        $this->command->info('Database seeded successfully!');
    }
}
