<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\PlayerGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Game::with(['creator', 'currentRound', 'players.user'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('my_games')) {
            $query->where('creator_id', $request->user()->id)
                ->orWhereHas('players', function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                });
        }

        $games = $query->get();

        return response()->json($games, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'max_players' => 'required|integer|min:2|max:20',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = Game::create([
            'name' => $request->name,
            'creator_id' => $request->user()->id,
            'max_players' => $request->max_players,
            'status' => 'waiting',
            'settings' => $request->settings ?? [
                'letters' => ['A', 'B', 'C', 'D', 'E'],
                'categories' => ['Nomi', 'Cose', 'Citta'],
                'rounds' => 5,
                'round_duration' => 60, // seconds
            ],
        ]);

        // Auto-join creator to the game
        PlayerGame::create([
            'game_id' => $game->id,
            'user_id' => $request->user()->id,
            'score' => 0,
            'status' => 'joined',
        ]);

        return response()->json($game->load(['creator', 'players']), 201);
    }

    public function show(Request $request, Game $game): JsonResponse
    {
        $game->load(['creator', 'currentRound', 'rounds' => function ($q) {
            $q->orderBy('created_at');
        }, 'players.user']);

        return response()->json($game, 200);
    }

    public function join(Request $request, Game $game): JsonResponse
    {
        // Check if user is already in the game
        $existing = PlayerGame::where('game_id', $game->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already joined this game',
            ], 400);
        }

        // Check if game is full
        $playerCount = PlayerGame::where('game_id', $game->id)
            ->where('status', 'joined')
            ->count();

        if ($playerCount >= $game->max_players) {
            return response()->json([
                'message' => 'Game is full',
            ], 400);
        }

        // Check if game is not started yet
        if ($game->status !== 'waiting') {
            return response()->json([
                'message' => 'Cannot join a game that has already started',
            ], 400);
        }

        PlayerGame::create([
            'game_id' => $game->id,
            'user_id' => $request->user()->id,
            'score' => 0,
            'status' => 'joined',
        ]);

        return response()->json([
            'message' => 'Successfully joined the game',
            'game' => $game->load(['players.user']),
        ], 200);
    }

    public function leave(Request $request, Game $game): JsonResponse
    {
        $player = PlayerGame::where('game_id', $game->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$player) {
            return response()->json([
                'message' => 'Not in this game',
            ], 400);
        }

        $player->update(['status' => 'left']);

        return response()->json([
            'message' => 'Successfully left the game',
        ], 200);
    }

    public function start(Request $request, Game $game): JsonResponse
    {
        // Only creator can start the game
        if ($game->creator_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the game creator can start the game',
            ], 403);
        }

        // Check minimum players
        $playerCount = PlayerGame::where('game_id', $game->id)
            ->where('status', 'joined')
            ->count();

        if ($playerCount < 2) {
            return response()->json([
                'message' => 'Need at least 2 players to start the game',
            ], 400);
        }

        // Update game status
        $game->update(['status' => 'started']);

        // Create first round
        $settings = $game->settings ?? [
            'letters' => ['A', 'B', 'C', 'D', 'E'],
            'categories' => ['Nomi', 'Cose', 'Citta'],
            'rounds' => 5,
            'round_duration' => 60,
        ];

        $letter = $settings['letters'][0] ?? 'A';
        $category = $settings['categories'][0] ?? 'Nomi';
        $duration = $settings['round_duration'] ?? 60;

        $round = $game->rounds()->create([
            'letter' => $letter,
            'category' => $category,
            'starts_at' => now(),
            'ends_at' => now()->addSeconds($duration),
            'status' => 'active',
        ]);

        $game->update(['current_round_id' => $round->id]);

        return response()->json([
            'message' => 'Game started successfully',
            'game' => $game->load(['currentRound', 'rounds', 'players.user']),
        ], 200);
    }
}
