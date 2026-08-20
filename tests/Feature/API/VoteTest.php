<?php

namespace Tests\Feature\API;

use App\Models\Category;
use App\Models\Game;
use App\Models\PlayerGame;
use App\Models\Round;
use App\Models\User;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_on_word(): void
    {
        $user = User::factory()->create();
        $voter = User::factory()->create();
        $token = $voter->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'voting',
        ]);

        $word = Word::factory()->create([
            'round_id' => $round->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'word' => 'Albero',
        ]);

        PlayerGame::factory()->create([
            'game_id' => $game->id,
            'user_id' => $voter->id,
            'status' => 'joined',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/words/{$word->id}/vote", [
                'is_valid' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'word_id',
                'user_id',
                'is_valid',
            ]);

        $this->assertDatabaseHas('votes', [
            'word_id' => $word->id,
            'user_id' => $voter->id,
            'is_valid' => true,
        ]);
    }

    public function test_user_cannot_vote_on_own_word(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'voting',
        ]);

        $word = Word::factory()->create([
            'round_id' => $round->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'word' => 'Albero',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/words/{$word->id}/vote", [
                'is_valid' => true,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_vote_twice_on_same_word(): void
    {
        $user = User::factory()->create();
        $voter = User::factory()->create();
        $token = $voter->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'voting',
        ]);

        $word = Word::factory()->create([
            'round_id' => $round->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'word' => 'Albero',
        ]);

        // First vote
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/words/{$word->id}/vote", [
                'is_valid' => true,
            ]);

        // Second vote
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/words/{$word->id}/vote", [
                'is_valid' => false,
            ]);

        $response->assertStatus(400);
    }

    public function test_user_cannot_vote_when_round_not_in_voting_phase(): void
    {
        $user = User::factory()->create();
        $voter = User::factory()->create();
        $token = $voter->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'active', // Not in voting phase
        ]);

        $word = Word::factory()->create([
            'round_id' => $round->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'word' => 'Albero',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/words/{$word->id}/vote", [
                'is_valid' => true,
            ]);

        $response->assertStatus(400);
    }
}
