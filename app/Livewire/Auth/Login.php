<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect('/games');
        }
    }

    public function login()
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Le credenziali fornite non sono corrette.',
            ]);
        }

        // Regenerate session
        request()->session()->regenerate();

        // Redirect to games page
        return redirect('/games');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
