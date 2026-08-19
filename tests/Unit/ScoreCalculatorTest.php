<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Word;
use App\Models\Round;
use App\Models\Game;
use App\Models\User;
use App\Models\Category;
use App\Models\PlayerGame;
use App\Services\ScoreCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoreCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $scoreCalculator;
    protected $user1;
    protected $user2;
    protected $game;
    protected $round;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoreCalculator = new ScoreCalculator();

        // Create users
        $this->user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'stats' => []
        ]);

        $this->user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'stats' => []
        ]);

        // Create category
        $this->category = Category::create(['name' => 'Nomi']);

        // Create game
        $this->game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user1->id,
            'max_players' => 2,
            'status' => 'started',
            'settings' => []
        ]);

        // Create players
        PlayerGame::create([
            'game_id' => $this->game->id,
            'user_id' => $this->user1->id,
            'score' => 0,
            'status' => 'joined'
        ]);

        PlayerGame::create([
            'game_id' => $this->game->id,
            'user_id' => $this->user2->id,
            'score' => 0,
            'status' => 'joined'
        ]);

        // Create round
        $this->round = Round::create([
            'game_id' => $this->game->id,
            'letter' => 'A',
            'category_id' => $this->category->id,
            'round_number' => 1,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(1)
        ]);

        $this->game->update(['current_round_id' => $this->round->id]);
    }

    /**
     * Test unique word gets 10 points.
     */
    public function test_unique_word_gets_10_points(): void
    {
        // Create unique word
        $word = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'word' => 'Alberto',
            'is_valid' => true
        ]);

        $words = [$word->toArray()];
        $score = $this->scoreCalculator->calculateWordScore($word, $words);

        $this->assertEquals(10, $score);
    }

    /**
     * Test duplicate word gets 5 points.
     */
    public function test_duplicate_word_gets_5_points(): void
    {
        // Create two words with the same value
        $word1 = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'word' => 'Alberto',
            'is_valid' => true
        ]);

        $word2 = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user2->id,
            'category_id' => $this->category->id,
            'word' => 'Alberto',
            'is_valid' => true
        ]);

        $words = [$word1->toArray(), $word2->toArray()];
        $score = $this->scoreCalculator->calculateWordScore($word1, $words);

        $this->assertEquals(5, $score);
    }

    /**
     * Test invalid word gets -15 points.
     */
    public function test_invalid_word_gets_minus_15_points(): void
    {
        // Create invalid word (doesn't start with correct letter)
        $word = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'word' => 'Zebra', // Doesn't start with 'A'
            'is_valid' => null
        ]);

        $words = [$word->toArray()];
        $score = $this->scoreCalculator->calculateWordScore($word, $words);

        $this->assertEquals(-15, $score);
    }

    /**
     * Test word validation.
     */
    public function test_word_validation(): void
    {
        $word = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'word' => 'Alberto',
            'is_valid' => null
        ]);

        $this->assertTrue($word->isWordValid());

        // Test word that doesn't start with correct letter
        $word->word = 'Zebra';
        $this->assertFalse($word->isWordValid());

        // Test empty word
        $word->word = '';
        $this->assertFalse($word->isWordValid());

        // Test word with numbers
        $word->word = 'A123';
        $this->assertFalse($word->isWordValid());

        // Test word that's too short
        $word->word = 'A';
        $this->assertFalse($word->isWordValid());
    }

    /**
     * Test calculate round scores.
     */
    public function test_calculate_round_scores(): void
    {
        // Create words for both users
        $word1 = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'word' => 'Alberto',
            'is_valid' => true
        ]);

        $word2 = Word::create([
            'round_id' => $this->round->id,
            'user_id' => $this->user2->id,
            'category_id' => $this->category->id,
            'word' => 'Anna',
            'is_valid' => true
        ]);

        // Calculate scores
        $this->scoreCalculator->calculateRoundScores($this->round);

        // Refresh players
        $player1 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user1->id)
            ->first();

        $player2 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user2->id)
            ->first();

        // Both should have 10 points for unique words
        $this->assertEquals(10, $player1->score);
        $this->assertEquals(10, $player2->score);
    }

    /**
     * Test calculate final scores.
     */
    public function test_calculate_final_scores(): void
    {
        // Update player scores manually
        $player1 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user1->id)
            ->first();
        $player1->update(['score' => 25]);

        $player2 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user2->id)
            ->first();
        $player2->update(['score' => 20]);

        $results = $this->scoreCalculator->calculateFinalScores($this->game);

        $this->assertCount(2, $results);
        $this->assertEquals(1, $results[0]['position']);
        $this->assertEquals($this->user1->id, $results[0]['user_id']);
        $this->assertEquals(25, $results[0]['score']);

        $this->assertEquals(2, $results[1]['position']);
        $this->assertEquals($this->user2->id, $results[1]['user_id']);
        $this->assertEquals(20, $results[1]['score']);
    }

    /**
     * Test get winner.
     */
    public function test_get_winner(): void
    {
        // Update player scores manually
        $player1 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user1->id)
            ->first();
        $player1->update(['score' => 30]);

        $player2 = PlayerGame::where('game_id', $this->game->id)
            ->where('user_id', $this->user2->id)
            ->first();
        $player2->update(['score' => 20]);

        $winner = $this->scoreCalculator->getWinner($this->game);

        $this->assertEquals($player1->id, $winner->id);
        $this->assertEquals($this->user1->id, $winner->user_id);
    }
}
