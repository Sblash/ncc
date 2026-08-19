<?php

namespace App\Livewire\Game;

use Livewire\Component;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class GameLobby extends Component
{
    public $game;
    public $players = [];
    public $isCreator = false;
    public $canStart = false;

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

        $this->game = Game::with(['creator', 'players.user', 'currentRound'])->find($gameId);

        if (!$this->game) {
            session()->flash('error', 'Partita non trovata');
            return redirect('/games');
        }

        $this->isCreator = $this->game->isCreator(Auth::user());
        $this->updatePlayers();
        $this->checkCanStart();
    }

    public function updatePlayers()
    {
        $this->players = $this->game->players()->with('user')->get()->toArray();
    }

    public function checkCanStart()
    {
        $this->canStart = $this->isCreator && 
                        $this->game->status === 'waiting' &&
                        $this->game->players()->count() >= 2;
    }

    public function startGame()
    {
        if (!$this->canStart) {
            session()->flash('error', 'Non puoi avviare la partita. Sei sicuro di essere il creatore e ci siano almeno 2 giocatori?');
            return;
        }

        $success = $this->gameService->startGame(Auth::user(), $this->game);

        if ($success) {
            session()->flash('success', 'Partita avviata!');
            return redirect('/games/' . $this->game->id . '/round');
        } else {
            session()->flash('error', 'Impossibile avviare la partita.');
        }
    }

    public function leaveGame()
    {
        $success = $this->gameService->leaveGame(Auth::user(), $this->game);

        if ($success) {
            session()->flash('success', 'Hai lasciato la partita.');
            return redirect('/games');
        } else {
            session()->flash('error', 'Impossibile lasciare la partita.');
        }
    }

    public function copyInviteLink()
    {
        $url = url('/games/' . $this->game->id);
        $this->dispatch('copy-to-clipboard', text: $url);
        session()->flash('success', 'Link di invito copiato negli appunti!');
    }

    public function render()
    {
        return view('livewire.game.game-lobby');
    }
}
