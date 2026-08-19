<?php

namespace App\Http\Livewire\Game;

use Livewire\Component;
use App\Models\User;
use App\Services\GameService;
use App\Services\ScoreCalculator;
use Illuminate\Support\Facades\Auth;

class GameStats extends Component
{
    public $user;
    public $stats = [];
    public $recentGames = [];
    public $leaderboard = [];

    protected $gameService;
    protected $scoreCalculator;

    public function boot(GameService $gameService, ScoreCalculator $scoreCalculator)
    {
        $this->gameService = $gameService;
        $this->scoreCalculator = $scoreCalculator;
    }

    public function mount()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $this->user = Auth::user();
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'games_played' => $this->user->getGamesPlayedAttribute(),
            'total_score' => $this->user->getTotalScoreAttribute(),
            'avg_score' => $this->user->getAvgScoreAttribute(),
            'wins' => $this->user->getWinsAttribute()
        ];

        // Get recent games
        $this->recentGames = $this->user->games()
            ->with(['creator', 'players', 'currentRound'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        // Get leaderboard
        $this->loadLeaderboard();
    }

    public function loadLeaderboard()
    {
        $users = User::orderByRaw('JSON_EXTRACT(stats, "$.total_score") DESC')
            ->limit(10)
            ->get();

        $this->leaderboard = [];
        $position = 1;

        foreach ($users as $user) {
            $this->leaderboard[] = [
                'position' => $position++,
                'user_id' => $user->id,
                'name' => $user->name,
                'total_score' => $user->getTotalScoreAttribute(),
                'games_played' => $user->getGamesPlayedAttribute(),
                'avg_score' => $user->getAvgScoreAttribute(),
                'wins' => $user->getWinsAttribute()
            ];
        }
    }

    public function render()
    {
        return view('livewire.game.game-stats');
    }
}
