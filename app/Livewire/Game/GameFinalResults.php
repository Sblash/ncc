<?php

namespace App\Livewire\Game;

use Livewire\Component;
use App\Models\Game;
use App\Services\GameService;
use App\Services\ScoreCalculator;
use Illuminate\Support\Facades\Auth;

class GameFinalResults extends Component
{
    public $game;
    public $finalResults = [];
    public $isCreator = false;

    protected $gameService;
    protected $scoreCalculator;

    public function boot(GameService $gameService, ScoreCalculator $scoreCalculator)
    {
        $this->gameService = $gameService;
        $this->scoreCalculator = $scoreCalculator;
    }

    public function mount($gameId)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $this->game = Game::with(['creator', 'players.user', 'rounds' => function($query) {
            $query->with(['category', 'words' => function($q) {
                $q->with(['user', 'category', 'votes']);
            }]);
        }])->find($gameId);

        if (!$this->game) {
            session()->flash('error', 'Partita non trovata');
            return redirect('/games');
        }

        // Check if user is in the game
        if (!$this->game->hasPlayer(Auth::user())) {
            session()->flash('error', 'Non fai parte di questa partita');
            return redirect('/games');
        }

        $this->isCreator = $this->game->isCreator(Auth::user());

        if ($this->game->status !== 'finished') {
            // Game is not finished yet
            if ($this->game->currentRound && $this->game->currentRound->status === 'active') {
                return redirect('/games/' . $gameId . '/round');
            } elseif ($this->game->currentRound && $this->game->currentRound->status === 'voting') {
                return redirect('/games/' . $gameId . '/voting');
            } elseif ($this->game->currentRound && $this->game->currentRound->status === 'completed') {
                return redirect('/games/' . $gameId . '/results');
            } else {
                return redirect('/games/' . $gameId);
            }
        }

        $this->calculateFinalResults();
    }

    public function calculateFinalResults()
    {
        $this->finalResults = $this->scoreCalculator->calculateFinalScores($this->game);
    }

    public function newGame()
    {
        return redirect('/games/create');
    }

    public function backToGames()
    {
        return redirect('/games');
    }

    public function render()
    {
        return view('livewire.game.game-final-results');
    }
}
