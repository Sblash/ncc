<?php

namespace Tests\Feature\API;

use App\Models\Category;
use App\Models\Game;
use App\Models\PlayerGame;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_word(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create([
            'creator_id' => $user->id,
            'status' => 'started',
        ]);

        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(10),
        ]);

        $game->update(['current_round_id' => $round->id]);

        PlayerGame::factory()->create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/rounds/{$round->id}/words", [
                'word' => 'Albero',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'round_id',
                'user_id',
                'category_id',
                'word',
            ]);

        $this->assertDatabaseHas('words', [
            'round_id' => $round->id,
            'user_id' => $user->id,
            'word' => 'Albero',
        ]);
    }

    public function test_word_must_start_with_round_letter(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'B',
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/rounds/{$round->id}/words", [
                'word' => 'Albero', // Starts with A, not B
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_word_must_be_valid_format(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/rounds/{$round->id}/words", [
                'word' => '123Albero', // Invalid format
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_submit_duplicate_word_for_category(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        
        $game = Game::factory()->create(['creator_id' => $user->id]);
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'letter' => 'A',
            'status' => 'active',
        ]);

        // First submission
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/rounds/{$round->id}/words", [
                'word' => 'Albero',
                'category_id' => $category->id,
            ]);

        // Second submission for same category
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/rounds/{$round->id}/words", [
                'word' => 'Ape',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(400);
    }
}
