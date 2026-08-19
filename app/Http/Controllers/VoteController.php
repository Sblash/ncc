<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoteController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Vote on a word.
     */
    public function store(Word $word, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_valid' => ['required', 'boolean']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vote = $this->gameService->voteOnWord(
                $request->user(),
                $word,
                $request->is_valid
            );

            return response()->json([
                'message' => 'Vote submitted successfully',
                'vote' => $vote->load(['user', 'word'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get votes for a word.
     */
    public function index(Word $word): JsonResponse
    {
        $votes = $word->votes()->with(['user'])->get();

        return response()->json([
            'votes' => $votes
        ], 200);
    }

    /**
     * Get words that need to be voted on by the current user.
     */
    public function wordsToVote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'round_id' => ['required', 'integer', 'exists:rounds,id']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $roundId = $request->round_id;
        $userId = $request->user()->id;

        // Get words from other users that this user hasn't voted on yet
        $words = Word::where('round_id', $roundId)
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('votes', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user', 'category'])
            ->get();

        return response()->json([
            'words' => $words
        ], 200);
    }
}
