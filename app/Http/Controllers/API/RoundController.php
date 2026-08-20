<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function index(Request $request, Game $game): JsonResponse
    {
        $rounds = $game->rounds()
            ->with(['words' => function ($q) {
                $q->with(['user', 'category', 'votes']);
            }])
            ->orderBy('created_at')
            ->get();

        return response()->json($rounds, 200);
    }

    public function show(Request $request, Round $round): JsonResponse
    {
        $round->load(['game', 'words' => function ($q) {
            $q->with(['user', 'category', 'votes.user']);
        }]);

        return response()->json($round, 200);
    }

    public function nextRound(Request $request, Game $game): JsonResponse
    {
        // Get current round
        $currentRound = $game->currentRound;

        if (!$currentRound) {
            return response()->json([
                'message' => 'No current round found',
            ], 400);
        }

        // Check if current round is completed
        if ($currentRound->status !== 'completed') {
            return response()->json([
                'message' => 'Current round is not completed yet',
            ], 400);
        }

        $settings = $game->settings ?? [
            'letters' => ['A', 'B', 'C', 'D', 'E'],
            'categories' => ['Nomi', 'Cose', 'Citta'],
            'rounds' => 5,
            'round_duration' => 60,
        ];

        $roundsCount = $game->rounds()->count();
        $totalRounds = count($settings['letters'] ?? ['A']);

        if ($roundsCount >= $totalRounds) {
            // All rounds completed, finish the game
            $game->update(['status' => 'finished']);

            return response()->json([
                'message' => 'All rounds completed, game finished',
                'game' => $game->load(['rounds', 'players.user']),
            ], 200);
        }

        $nextLetter = $settings['letters'][$roundsCount] ?? 'A';
        $nextCategory = $settings['categories'][$roundsCount % count($settings['categories'])] ?? 'Nomi';
        $duration = $settings['round_duration'] ?? 60;

        $round = $game->rounds()->create([
            'letter' => $nextLetter,
            'category' => $nextCategory,
            'starts_at' => now(),
            'ends_at' => now()->addSeconds($duration),
            'status' => 'active',
        ]);

        $game->update(['current_round_id' => $round->id]);

        return response()->json([
            'message' => 'Next round started',
            'round' => $round,
            'game' => $game->load(['currentRound', 'rounds']),
        ], 200);
    }

    public function endRound(Request $request, Round $round): JsonResponse
    {
        // Only for testing/administrative purposes
        // In production, rounds should end automatically via scheduler
        $round->update([
            'status' => 'voting',
            'ends_at' => now(),
        ]);

        return response()->json([
            'message' => 'Round ended manually',
            'round' => $round,
        ], 200);
    }
}
