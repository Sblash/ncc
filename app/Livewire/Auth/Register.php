<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect('/games');
        }
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'stats' => [
                'games_played' => 0,
                'total_score' => 0,
                'avg_score' => 0,
                'wins' => 0
            ]
        ]);

        // Login the new user
        Auth::login($user, true);

        // Regenerate session
        request()->session()->regenerate();

        // Redirect to games page
        return redirect('/games');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
