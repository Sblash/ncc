<?php

namespace App;

use App\Models\Round;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Get round details.
     */
    public function show(Round $round): JsonResponse
    {
        $round->load([
            'game' => function($query) {
                $query->with(['creator', 'players']);
            },
            'category',
            'words' => function($query) {
                $query->with(['user', 'category', 'votes']);
            }
        ]);

        return response()->json([
            'round' => $round
        ], 200);
    }

    /**
     * Start a round.
     */
    public function start(Round $round, Request $request): JsonResponse
    {
        // Check if user is the game creator
        if (!$round->game->isCreator($request->user())) {
            return response()->json([
                'message' => 'Only the game creator can start rounds'
            ], 403);
        }

        $success = $this->gameService->startRound($round);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot start this round'
            ], 400);
        }

        return response()->json([
            'message' => 'Round started successfully',
            'round' => $round->fresh()
        ], 200);
    }

    /**
     * End a round (start voting phase).
     */
    public function end(Round $round, Request $request): JsonResponse
    {
        // Check if user is the game creator
        if (!$round->game->isCreator($request->user())) {
            return response()->json([
                'message' => 'Only the game creator can end rounds'
            ], 403);
        }

        $success = $this->gameService->endRound($round);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot end this round'
            ], 400);
        }

        return response()->json([
            'message' => 'Round ended successfully, voting phase started',
            'round' => $round->fresh()
        ], 200);
    }

    /**
     * Complete a round (end voting, calculate scores, move to next).
     */
    public function complete(Round $round, Request $request): JsonResponse
    {
        // Check if user is the game creator
        if (!$round->game->isCreator($request->user())) {
            return response()->json([
                'message' => 'Only the game creator can complete rounds'
            ], 403);
        }

        $success = $this->gameService->completeRound($round);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot complete this round'
            ], 400);
        }

        return response()->json([
            'message' => 'Round completed successfully',
            'round' => $round->fresh(),
            'game' => $round->game->fresh(['currentRound'])
        ], 200);
    }

    /**
     * Get the current round for a game.
     */
    public function current(Request $request): JsonResponse
    {
        $gameId = $request->input('game_id');
        
        if (!$gameId) {
            return response()->json([
                'message' => 'Game ID is required'
            ], 400);
        }

        $round = Round::where('game_id', $gameId)
            ->where('status', '!=', 'completed')
            ->orderBy('round_number', 'DESC')
            ->first();

        if (!$round) {
            return response()->json([
                'message' => 'No active round found for this game'
            ], 404);
        }

        $round->load([
            'game',
            'category',
            'words' => function($query) {
                $query->with(['user', 'category', 'votes']);
            }
        ]);

        return response()->json([
            'round' => $round
        ], 200);
    }
}
