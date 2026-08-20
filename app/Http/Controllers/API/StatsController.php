<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PlayerGame;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function show(Request $request, User $user): JsonResponse
    {
        // Get user's games
        $gamesPlayed = PlayerGame::where('user_id', $user->id)
            ->where('status', 'joined')
            ->count();

        // Get total score
        $totalScore = PlayerGame::where('user_id', $user->id)
            ->sum('score');

        // Get average score
        $avgScore = $gamesPlayed > 0 ? $totalScore / $gamesPlayed : 0;

        // Get games won (player with highest score in finished games)
        $gamesWon = 0;
        $finishedGames = PlayerGame::whereHas('game', function ($q) {
            $q->where('status', 'finished');
        })->where('user_id', $user->id)->get();

        foreach ($finishedGames as $playerGame) {
            $game = $playerGame->game;
            $maxScore = $game->players()->max('score');
            
            if ($playerGame->score === $maxScore && $maxScore > 0) {
                $gamesWon++;
            }
        }

        // Get win rate
        $winRate = $gamesPlayed > 0 ? ($gamesWon / $gamesPlayed) * 100 : 0;

        // Update user stats
        $user->stats = [
            'games_played' => $gamesPlayed,
            'total_score' => $totalScore,
            'avg_score' => round($avgScore, 2),
            'games_won' => $gamesWon,
            'win_rate' => round($winRate, 2),
        ];
        $user->save();

        return response()->json([
            'user' => $user,
            'stats' => $user->stats,
        ], 200);
    }

    public function leaderboard(Request $request): JsonResponse
    {
        $users = User::with(['players' => function ($q) {
            $q->where('status', 'joined');
        }])
            ->orderByDesc(function ($q) {
                $q->selectRaw('COALESCE((SELECT SUM(score) FROM player_game WHERE user_id = users.id), 0)');
            })
            ->limit(10)
            ->get();

        $leaderboard = [];
        foreach ($users as $user) {
            $totalScore = $user->players()->sum('score');
            $gamesPlayed = $user->players()->count();
            
            $leaderboard[] = [
                'user' => $user,
                'total_score' => $totalScore,
                'games_played' => $gamesPlayed,
                'avg_score' => $gamesPlayed > 0 ? round($totalScore / $gamesPlayed, 2) : 0,
            ];
        }

        return response()->json($leaderboard, 200);
    }
}
