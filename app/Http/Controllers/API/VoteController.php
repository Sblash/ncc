<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoteController extends Controller
{
    public function store(Request $request, Word $word): JsonResponse
    {
        // Check if word's round is in voting phase
        if ($word->round->status !== 'voting') {
            return response()->json([
                'message' => 'Cannot vote, round is not in voting phase',
            ], 400);
        }

        // Check if user is trying to vote on their own word
        if ($word->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot vote on your own word',
            ], 400);
        }

        // Check if user already voted on this word
        $existingVote = Vote::where('word_id', $word->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingVote) {
            return response()->json([
                'message' => 'You have already voted on this word',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'is_valid' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vote = Vote::create([
            'word_id' => $word->id,
            'user_id' => $request->user()->id,
            'is_valid' => $request->is_valid,
        ]);

        // Check if all players have voted on all words
        $this->checkVotingCompletion($word->round);

        return response()->json($vote->load(['word', 'user']), 201);
    }

    protected function checkVotingCompletion($round): void
    {
        $words = $round->words;
        $playerCount = $round->game->players()->where('status', 'joined')->count();

        foreach ($words as $word) {
            $votesCount = $word->votes()->count();
            
            // Each word should have votes from all other players
            $expectedVotes = $playerCount - 1; // Exclude the word owner
            
            if ($votesCount >= $expectedVotes) {
                // Calculate final validity based on majority vote
                $validVotes = $word->votes()->where('is_valid', true)->count();
                $invalidVotes = $word->votes()->where('is_valid', false)->count();
                
                $word->is_valid = $validVotes > $invalidVotes;
                $word->save();
            }
        }

        // Check if all words have been voted on
        $allWordsVoted = true;
        foreach ($words as $word) {
            if ($word->votes()->count() < ($playerCount - 1)) {
                $allWordsVoted = false;
                break;
            }
        }

        if ($allWordsVoted) {
            // Mark round as completed and calculate scores
            $round->update(['status' => 'completed']);
            $this->calculateRoundScores($round);
        }
    }

    protected function calculateRoundScores($round): void
    {
        $game = $round->game;
        $words = $round->words;
        
        // Group words by category
        $wordsByCategory = $words->groupBy('category_id');
        
        foreach ($wordsByCategory as $categoryId => $categoryWords) {
            // Count valid and invalid words for this category
            $validWords = $categoryWords->where('is_valid', true);
            $invalidWords = $categoryWords->where('is_valid', false);
            
            // Get unique valid words
            $uniqueValidWords = $validWords->unique('word');
            $duplicateValidWords = $validWords->diff($uniqueValidWords);
            
            // Update scores for each player
            foreach ($categoryWords as $word) {
                $playerGame = $game->players()
                    ->where('user_id', $word->user_id)
                    ->first();
                
                if (!$playerGame) {
                    continue;
                }
                
                $currentScore = $playerGame->score;
                
                if ($word->is_valid) {
                    // Check if word is unique
                    $isUnique = $uniqueValidWords->where('word', $word->word)->count() === 1;
                    
                    if ($isUnique) {
                        // Unique valid word: +10 points
                        $playerGame->score = $currentScore + 10;
                    } else {
                        // Duplicate valid word: +5 points
                        $playerGame->score = $currentScore + 5;
                    }
                } else {
                    // Invalid word: -15 points
                    $playerGame->score = $currentScore - 15;
                }
                
                $playerGame->save();
            }
        }
    }
}
