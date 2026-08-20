<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GameStats extends Component
{
    public $userId;
    public $stats = [];
    public $leaderboard = [];

    public function mount($userId = null): void
    {
        $this->userId = $userId ?? (session('auth_user.id') ?? null);
        $this->loadStats();
        $this->loadLeaderboard();
    }

    public function loadStats(): void
    {
        if (!$this->userId) {
            return;
        }
        
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get("/api/users/{$this->userId}/stats");
            
            if ($response->successful()) {
                $this->stats = $response->json();
            }
        } catch (\Exception $e) {
            $this->stats = [];
        }
    }

    public function loadLeaderboard(): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get('/api/leaderboard');
            
            if ($response->successful()) {
                $this->leaderboard = $response->json();
            }
        } catch (\Exception $e) {
            $this->leaderboard = [];
        }
    }

    public function render()
    {
        return view('livewire.game.game-stats');
    }
}
