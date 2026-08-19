<?php

namespace App;

use App\Models\User;
use App\Services\GameService;
use App\Services\ScoreCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    protected GameService $gameService;
    protected ScoreCalculator $scoreCalculator;

    public function __construct(GameService $gameService, ScoreCalculator $scoreCalculator)
    {
        $this->gameService = $gameService;
        $this->scoreCalculator = $scoreCalculator;
    }

    /**
     * Get user stats.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['games' => function($query) {
            $query->with(['players', 'rounds']);
        }]);

        $stats = [
            'user' => $user,
            'games_played' => $user->getGamesPlayedAttribute(),
            'total_score' => $user->getTotalScoreAttribute(),
            'avg_score' => $user->getAvgScoreAttribute(),
            'wins' => $user->getWinsAttribute(),
            'recent_games' => $user->games()->with(['creator', 'players'])->limit(10)->get()
        ];

        return response()->json($stats, 200);
    }

    /**
     * Get game leaderboard.
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $users = User::orderByRaw('JSON_EXTRACT(stats, "$.total_score") DESC')
            ->limit($limit)
            ->get();

        $leaderboard = [];
        $position = 1;

        foreach ($users as $user) {
            $leaderboard[] = [
                'position' => $position++,
                'user_id' => $user->id,
                'name' => $user->name,
                'total_score' => $user->getTotalScoreAttribute(),
                'games_played' => $user->getGamesPlayedAttribute(),
                'avg_score' => $user->getAvgScoreAttribute(),
                'wins' => $user->getWinsAttribute()
            ];
        }

        return response()->json([
            'leaderboard' => $leaderboard
        ], 200);
    }

    /**
     * Get game results.
     */
    public function gameResults(Request $request): JsonResponse
    {
        $validator = $request->all([
            'game_id' => ['required', 'integer', 'exists:games,id']
        ]);

        $gameId = $request->game_id;
        $game = $this->gameService->getUserGames($request->user())
            ->where('id', $gameId)
            ->first();

        if (!$game) {
            return response()->json([
                'message' => 'Game not found or you are not part of it'
            ], 404);
        }

        $results = $this->scoreCalculator->calculateFinalScores($game);

        return response()->json([
            'game' => $game,
            'results' => $results
        ], 200);
    }

    /**
     * Get round results.
     */
    public function roundResults(Request $request): JsonResponse
    {
        $validator = $request->all([
            'round_id' => ['required', 'integer', 'exists:rounds,id']
        ]);

        $roundId = $request->round_id;
        $round = Round::find($roundId);

        if (!$round) {
            return response()->json([
                'message' => 'Round not found'
            ], 404);
        }

        // Get all words with their validity
        $words = $round->words()->with(['user', 'category', 'votes'])->get();

        // Group by user
        $userResults = [];
        foreach ($words as $word) {
            $userId = $word->user_id;
            if (!isset($userResults[$userId])) {
                $userResults[$userId] = [
                    'user_id' => $userId,
                    'user_name' => $word->user->name,
                    'words' => []
                ];
            }

            $userResults[$userId]['words'][] = [
                'word' => $word->word,
                'category' => $word->category->name,
                'is_valid' => $word->is_valid,
                'votes' => $word->votes->count()
            ];
        }

        return response()->json([
            'round' => $round,
            'results' => array_values($userResults)
        ], 200);
    }
}
