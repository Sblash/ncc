<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Logout extends Component
{
    public function mount(): void
    {
        try {
            $token = session('auth_token');
            if ($token) {
                Http::withToken($token)->post('/api/logout');
            }
            session()->forget(['auth_token', 'auth_user']);
            $this->redirect(route('login'), true);
        } catch (\Exception $e) {
            session()->forget(['auth_token', 'auth_user']);
            $this->redirect(route('login'), true);
        }
    }

    public function render()
    {
        return null;
    }
}
