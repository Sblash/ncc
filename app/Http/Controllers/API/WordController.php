<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Round;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WordController extends Controller
{
    public function store(Request $request, Round $round): JsonResponse
    {
        // Check if round is active
        if ($round->status !== 'active') {
            return response()->json([
                'message' => 'Cannot submit words, round is not active',
            ], 400);
        }

        // Check if round has expired
        if ($round->ends_at && now() > $round->ends_at) {
            return response()->json([
                'message' => 'Cannot submit words, round has ended',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'word' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if user already submitted a word for this category in this round
        $existingWord = Word::where('round_id', $round->id)
            ->where('user_id', $request->user()->id)
            ->where('category_id', $request->category_id)
            ->first();

        if ($existingWord) {
            return response()->json([
                'message' => 'You have already submitted a word for this category in this round',
            ], 400);
        }

        // Validate word starts with the round's letter (case-insensitive)
        $word = trim($request->word);
        $letter = strtoupper($round->letter);
        $firstChar = strtoupper(substr($word, 0, 1));

        if ($firstChar !== $letter) {
            return response()->json([
                'message' => 'Word must start with letter: ' . $letter,
            ], 422);
        }

        // Validate word is valid (only letters and spaces)
        if (!preg_match('/^[a-zA-Z\s]+$/', $word)) {
            return response()->json([
                'message' => 'Word can only contain letters and spaces',
            ], 422);
        }

        $category = Category::findOrFail($request->category_id);

        $wordModel = Word::create([
            'round_id' => $round->id,
            'user_id' => $request->user()->id,
            'category_id' => $category->id,
            'word' => $word,
            'is_valid' => null, // Will be determined during voting
        ]);

        return response()->json($wordModel->load(['user', 'category']), 201);
    }

    public function index(Request $request, Round $round): JsonResponse
    {
        $words = $round->words()
            ->with(['user', 'category', 'votes'])
            ->orderBy('created_at')
            ->get();

        return response()->json($words, 200);
    }
}
