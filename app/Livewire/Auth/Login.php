<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Login extends Component
{
    public string $name = '';
    public string $password = '';

    protected $rules = [
        'name' => 'required|string',
        'password' => 'required',
    ];

    public function mount(): void
    {
        // Controlla se l'utente e' gia' loggato
        if (session()->has('auth_token') && session()->has('auth_user')) {
            $token = session('auth_token');
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->tokenable) {
                $this->redirect(route('games.index'), true);
            }
        }
    }

    public function submit(): void
    {
        $this->validate();

        try {
            Log::info('Login attempt for username: ' . $this->name);

            // Cerca utente per name
            $user = \App\Models\User::where('name', $this->name)->first();
            
            if (!$user) {
                Log::warning('User not found: ' . $this->name);
                $this->addError('name', 'Invalid credentials');
                return;
            }

            // Verifica password
            if (!Hash::check($this->password, $user->password)) {
                Log::warning('Invalid password for user: ' . $this->name);
                $this->addError('name', 'Invalid credentials');
                return;
            }

            // Crea token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;
            Log::info('Token created for user: ' . $user->id);
            
            // Salva in sessione
            session(['auth_token' => $token]);
            session(['auth_user' => $user->toArray()]);
            Log::info('Session set for user: ' . $user->id);
            
            $this->redirect(route('games.index'), true);
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->addError('name', 'Connection error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
