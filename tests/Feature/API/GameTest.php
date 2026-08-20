<?php

namespace Tests\Feature\API;

use App\Models\Category;
use App\Models\Game;
use App\Models\PlayerGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_game(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/games', [
                'name' => 'Test Game',
                'max_players' => 8,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'creator_id',
                'max_players',
                'status',
                'creator',
                'players',
            ]);

        $this->assertDatabaseHas('games', ['name' => 'Test Game']);
    }

    public function test_user_can_list_games(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Game::factory()->create(['creator_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_user_can_join_game(): void
    {
        $creator = User::factory()->create();
        $player = User::factory()->create();
        
        $game = Game::factory()->create([
            'creator_id' => $creator->id,
            'max_players' => 8,
            'status' => 'waiting',
        ]);

        $token = $player->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/games/{$game->id}/join");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('player_game', [
            'game_id' => $game->id,
            'user_id' => $player->id,
            'status' => 'joined',
        ]);
    }

    public function test_user_can_leave_game(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        PlayerGame::factory()->create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/games/{$game->id}/leave");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('player_game', [
            'game_id' => $game->id,
            'user_id' => $user->id,
            'status' => 'left',
        ]);
    }

    public function test_creator_can_start_game(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create([
            'creator_id' => $user->id,
            'max_players' => 8,
            'status' => 'waiting',
        ]);

        // Add another player
        $player2 = User::factory()->create();
        PlayerGame::factory()->create([
            'game_id' => $game->id,
            'user_id' => $player2->id,
            'status' => 'joined',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/games/{$game->id}/start");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'status' => 'started',
        ]);
        
        $this->assertDatabaseCount('rounds', 1);
    }

    public function test_non_creator_cannot_start_game(): void
    {
        $creator = User::factory()->create();
        $player = User::factory()->create();
        
        $game = Game::factory()->create([
            'creator_id' => $creator->id,
            'max_players' => 8,
            'status' => 'waiting',
        ]);

        $token = $player->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/games/{$game->id}/start");

        $response->assertStatus(403);
    }
}
