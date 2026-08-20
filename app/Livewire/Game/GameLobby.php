<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GameLobby extends Component
{
    public $gameId;
    public $game;
    public $players = [];

    public function mount($gameId): void
    {
        $this->gameId = $gameId;
        $this->loadGame();
    }

    public function loadGame(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/games/{$this->gameId}");
            
            if ($response->successful()) {
                $this->game = $response->json();
                $this->players = $this->game['players'] ?? [];
            } else {
                $this->redirect(route('games.index'), true);
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function startGame(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->post("/api/games/{$this->gameId}/start");
            
            if ($response->successful()) {
                $this->dispatch('game-started');
                $this->loadGame();
            } else {
                $this->dispatch('error', message: 'Failed to start game');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Connection error');
        }
    }

    public function leaveGame(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->post("/api/games/{$this->gameId}/leave");
            
            if ($response->successful()) {
                $this->redirect(route('games.index'), true);
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function getIsCreatorProperty(): bool
    {
        $user = session('auth_user');
        return ($user['id'] ?? null) === ($this->game['creator_id'] ?? null);
    }

    public function getCanStartProperty(): bool
    {
        if ($this->game['status'] ?? null !== 'waiting') {
            return false;
        }
        
        $playerCount = count(array_filter($this->players, function ($p) {
            return ($p['status'] ?? '') === 'joined';
        }));
        
        return $playerCount >= 2;
    }

    public function render()
    {
        return view('livewire.game.game-lobby');
    }
}
