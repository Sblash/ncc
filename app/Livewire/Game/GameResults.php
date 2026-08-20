<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GameResults extends Component
{
    public $gameId;
    public $game;
    public $rounds = [];
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
                $this->loadRounds();
            } else {
                $this->redirect(route('games.index'), true);
            }
        } catch (\Exception $e) {
            $this->redirect(route('games.index'), true);
        }
    }

    public function loadRounds(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/games/{$this->gameId}/rounds");
            
            if ($response->successful()) {
                $this->rounds = $response->json();
            }
        } catch (\Exception $e) {
            $this->rounds = [];
        }
    }

    public function getSortedPlayersProperty(): array
    {
        $players = $this->players;
        
        usort($players, function ($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });
        
        return $players;
    }

    public function getWinnerProperty()
    {
        $sorted = $this->getSortedPlayersProperty();
        return $sorted[0] ?? null;
    }

    public function render()
    {
        return view('livewire.game.game-results');
    }
}
