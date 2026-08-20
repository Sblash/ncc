<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GameVoting extends Component
{
    public $gameId;
    public $round;
    public $words = [];
    public $votes = [];

    public function mount($gameId): void
    {
        $this->gameId = $gameId;
        $this->loadRound();
    }

    public function loadRound(): void
    {
        try {
            $token = session('auth_token');
            $game = Http::withToken($token)->get("/api/games/{$this->gameId}");
            
            if ($game->successful()) {
                $gameData = $game->json();
                if (!empty($gameData['current_round_id'])) {
                    $roundId = $gameData['current_round_id'];
                    $this->loadRoundData($roundId);
                }
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function loadRoundData($roundId): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/rounds/{$roundId}");
            
            if ($response->successful()) {
                $this->round = $response->json();
                $this->loadWords();
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function loadWords(): void
    {
        if (empty($this->round['id'])) {
            return;
        }
        
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/rounds/{$this->round['id']}/words");
            
            if ($response->successful()) {
                $this->words = $response->json();
                $this->loadUserVotes();
            }
        } catch (\Exception $e) {
            $this->words = [];
        }
    }

    public function loadUserVotes(): void
    {
        $userId = session('auth_user.id') ?? null;
        $this->votes = [];
        
        foreach ($this->words as $word) {
            foreach ($word['votes'] ?? [] as $vote) {
                if (($vote['user_id'] ?? null) === $userId) {
                    $this->votes[$word['id']] = $vote['is_valid'] ?? null;
                    break;
                }
            }
        }
    }

    public function vote($wordId, $isValid): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->post("/api/words/{$wordId}/vote", [
                'is_valid' => $isValid,
            ]);
            
            if ($response->successful()) {
                $this->votes[$wordId] = $isValid;
                $this->loadRound();
            } else {
                $this->dispatch('error', message: 'Failed to vote');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Connection error');
        }
    }

    public function getWordsToVoteProperty(): array
    {
        $userId = session('auth_user.id') ?? null;
        
        return array_filter($this->words, function ($word) use ($userId) {
            // Filter out user's own words
            return ($word['user_id'] ?? null) !== $userId;
        });
    }

    public function getHasVotedAllProperty(): bool
    {
        foreach ($this->getWordsToVoteProperty() as $word) {
            if (!isset($this->votes[$word['id']])) {
                return false;
            }
        }
        
        return true;
    }

    public function render()
    {
        return view('livewire.game.game-voting')->layout('components.layouts.app');
    }
}
