<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function submit(): void
    {
        $this->validate();

        try {
            $response = Http::asForm()->post('/api/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                session(['auth_token' => $data['access_token']]);
                session(['auth_user' => $data['user']]);
                $this->redirect(route('games.index'), true);
            } else {
                $this->addError('email', 'Invalid credentials');
            }
        } catch (\Exception $e) {
            $this->addError('email', 'Connection error. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
