<?php

namespace App\Services;

use App\Models\Word;
use App\Models\PlayerGame;
use App\Models\Round;

class ScoreCalculator
{
    /**
     * Calculate scores for a completed round.
     */
    public function calculateRoundScores(Round $round): void
    {
        // First, determine validity for all words based on votes
        $this->determineWordValidity($round);

        // Get all words for this round
        $words = $round->words()->with(['user', 'category'])->get();

        // Group words by user and category
        $userWords = [];
        foreach ($words as $word) {
            $userWords[$word->user_id][$word->category_id] = $word;
        }

        // Calculate scores for each user
        foreach ($userWords as $userId => $categoryWords) {
            $playerGame = PlayerGame::where('game_id', $round->game_id)
                ->where('user_id', $userId)
                ->first();

            if (!$playerGame) {
                continue;
            }

            $score = 0;

            foreach ($categoryWords as $categoryId => $word) {
                $score += $this->calculateWordScore($word, $categoryWords);
            }

            $playerGame->addScore($score);
        }
    }

    /**
     * Determine validity for all words in a round based on votes.
     */
    private function determineWordValidity(Round $round): void
    {
        $words = $round->words;

        foreach ($words as $word) {
            // Only determine if not already determined
            if ($word->is_valid === null) {
                $word->determineValidity();
            }
        }
    }

    /**
     * Calculate score for a single word.
     */
    public function calculateWordScore(Word $word, array $userWords): int
    {
        // Check if word is valid
        if (!$word->isWordValid()) {
            return -15; // Invalid word penalty
        }

        // Check if word is marked as invalid by votes
        if ($word->is_valid === false) {
            return -15; // Voted as invalid
        }

        // Check if this word is unique among all users for this category
        $categoryWords = [];
        foreach ($userWords as $categoryId => $categoryWord) {
            if ($categoryWord->category_id === $word->category_id) {
                $categoryWords[] = strtolower($categoryWord->word);
            }
        }

        $wordLower = strtolower($word->word);
        $count = 0;

        foreach ($categoryWords as $categoryWord) {
            if ($categoryWord === $wordLower) {
                $count++;
            }
        }

        if ($count === 1) {
            return 10; // Unique word
        } elseif ($count > 1) {
            return 5; // Duplicate word
        }

        // If we can't determine uniqueness, give base points
        return 10;
    }

    /**
     * Calculate final scores for a game.
     */
    public function calculateFinalScores(Game $game): array
    {
        $players = $game->playerGames()->with('user')->get();
        $results = [];

        foreach ($players as $playerGame) {
            $results[] = [
                'user_id' => $playerGame->user_id,
                'user_name' => $playerGame->user->name,
                'score' => $playerGame->score,
                'position' => 0
            ];
        }

        // Sort by score descending
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Assign positions
        $position = 1;
        foreach ($results as &$result) {
            $result['position'] = $position++;
        }

        return $results;
    }

    /**
     * Get the winner of a game.
     */
    public function getWinner(Game $game): ?PlayerGame
    {
        $playerGames = $game->playerGames()
            ->orderBy('score', 'DESC')
            ->get();

        return $playerGames->first();
    }

    /**
     * Update user stats after a game.
     */
    public function updateUserStatsAfterGame(Game $game): void
    {
        $finalScores = $this->calculateFinalScores($game);
        $winner = $finalScores[0]['user_id'] ?? null;

        foreach ($finalScores as $scoreData) {
            $user = $game->players()->where('users.id', $scoreData['user_id'])->first();

            if ($user) {
                $stats = [
                    'games_played' => ($user->stats['games_played'] ?? 0) + 1,
                    'total_score' => ($user->stats['total_score'] ?? 0) + $scoreData['score']
                ];

                if ($user->id === $winner) {
                    $stats['wins'] = ($user->stats['wins'] ?? 0) + 1;
                }

                $user->updateStats($stats);
            }
        }
    }
}
