<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'creator_id',
        'max_players',
        'current_round_id',
        'status',
        'settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'status' => 'string'
    ];

    /**
     * Get the creator of the game.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get all players in the game.
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'player_game')
            ->withPivot(['score', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all player game records.
     */
    public function playerGames()
    {
        return $this->hasMany(PlayerGame::class);
    }

    /**
     * Get all rounds in the game.
     */
    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    /**
     * Get the current round.
     */
    public function currentRound()
    {
        return $this->belongsTo(Round::class, 'current_round_id');
    }

    /**
     * Check if the game is full.
     */
    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_players;
    }

    /**
     * Check if the game has started.
     */
    public function hasStarted(): bool
    {
        return $this->status === 'started';
    }

    /**
     * Check if the game has finished.
     */
    public function hasFinished(): bool
    {
        return $this->status === 'finished';
    }

    /**
     * Check if the user is the creator.
     */
    public function isCreator(User $user): bool
    {
        return $this->creator_id === $user->id;
    }

    /**
     * Check if a user is in the game.
     */
    public function hasPlayer(User $user): bool
    {
        return $this->players()->where('users.id', $user->id)->exists();
    }

    /**
     * Get the current round number.
     */
    public function getCurrentRoundNumber(): int
    {
        return $this->currentRound ? $this->currentRound->round_number : 0;
    }

    /**
     * Get the total number of rounds.
     */
    public function getTotalRounds(): int
    {
        return $this->settings['rounds'] ?? 5;
    }

    /**
     * Get the round duration in seconds.
     */
    public function getRoundDuration(): int
    {
        return ($this->settings['round_duration'] ?? 60) * 1000; // Convert to milliseconds for frontend
    }

    /**
     * Get the letters for the game.
     */
    public function getLetters(): array
    {
        return $this->settings['letters'] ?? ['A', 'B', 'C', 'D', 'E'];
    }

    /**
     * Get the category IDs for the game.
     */
    public function getCategoryIds(): array
    {
        return $this->settings['categories'] ?? [1, 2, 3];
    }

    /**
     * Get the next letter for a new round.
     */
    public function getNextLetter(): string
    {
        $letters = $this->getLetters();
        $currentRoundNumber = $this->getCurrentRoundNumber();
        return $letters[$currentRoundNumber % count($letters)] ?? $letters[0];
    }

    /**
     * Get the next category for a new round.
     */
    public function getNextCategoryId(): int
    {
        $categoryIds = $this->getCategoryIds();
        $currentRoundNumber = $this->getCurrentRoundNumber();
        return $categoryIds[$currentRoundNumber % count($categoryIds)] ?? $categoryIds[0];
    }
}
