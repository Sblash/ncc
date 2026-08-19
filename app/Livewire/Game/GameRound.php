<?php

namespace App\Http\Livewire\Game;

use Livewire\Component;
use App\Models\Game;
use App\Models\Round;
use App\Models\Word;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class GameRound extends Component
{
    public $game;
    public $round;
    public $timeRemaining = 0;
    public $words = [];
    public $categories = [];
    public $selectedWords = [];
    public $isSubmitting = false;
    public $showConfirmation = false;

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

        // Get current round
        $this->round = $this->game->currentRound;

        if (!$this->round || $this->round->status !== 'active') {
            // Redirect to appropriate page based on round status
            if ($this->round && $this->round->status === 'voting') {
                return redirect('/games/' . $gameId . '/voting');
            } elseif ($this->round && $this->round->status === 'completed') {
                return redirect('/games/' . $gameId . '/results');
            } elseif ($this->game->status === 'finished') {
                return redirect('/games/' . $gameId . '/final-results');
            } else {
                // No active round, go to lobby
                return redirect('/games/' . $gameId);
            }
        }

        $this->initializeRound();
    }

    public function initializeRound()
    {
        $this->categories = $this->game->getCategoryIds();
        $this->timeRemaining = $this->round->getTimeRemaining();
        
        // Load existing words for this user
        $existingWords = Word::where('round_id', $this->round->id)
            ->where('user_id', Auth::id())
            ->get();

        foreach ($existingWords as $word) {
            $this->selectedWords[$word->category_id] = $word->word;
        }

        // Initialize empty words for categories without submissions
        foreach ($this->categories as $categoryId) {
            if (!isset($this->selectedWords[$categoryId])) {
                $this->selectedWords[$categoryId] = '';
            }
        }
    }

    public function submitWord($categoryId)
    {
        $word = trim($this->selectedWords[$categoryId] ?? '');

        if (empty($word)) {
            session()->flash('error', 'Inserisci una parola valida');
            return;
        }

        try {
            $wordModel = $this->gameService->submitWord(
                Auth::user(),
                $this->round,
                $word,
                $categoryId
            );

            session()->flash('success', 'Parola salvata!');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function submitAllWords()
    {
        $this->isSubmitting = true;

        foreach ($this->selectedWords as $categoryId => $word) {
            $word = trim($word);
            if (!empty($word)) {
                try {
                    $this->gameService->submitWord(
                        Auth::user(),
                        $this->round,
                        $word,
                        $categoryId
                    );
                } catch (\Exception $e) {
                    session()->flash('error', 'Errore nel salvataggio: ' . $e->getMessage());
                    $this->isSubmitting = false;
                    return;
                }
            }
        }

        session()->flash('success', 'Tutte le parole sono state salvate!');
        $this->isSubmitting = false;
    }

    public function getTimeRemaining()
    {
        // This will be updated by polling
        return $this->timeRemaining;
    }

    public function checkRoundStatus()
    {
        // Poll the round status
        $this->round->refresh();
        
        if ($this->round->status !== 'active') {
            // Round has changed status, redirect
            if ($this->round->status === 'voting') {
                return redirect('/games/' . $this->game->id . '/voting');
            } elseif ($this->round->status === 'completed') {
                return redirect('/games/' . $this->game->id . '/results');
            }
        }

        $this->timeRemaining = $this->round->getTimeRemaining();
    }

    public function render()
    {
        return view('livewire.game.game-round');
    }
}
