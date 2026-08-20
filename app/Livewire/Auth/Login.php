<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Login extends Component
{
    public string $name = '';
    public string $password = '';

    protected $rules = [
        'name' => 'required|string',
        'password' => 'required',
    ];

    public function submit(): void
    {
        $this->validate();

        try {
            $response = Http::asForm()->post('/api/login', [
                'name' => $this->name,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                session(['auth_token' => $data['access_token']]);
                session(['auth_user' => $data['user']]);
                $this->redirect(route('games.index'), true);
            } else {
                $this->addError('name', 'Invalid credentials');
            }
        } catch (\Exception $e) {
            $this->addError('name', 'Connection error. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
