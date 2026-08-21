<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:users',
        'password' => 'required|min:8|confirmed',
    ];

    public function submit(): void
    {
        $this->validate();

        try {
            Log::info('Registration attempt for username: ' . $this->name);

            // Crea utente direttamente
            $user = User::create([
                'name' => $this->name,
                'email' => $this->name . '@example.com',
                'password' => Hash::make($this->password),
                'stats' => json_encode([
                    'games_played' => 0,
                    'total_score' => 0,
                    'avg_score' => 0,
                ]),
            ]);

            Log::info('User created with ID: ' . $user->id);

            // Crea token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Token created for user: ' . $user->id);
            
            // Salva in sessione
            session(['auth_token' => $token]);
            session(['auth_user' => $user->toArray()]);
            Log::info('Session set for user: ' . $user->id);
            
            $this->redirect(route('games.index'), true);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->addError('name', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.guest');
    }
}
