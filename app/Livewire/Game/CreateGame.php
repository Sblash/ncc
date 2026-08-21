<?php

namespace App\Livewire\Game;

use App\Models\Category;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateGame extends Component
{
    public string $name = '';
    public int $maxPlayers = 8;
    public array $selectedCategories = [];
    public int $rounds = 5;
    public int $roundDuration = 60;

    public array $allCategories = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'maxPlayers' => 'required|integer|min:2|max:20',
        'selectedCategories' => 'required|array|min:1',
        'rounds' => 'required|integer|min:1|max:10',
        'roundDuration' => 'required|integer|min:30|max:300',
    ];

    public function mount(): void
    {
        $this->loadCategories();
        $this->selectedCategories = [1, 2, 3]; // Default: Nomi, Cose, Citta
    }

    public function loadCategories(): void
    {
        $this->allCategories = Category::all()->toArray();
        
        // Fallback se non ci sono categorie
        if (empty($this->allCategories)) {
            $this->allCategories = [
                ['id' => 1, 'name' => 'Nomi'],
                ['id' => 2, 'name' => 'Cose'],
                ['id' => 3, 'name' => 'Citta'],
            ];
        }
    }

    public function submit(): void
    {
        $this->validate();

        try {
            // Ottieni l'utente autenticato
            $user = Auth::user();
            
            if (!$user) {
                $user = \App\Models\User::find(session('auth_user.id') ?? null);
            }

            // Crea la partita direttamente
            $game = Game::create([
                'name' => $this->name,
                'creator_id' => $user?->id,
                'max_players' => $this->maxPlayers,
                'status' => 'waiting',
                'settings' => json_encode([
                    'categories' => array_map(function ($id) {
                        $category = collect($this->allCategories)->firstWhere('id', $id);
                        return $category['name'] ?? 'Unknown';
                    }, $this->selectedCategories),
                    'rounds' => $this->rounds,
                    'round_duration' => $this->roundDuration,
                    'letters' => range('A', chr(64 + $this->rounds)),
                ]),
            ]);

            // Aggiungi il creatore alla partita
            $user?->games()->attach($game->id, [
                'score' => 0,
                'status' => 'joined',
            ]);

            $this->redirect(route('games.index'), true);
        } catch (\Exception $e) {
            $this->addError('name', 'Failed to create game: ' . $e->getMessage());
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('games.index'), true);
    }

    public function render()
    {
        return view('livewire.game.create-game')->layout('components.layouts.app');
    }
}
