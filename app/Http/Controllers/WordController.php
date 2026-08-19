<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Word;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WordController extends Controller
{
    protected GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Submit a word for a round.
     */
    public function store(Round $round, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'word' => ['required', 'string', 'min:2'],
            'category_id' => ['required', 'integer', 'exists:categories,id']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $word = $this->gameService->submitWord(
                $request->user(),
                $round,
                $request->word,
                $request->category_id
            );

            return response()->json([
                'message' => 'Word submitted successfully',
                'word' => $word->load(['user', 'category'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get words for a round.
     */
    public function index(Round $round): JsonResponse
    {
        $words = $round->words()->with(['user', 'category', 'votes'])->get();

        return response()->json([
            'words' => $words
        ], 200);
    }

    /**
     * Get words submitted by the current user for a round.
     */
    public function myWords(Round $round, Request $request): JsonResponse
    {
        $words = $round->words()
            ->where('user_id', $request->user()->id)
            ->with(['category'])
            ->get();

        return response()->json([
            'words' => $words
        ], 200);
    }

    /**
     * Update a word.
     */
    public function update(Word $word, Request $request): JsonResponse
    {
        // Check if word belongs to current user
        if ($word->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only update your own words'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'word' => ['required', 'string', 'min:2']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $word->update(['word' => $request->word]);

        return response()->json([
            'message' => 'Word updated successfully',
            'word' => $word
        ], 200);
    }

    /**
     * Delete a word.
     */
    public function destroy(Word $word, Request $request): JsonResponse
    {
        // Check if word belongs to current user
        if ($word->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only delete your own words'
            ], 403);
        }

        $word->delete();

        return response()->json([
            'message' => 'Word deleted successfully'
        ], 200);
    }
}
