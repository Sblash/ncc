<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Game;
use App\Models\Category;
use App\Models\PlayerGame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'stats' => []
        ]);

        $this->token = $this->user->createToken('test_token')->plainTextToken;

        // Create default categories
        Category::create(['name' => 'Nomi']);
        Category::create(['name' => 'Cose']);
        Category::create(['name' => 'Città']);
    }

    /**
     * Test create game.
     */
    public function test_create_game(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/games', [
                'name' => 'Test Game',
                'max_players' => 5,
                'rounds' => 3,
                'round_duration' => 60,
                'letters' => ['A', 'B', 'C'],
                'categories' => [1, 2, 3]
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'game' => [
                    'id',
                    'name',
                    'creator_id',
                    'max_players',
                    'status',
                    'settings'
                ]
            ]);

        $this->assertDatabaseHas('games', [
            'name' => 'Test Game',
            'creator_id' => $this->user->id
        ]);
    }

    /**
     * Test get all games.
     */
    public function test_get_all_games(): void
    {
        Game::create([
            'name' => 'Game 1',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => []
        ]);

        Game::create([
            'name' => 'Game 2',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'started',
            'settings' => []
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'games' => [
                    '*' => [
                        'id',
                        'name',
                        'creator_id',
                        'status'
                    ]
                ]
            ]);
    }

    /**
     * Test get single game.
     */
    public function test_get_single_game(): void
    {
        $game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => []
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->getJson('/api/games/' . $game->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'game' => [
                    'id',
                    'name',
                    'creator',
                    'players',
                    'current_round'
                ]
            ]);
    }

    /**
     * Test join game.
     */
    public function test_join_game(): void
    {
        $game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => []
        ]);

        // Create another user to join
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
            'stats' => []
        ]);

        $otherToken = $otherUser->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $otherToken])
            ->postJson('/api/games/' . $game->id . '/join');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully joined the game']);

        $this->assertDatabaseHas('player_game', [
            'game_id' => $game->id,
            'user_id' => $otherUser->id,
            'status' => 'joined'
        ]);
    }

    /**
     * Test cannot join full game.
     */
    public function test_cannot_join_full_game(): void
    {
        $game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user->id,
            'max_players' => 2,
            'status' => 'waiting',
            'settings' => []
        ]);

        // Add first player (creator)
        PlayerGame::create([
            'game_id' => $game->id,
            'user_id' => $this->user->id,
            'score' => 0,
            'status' => 'joined'
        ]);

        // Add second player
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
            'stats' => []
        ]);

        PlayerGame::create([
            'game_id' => $game->id,
            'user_id' => $otherUser->id,
            'score' => 0,
            'status' => 'joined'
        ]);

        // Try to add third user
        $thirdUser = User::create([
            'name' => 'Third User',
            'email' => 'third@example.com',
            'password' => Hash::make('password123'),
            'stats' => []
        ]);

        $thirdToken = $thirdUser->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $thirdToken])
            ->postJson('/api/games/' . $game->id . '/join');

        $response->assertStatus(400);
    }

    /**
     * Test start game.
     */
    public function test_start_game(): void
    {
        $game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => [
                'rounds' => 3,
                'round_duration' => 60,
                'letters' => ['A', 'B', 'C'],
                'categories' => [1, 2, 3]
            ]
        ]);

        // Add second player
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
            'stats' => []
        ]);

        PlayerGame::create([
            'game_id' => $game->id,
            'user_id' => $otherUser->id,
            'score' => 0,
            'status' => 'joined'
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/games/' . $game->id . '/start');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Game started successfully']);

        $game->refresh();
        $this->assertEquals('started', $game->status);
        $this->assertNotNull($game->current_round_id);
    }

    /**
     * Test cannot start game with less than 2 players.
     */
    public function test_cannot_start_game_with_less_than_2_players(): void
    {
        $game = Game::create([
            'name' => 'Test Game',
            'creator_id' => $this->user->id,
            'max_players' => 5,
            'status' => 'waiting',
            'settings' => []
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/games/' . $game->id . '/start');

        $response->assertStatus(400);
    }
}
