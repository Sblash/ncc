<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Logout;
use App\Livewire\Auth\Register;
use App\Livewire\Game\CreateGame;
use App\Livewire\Game\GameLobby;
use App\Livewire\Game\GameResults;
use App\Livewire\Game\GameRound;
use App\Livewire\Game\GameStats;
use App\Livewire\Game\GameVoting;
use App\Livewire\Game\ListGames;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', Logout::class)->name('logout');
    
    // Game routes
    Route::get('/games', ListGames::class)->name('games.index');
    Route::get('/games/create', CreateGame::class)->name('games.create');
    Route::get('/games/{game}', GameLobby::class)->name('games.show');
    Route::get('/games/{game}/round', GameRound::class)->name('games.round');
    Route::get('/games/{game}/voting', GameVoting::class)->name('games.voting');
    Route::get('/games/{game}/results', GameResults::class)->name('games.results');
    
    // Stats
    Route::get('/stats', GameStats::class)->name('stats');
});

// API routes
require __DIR__.'/api.php';

// Fallback
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('games.index');
    }
    return redirect()->route('login');
})->name('home');
