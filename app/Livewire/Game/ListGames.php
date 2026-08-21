<?php

namespace App\Livewire\Game;

use App\Models\Game;
use App\Models\PlayerGame;
use Illuminate\Support\Facades\Auth;
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
            $user = Auth::user();
            
            if (!$user) {
                $userId = session('auth_user.id') ?? null;
                if ($userId) {
                    $user = \App\Models\User::find($userId);
                }
            }

            $query = Game::with(['creator', 'currentRound', 'players.user'])
                ->orderBy('created_at', 'desc');

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            if ($this->search) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }

            if ($this->myGamesOnly && $user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('players', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            }

            return $query->get()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function joinGame($gameId): void
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                $userId = session('auth_user.id') ?? null;
                if ($userId) {
                    $user = \App\Models\User::find($userId);
                }
            }

            if (!$user) {
                $this->dispatch('error', message: 'Not authenticated');
                return;
            }

            // Check if already joined
            $existing = PlayerGame::where('game_id', $gameId)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $this->dispatch('error', message: 'Already joined this game');
                return;
            }

            // Check if game is full
            $playerCount = PlayerGame::where('game_id', $gameId)
                ->where('status', 'joined')
                ->count();

            $game = Game::find($gameId);
            
            if ($playerCount >= $game->max_players) {
                $this->dispatch('error', message: 'Game is full');
                return;
            }

            // Join the game
            PlayerGame::create([
                'game_id' => $gameId,
                'user_id' => $user->id,
                'score' => 0,
                'status' => 'joined',
            ]);

            $this->dispatch('game-joined');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to join game: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.game.list-games', [
            'games' => $this->getGamesProperty(),
        ])->layout('components.layouts.app');
    }
}
