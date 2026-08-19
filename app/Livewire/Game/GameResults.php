<?php

namespace App\Livewire\Game;

use Livewire\Component;
use App\Models\Game;
use App\Models\Round;
use App\Services\GameService;
use App\Services\ScoreCalculator;
use Illuminate\Support\Facades\Auth;

class GameResults extends Component
{
    public $game;
    public $round;
    public $results = [];
    public $isCreator = false;
    public $showNextRoundButton = false;

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

        if (!$this->round || $this->round->status !== 'completed') {
            // Redirect to appropriate page
            if ($this->round && $this->round->status === 'active') {
                return redirect('/games/' . $gameId . '/round');
            } elseif ($this->round && $this->round->status === 'voting') {
                return redirect('/games/' . $gameId . '/voting');
            } elseif ($this->game->status === 'finished') {
                return redirect('/games/' . $gameId . '/final-results');
            } else {
                return redirect('/games/' . $gameId);
            }
        }

        $this->calculateResults();
    }

    public function calculateResults()
    {
        // Get all player scores for this round
        $playerScores = [];
        
        foreach ($this->game->players as $player) {
            $playerScores[$player->id] = [
                'user_id' => $player->id,
                'user_name' => $player->user->name,
                'score' => 0,
                'words' => []
            ];
        }

        // Get all words for this round
        $words = $this->round->words()->with(['user', 'category', 'votes'])->get();

        foreach ($words as $word) {
            $userId = $word->user_id;
            $score = $this->scoreCalculator->calculateWordScore($word, $words->toArray());
            
            $playerScores[$userId]['score'] += $score;
            $playerScores[$userId]['words'][] = [
                'word' => $word->word,
                'category' => $word->category->name,
                'is_valid' => $word->is_valid,
                'score' => $score
            ];
        }

        // Sort by score descending
        usort($playerScores, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $this->results = array_values($playerScores);

        // Check if there are more rounds
        $this->showNextRoundButton = $this->isCreator && 
                                    $this->game->status === 'started' &&
                                    $this->round->round_number < $this->game->getTotalRounds();
    }

    public function nextRound()
    {
        if (!$this->showNextRoundButton) {
            session()->flash('error', 'Non puoi avviare il prossimo round');
            return;
        }

        // Create next round
        $nextRound = $this->gameService->createNextRound($this->game);
        
        // Start the next round
        $this->gameService->startRound($nextRound);

        session()->flash('success', 'Prossimo round avviato!');
        return redirect('/games/' . $this->game->id . '/round');
    }

    public function checkRoundStatus()
    {
        // Poll the game and round status
        $this->game->refresh();
        $this->round->refresh();
        
        if ($this->round->status !== 'completed') {
            // Round has changed status, redirect
            if ($this->round->status === 'active') {
                return redirect('/games/' . $this->game->id . '/round');
            } elseif ($this->round->status === 'voting') {
                return redirect('/games/' . $this->game->id . '/voting');
            }
        }

        if ($this->game->status === 'finished') {
            return redirect('/games/' . $this->game->id . '/final-results');
        }

        $this->calculateResults();
    }

    public function render()
    {
        return view('livewire.game.game-results');
    }
}
