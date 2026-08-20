<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ListGames extends Component
{
    public $myGamesOnly = false;
    public $statusFilter = '';
    public $search = '';

    protected $queryString = [
        'myGamesOnly' => ['except' => false],
        'statusFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function toggleMyGames(): void
    {
        $this->myGamesOnly = !$this->myGamesOnly;
    }

    public function getGamesProperty(): array
    {
        try {
            $token = session('auth_token');
            $url = '/api/games';
            
            $params = [];
            if ($this->myGamesOnly) {
                $params['my_games'] = true;
            }
            if ($this->statusFilter) {
                $params['status'] = $this->statusFilter;
            }
            if ($this->search) {
                $params['search'] = $this->search;
            }
            
            $response = Http::withToken($token)->get($url, $params);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function joinGame($gameId): void
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->post("/api/games/{$gameId}/join");
            
            if ($response->successful()) {
                $this->dispatch('game-joined');
            } else {
                $this->dispatch('error', message: 'Failed to join game');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Connection error');
        }
    }

    public function render()
    {
        return view('livewire.game.list-games', [
            'games' => $this->getGamesProperty(),
        ]);
    }
}
