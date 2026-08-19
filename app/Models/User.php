<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stats'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'stats' => 'array'
    ];

    /**
     * Get the games created by the user.
     */
    public function createdGames()
    {
        return $this->hasMany(Game::class, 'creator_id');
    }

    /**
     * Get the games the user is playing in.
     */
    public function playerGames()
    {
        return $this->hasMany(PlayerGame::class);
    }

    /**
     * Get all games the user is part of.
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'player_game')
            ->withPivot(['score', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all words submitted by the user.
     */
    public function words()
    {
        return $this->hasMany(Word::class);
    }

    /**
     * Get all votes made by the user.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Update user stats.
     */
    public function updateStats(array $updates): void
    {
        $currentStats = $this->stats ?? [
            'games_played' => 0,
            'total_score' => 0,
            'avg_score' => 0,
            'wins' => 0
        ];

        $newStats = array_merge($currentStats, $updates);

        // Recalculate average score
        if (isset($newStats['total_score']) && isset($newStats['games_played']) && $newStats['games_played'] > 0) {
            $newStats['avg_score'] = round($newStats['total_score'] / $newStats['games_played'], 2);
        }

        $this->update(['stats' => $newStats]);
    }

    /**
     * Get the user's total score.
     */
    public function getTotalScoreAttribute(): int
    {
        return $this->stats['total_score'] ?? 0;
    }

    /**
     * Get the user's average score.
     */
    public function getAvgScoreAttribute(): float
    {
        return $this->stats['avg_score'] ?? 0;
    }

    /**
     * Get the number of games played.
     */
    public function getGamesPlayedAttribute(): int
    {
        return $this->stats['games_played'] ?? 0;
    }

    /**
     * Get the number of wins.
     */
    public function getWinsAttribute(): int
    {
        return $this->stats['wins'] ?? 0;
    }
}
