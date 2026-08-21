<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class Logout extends Component
{
    public function mount(): void
    {
        try {
            $token = session('auth_token');
            if ($token) {
                // Trova e cancella il token
                $user = session('auth_user');
                if ($user && isset($user['id'])) {
                    $userModel = \App\Models\User::find($user['id']);
                    if ($userModel) {
                        $userModel->tokens()->where('token', hash('sha256', $token))->delete();
                    }
                }
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
