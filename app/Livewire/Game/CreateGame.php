<?php

namespace App\Livewire\Game;

use Illuminate\Support\Facades\Http;
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
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get('/api/categories');
            
            if ($response->successful()) {
                $this->allCategories = $response->json();
            }
        } catch (\Exception $e) {
            // Fallback categories
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
            $token = session('auth_token');
            $response = Http::withToken($token)->post('/api/games', [
                'name' => $this->name,
                'max_players' => $this->maxPlayers,
                'settings' => [
                    'categories' => array_map(function ($id) {
                        $category = collect($this->allCategories)->firstWhere('id', $id);
                        return $category['name'] ?? 'Unknown';
                    }, $this->selectedCategories),
                    'rounds' => $this->rounds,
                    'round_duration' => $this->roundDuration,
                    'letters' => range('A', chr(64 + $this->rounds)),
                ],
            ]);

            if ($response->successful()) {
                $this->redirect(route('games.index'), true);
            } else {
                $this->dispatch('error', message: 'Failed to create game');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Connection error');
        }
    }

    public function render()
    {
        return view('livewire.game.create-game');
    }
}
