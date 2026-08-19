<?php

namespace App\Http\Livewire\Game;

use Livewire\Component;
use App\Models\Category;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class CreateGame extends Component
{
    public $name = '';
    public $max_players = 8;
    public $rounds = 5;
    public $round_duration = 60;
    public $selectedLetters = ['A', 'B', 'C', 'D', 'E'];
    public $selectedCategories = [];

    public $availableLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'Z'];
    public $availableCategories = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'max_players' => 'required|integer|min:2|max:20',
        'rounds' => 'required|integer|min:1|max:20',
        'round_duration' => 'required|integer|min:30|max:300',
        'selectedLetters' => 'required|array|min:1',
        'selectedCategories' => 'required|array|min:1'
    ];

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

        $this->availableCategories = Category::orderBy('name')->get()->toArray();
        $this->selectedCategories = array_column($this->availableCategories, 'id');
    }

    public function createGame()
    {
        $this->validate();

        $game = $this->gameService->createGame(Auth::user(), [
            'name' => $this->name,
            'max_players' => $this->max_players,
            'rounds' => $this->rounds,
            'round_duration' => $this->round_duration,
            'letters' => $this->selectedLetters,
            'categories' => $this->selectedCategories
        ]);

        session()->flash('success', 'Partita creata con successo!');
        return redirect('/games/' . $game->id);
    }

    public function toggleLetter($letter)
    {
        if (in_array($letter, $this->selectedLetters)) {
            $this->selectedLetters = array_filter($this->selectedLetters, function($l) use ($letter) {
                return $l !== $letter;
            });
        } else {
            $this->selectedLetters[] = $letter;
        }
    }

    public function toggleCategory($categoryId)
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_filter($this->selectedCategories, function($id) use ($categoryId) {
                return $id != $categoryId;
            });
        } else {
            $this->selectedCategories[] = $categoryId;
        }
    }

    public function render()
    {
        return view('livewire.game.create-game');
    }
}
