<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'letter',
        'category_id',
        'starts_at',
        'ends_at',
        'status',
        'round_number'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => 'string'
    ];

    /**
     * Get the game this round belongs to.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the category for this round.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all words submitted in this round.
     */
    public function words()
    {
        return $this->hasMany(Word::class);
    }

    /**
     * Check if the round is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the round is in voting phase.
     */
    public function isVoting(): bool
    {
        return $this->status === 'voting';
    }

    /**
     * Check if the round has completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the round has ended.
     */
    public function hasEnded(): bool
    {
        return $this->ends_at && now() >= $this->ends_at;
    }

    /**
     * Get the time remaining in the round.
     */
    public function getTimeRemaining(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        $remaining = $this->ends_at->diffInSeconds(now());
        return max(0, $remaining);
    }

    /**
     * Get all unique words for this round (grouped by word).
     */
    public function getUniqueWords()
    {
        return $this->words()
            ->selectRaw('word, COUNT(*) as count')
            ->groupBy('word')
            ->orderBy('count', 'DESC')
            ->get();
    }

    /**
     * Get words by user for this round.
     */
    public function getWordsByUser(User $user)
    {
        return $this->words()->where('user_id', $user->id)->get();
    }

    /**
     * Check if a user has submitted a word for this round.
     */
    public function userHasSubmitted(User $user): bool
    {
        return $this->words()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the count of words submitted by each user.
     */
    public function getWordCountsByUser()
    {
        return $this->words()
            ->selectRaw('user_id, COUNT(*) as word_count')
            ->groupBy('user_id')
            ->with('user')
            ->get();
    }
}
