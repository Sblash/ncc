<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\RoundController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\VoteController;
use App\Http\Controllers\API\WordController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // Auth routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Game routes
        Route::get('/games', [GameController::class, 'index']);
        Route::post('/games', [GameController::class, 'store']);
        Route::get('/games/{game}', [GameController::class, 'show']);
        Route::post('/games/{game}/join', [GameController::class, 'join']);
        Route::post('/games/{game}/leave', [GameController::class, 'leave']);
        Route::post('/games/{game}/start', [GameController::class, 'start']);

        // Round routes
        Route::get('/games/{game}/rounds', [RoundController::class, 'index']);
        Route::get('/rounds/{round}', [RoundController::class, 'show']);
        Route::post('/rounds/{round}/next', [RoundController::class, 'nextRound']);
        Route::post('/rounds/{round}/end', [RoundController::class, 'endRound']);

        // Word routes
        Route::post('/rounds/{round}/words', [WordController::class, 'store']);
        Route::get('/rounds/{round}/words', [WordController::class, 'index']);

        // Vote routes
        Route::post('/words/{word}/vote', [VoteController::class, 'store']);

        // Stats routes
        Route::get('/users/{user}/stats', [StatsController::class, 'show']);
        Route::get('/leaderboard', [StatsController::class, 'leaderboard']);
    });
});
