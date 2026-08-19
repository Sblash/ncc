<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Get all games with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Game::with(['creator', 'players', 'currentRound']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by my games
        if ($request->has('my_games') && $request->my_games) {
            $query->whereHas('players', function($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });
        }

        $games = $query->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'games' => $games
        ], 200);
    }

    /**
     * Create a new game.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'max_players' => ['integer', 'min:2', 'max:20'],
            'letters' => ['array', 'min:1'],
            'categories' => ['array', 'min:1'],
            'rounds' => ['integer', 'min:1', 'max:20'],
            'round_duration' => ['integer', 'min:30', 'max:300']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $game = $this->gameService->createGame($request->user(), $request->all());

        return response()->json([
            'game' => $game->load(['creator', 'players', 'currentRound']),
            'message' => 'Game created successfully'
        ], 201);
    }

    /**
     * Get game details.
     */
    public function show(Game $game): JsonResponse
    {
        $game->load([
            'creator',
            'players.user',
            'currentRound',
            'rounds' => function($query) {
                $query->with(['category', 'words' => function($q) {
                    $q->with(['user', 'category', 'votes']);
                }]);
            }
        ]);

        return response()->json([
            'game' => $game
        ], 200);
    }

    /**
     * Join a game.
     */
    public function join(Game $game, Request $request): JsonResponse
    {
        $success = $this->gameService->joinGame($request->user(), $game);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot join this game. It may be full, already started, or you are already in it.'
            ], 400);
        }

        return response()->json([
            'message' => 'Successfully joined the game',
            'game' => $game->load(['creator', 'players', 'currentRound'])
        ], 200);
    }

    /**
     * Leave a game.
     */
    public function leave(Game $game, Request $request): JsonResponse
    {
        $success = $this->gameService->leaveGame($request->user(), $game);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot leave this game. It may have already started or you are not in it.'
            ], 400);
        }

        return response()->json([
            'message' => 'Successfully left the game'
        ], 200);
    }

    /**
     * Start a game.
     */
    public function start(Game $game, Request $request): JsonResponse
    {
        $success = $this->gameService->startGame($request->user(), $game);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot start this game. You may not be the creator or there are not enough players.'
            ], 400);
        }

        return response()->json([
            'message' => 'Game started successfully',
            'game' => $game->load(['creator', 'players', 'currentRound'])
        ], 200);
    }

    /**
     * Get rounds for a game.
     */
    public function rounds(Game $game): JsonResponse
    {
        $rounds = $game->rounds()->with([
            'category',
            'words' => function($query) {
                $query->with(['user', 'category', 'votes']);
            }
        ])->orderBy('round_number')->get();

        return response()->json([
            'rounds' => $rounds
        ], 200);
    }
}
