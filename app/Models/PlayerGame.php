<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerGame extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'player_game';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'user_id',
        'score',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the game.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the player has joined.
     */
    public function hasJoined(): bool
    {
        return $this->status === 'joined';
    }

    /**
     * Check if the player has left.
     */
    public function hasLeft(): bool
    {
        return $this->status === 'left';
    }

    /**
     * Check if the player has finished.
     */
    public function hasFinished(): bool
    {
        return $this->status === 'finished';
    }

    /**
     * Add points to the player's score.
     */
    public function addScore(int $points): void
    {
        $this->increment('score', $points);
    }

    /**
     * Set the player's status.
     */
    public function setStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }
}
