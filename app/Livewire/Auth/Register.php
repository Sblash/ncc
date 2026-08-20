<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Http;
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
            $response = Http::post('/api/register', [
                'name' => $this->name,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                session(['auth_token' => $data['access_token']]);
                session(['auth_user' => $data['user']]);
                $this->redirect(route('games.index'), true);
            } else {
                $errors = $response->json();
                foreach ($errors as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->addError($field, $message);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addError('name', 'Registration failed. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.guest');
    }
}
