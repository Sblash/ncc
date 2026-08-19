<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\AuthController;
use App\GameController;
use App\RoundController;
use App\WordController;
use App\VoteController;
use App\StatsController;
use App\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Category routes (public)
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Category routes (protected for create, update, delete)
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

    // Game routes
    Route::apiResource('games', GameController::class);
    Route::post('/games/{game}/join', [GameController::class, 'join']);
    Route::post('/games/{game}/leave', [GameController::class, 'leave']);
    Route::post('/games/{game}/start', [GameController::class, 'start']);
    Route::get('/games/{game}/rounds', [GameController::class, 'rounds']);

    // Round routes
    Route::apiResource('rounds', RoundController::class)->only(['show']);
    Route::post('/rounds/{round}/start', [RoundController::class, 'start']);
    Route::post('/rounds/{round}/end', [RoundController::class, 'end']);
    Route::post('/rounds/{round}/complete', [RoundController::class, 'complete']);
    Route::get('/rounds/current', [RoundController::class, 'current']);

    // Word routes
    Route::apiResource('rounds/{round}/words', WordController::class)->only(['index', 'store']);
    Route::get('/rounds/{round}/words/my', [WordController::class, 'myWords']);
    Route::put('/words/{word}', [WordController::class, 'update']);
    Route::delete('/words/{word}', [WordController::class, 'destroy']);

    // Vote routes
    Route::post('/words/{word}/vote', [VoteController::class, 'store']);
    Route::get('/words/{word}/votes', [VoteController::class, 'index']);
    Route::get('/votes/words-to-vote', [VoteController::class, 'wordsToVote']);

    // Stats routes
    Route::get('/users/{user}/stats', [StatsController::class, 'show']);
    Route::get('/stats/leaderboard', [StatsController::class, 'leaderboard']);
    Route::get('/stats/game-results', [StatsController::class, 'gameResults']);
    Route::get('/stats/round-results', [StatsController::class, 'roundResults']);
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'message' => 'Not Found'
    ], 404);
});
