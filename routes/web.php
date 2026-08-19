<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Game\ListGames;
use App\Livewire\Game\CreateGame;
use App\Livewire\Game\GameLobby;
use App\Livewire\Game\GameRound;
use App\Livewire\Game\GameVoting;
use App\Livewire\Game\GameResults;
use App\Livewire\Game\GameFinalResults;
use App\Livewire\Game\GameStats;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home page - redirect to games list
Route::get('/', function () {
    return redirect('/games');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Game routes
Route::middleware('auth')->group(function () {
    // Games list
    Route::get('/games', ListGames::class)->name('games.index');
    
    // Create game
    Route::get('/games/create', CreateGame::class)->name('games.create');
    
    // Game lobby
    Route::get('/games/{game}', GameLobby::class)->name('games.lobby');
    
    // Game round
    Route::get('/games/{game}/round', GameRound::class)->name('games.round');
    
    // Game voting
    Route::get('/games/{game}/voting', GameVoting::class)->name('games.voting');
    
    // Game results
    Route::get('/games/{game}/results', GameResults::class)->name('games.results');
    
    // Final results
    Route::get('/games/{game}/final-results', GameFinalResults::class)->name('games.final-results');
    
    // User stats
    Route::get('/stats', GameStats::class)->name('stats');
    
    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Fallback route
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
