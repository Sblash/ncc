<?php

namespace App\Http\Livewire\Game;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class ListGames extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filter = 'all';
    public $search = '';
    public $perPage = 10;

    protected $gameService;

    public function boot(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function mount()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
    }

    public function getGamesProperty()
    {
        $query = Game::with(['creator', 'players', 'currentRound']);

        switch ($this->filter) {
            case 'waiting':
                $query->where('status', 'waiting');
                break;
            case 'started':
                $query->where('status', 'started');
                break;
            case 'finished':
                $query->where('status', 'finished');
                break;
            case 'my_games':
                $query->whereHas('players', function($q) {
                    $q->where('users.id', Auth::id());
                });
                break;
        }

        if ($this->search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('creator', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'DESC')->paginate($this->perPage);
    }

    public function joinGame($gameId)
    {
        $game = Game::find($gameId);
        
        if (!$game) {
            session()->flash('error', 'Partita non trovata');
            return;
        }

        $success = $this->gameService->joinGame(Auth::user(), $game);

        if ($success) {
            session()->flash('success', 'Sei entrato nella partita!');
            return redirect('/games/' . $game->id);
        } else {
            session()->flash('error', 'Non puoi unirti a questa partita. Potrebbe essere piena o già iniziata.');
        }
    }

    public function render()
    {
        return view('livewire.game.list-games', [
            'games' => $this->getGamesProperty()
        ]);
    }
}
