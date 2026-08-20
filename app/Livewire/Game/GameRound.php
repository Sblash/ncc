<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GameRound extends Component
{
    public $gameId;
    public $round;
    public $game;
    public $words = [];
    public $categories = [];
    public $timeRemaining = 0;
    public $selectedCategoryId = null;
    public $wordInput = '';

    public function mount($gameId): void
    {
        $this->gameId = $gameId;
        $this->loadGame();
        $this->loadCategories();
    }

    public function loadGame(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/games/{$this->gameId}");
            
            if ($response->successful()) {
                $this->game = $response->json();
                $this->loadRound();
            } else {
                $this->redirect(route('games.index'), true);
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function loadRound(): void
    {
        if (empty($this->game['current_round_id'])) {
            return;
        }
        
        try {
            $token = session('auth_token');
            $roundId = $this->game['current_round_id'];
            $response = Http::withToken($token)->get("/api/rounds/{$roundId}");
            
            if ($response->successful()) {
                $this->round = $response->json();
                $this->updateTimeRemaining();
                $this->loadWords();
            }
        } catch (\Exception $e) {
            // Round not loaded
        }
    }

    public function loadCategories(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get('/api/categories');
            
            if ($response->successful()) {
                $this->categories = $response->json();
            }
        } catch (\Exception $e) {
            $this->categories = [
                ['id' => 1, 'name' => 'Nomi'],
                ['id' => 2, 'name' => 'Cose'],
                ['id' => 3, 'name' => 'Citta'],
            ];
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
            }
        } catch (\Exception $e) {
            $this->words = [];
        }
    }

    public function updateTimeRemaining(): void
    {
        if (empty($this->round['ends_at'])) {
            $this->timeRemaining = 0;
            return;
        }
        
        $endsAt = strtotime($this->round['ends_at']);
        $now = time();
        $this->timeRemaining = max(0, $endsAt - $now);
    }

    public function selectCategory($categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function submitWord(): void
    {
        $this->validate([
            'wordInput' => 'required|string|max:255',
            'selectedCategoryId' => 'required|exists:categories,id',
        ]);

        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->post("/api/rounds/{$this->round['id']}/words", [
                'word' => $this->wordInput,
                'category_id' => $this->selectedCategoryId,
            ]);
            
            if ($response->successful()) {
                $this->wordInput = '';
                $this->loadWords();
            } else {
                $this->dispatch('error', message: 'Failed to submit word');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Connection error');
        }
    }

    public function getRoundStatusProperty(): string
    {
        return $this->round['status'] ?? 'pending';
    }

    public function getUserWordsProperty(): array
    {
        $userId = session('auth_user.id') ?? null;
        return array_filter($this->words, function ($word) use ($userId) {
            return ($word['user_id'] ?? null) === $userId;
        });
    }

    public function render()
    {
        return view('livewire.game.game-round')->layout('components.layouts.app');
    }
}
