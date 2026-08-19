<?php

namespace App\Livewire\Game;

use Livewire\Component;
use App\Models\Game;
use App\Models\Round;
use App\Models\Word;
use App\Models\Vote;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class GameVoting extends Component
{
    public $game;
    public $round;
    public $words = [];
    public $votes = [];
    public $allVoted = false;
    public $isCreator = false;

    protected $gameService;

    public function boot(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function mount($gameId)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $this->game = Game::with(['creator', 'players.user', 'currentRound.category', 'rounds'])->find($gameId);

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

        // Get current round
        $this->round = $this->game->currentRound;

        if (!$this->round || $this->round->status !== 'voting') {
            // Redirect to appropriate page
            if ($this->round && $this->round->status === 'active') {
                return redirect('/games/' . $gameId . '/round');
            } elseif ($this->round && $this->round->status === 'completed') {
                return redirect('/games/' . $gameId . '/results');
            } elseif ($this->game->status === 'finished') {
                return redirect('/games/' . $gameId . '/final-results');
            } else {
                return redirect('/games/' . $gameId);
            }
        }

        $this->initializeVoting();
    }

    public function initializeVoting()
    {
        // Get all words from other users for this round
        $this->words = Word::where('round_id', $this->round->id)
            ->where('user_id', '!=', Auth::id())
            ->with(['user', 'category', 'votes' => function($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->toArray();

        // Initialize votes array
        foreach ($this->words as $word) {
            $this->votes[$word['id']] = null;
            
            // Check if user has already voted on this word
            if (!empty($word['votes'])) {
                $this->votes[$word['id']] = $word['votes'][0]['is_valid'] ?? null;
            }
        }

        $this->checkAllVoted();
    }

    public function vote($wordId, $isValid)
    {
        $this->votes[$wordId] = $isValid;
        
        try {
            $word = Word::find($wordId);
            if ($word) {
                $this->gameService->voteOnWord(Auth::user(), $word, $isValid);
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->checkAllVoted();
    }

    public function checkAllVoted()
    {
        // Check if user has voted on all words
        foreach ($this->words as $word) {
            if ($this->votes[$word['id']] === null) {
                $this->allVoted = false;
                return;
            }
        }
        $this->allVoted = true;
    }

    public function completeRound()
    {
        if (!$this->isCreator) {
            session()->flash('error', 'Solo il creatore della partita può completare il round');
            return;
        }

        $success = $this->gameService->completeRound($this->round);

        if ($success) {
            session()->flash('success', 'Round completato!');
            return redirect('/games/' . $this->game->id . '/results');
        } else {
            session()->flash('error', 'Impossibile completare il round');
        }
    }

    public function checkRoundStatus()
    {
        // Poll the round status
        $this->round->refresh();
        
        if ($this->round->status !== 'voting') {
            // Round has changed status, redirect
            if ($this->round->status === 'active') {
                return redirect('/games/' . $this->game->id . '/round');
            } elseif ($this->round->status === 'completed') {
                return redirect('/games/' . $this->game->id . '/results');
            }
        }

        // Refresh words and votes
        $this->initializeVoting();
    }

    public function render()
    {
        return view('livewire.game.game-voting');
    }
}
