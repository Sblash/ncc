<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'round_id',
        'user_id',
        'category_id',
        'word',
        'is_valid'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_valid' => 'boolean'
    ];

    /**
     * Get the round this word belongs to.
     */
    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the user who submitted this word.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this word.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all votes for this word.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Check if the word is valid.
     */
    public function isValid(): bool
    {
        return $this->is_valid === true;
    }

    /**
     * Check if the word is invalid.
     */
    public function isInvalid(): bool
    {
        return $this->is_valid === false;
    }

    /**
     * Check if the word is pending validation.
     */
    public function isPending(): bool
    {
        return $this->is_valid === null;
    }

    /**
     * Set the word as valid.
     */
    public function markAsValid(): void
    {
        $this->update(['is_valid' => true]);
    }

    /**
     * Set the word as invalid.
     */
    public function markAsInvalid(): void
    {
        $this->update(['is_valid' => false]);
    }

    /**
     * Get the count of valid votes.
     */
    public function getValidVotesCount(): int
    {
        return $this->votes()->where('is_valid', true)->count();
    }

    /**
     * Get the count of invalid votes.
     */
    public function getInvalidVotesCount(): int
    {
        return $this->votes()->where('is_valid', false)->count();
    }

    /**
     * Determine if the word is valid based on votes.
     */
    public function determineValidity(): void
    {
        $validVotes = $this->getValidVotesCount();
        $invalidVotes = $this->getInvalidVotesCount();

        if ($validVotes > $invalidVotes) {
            $this->markAsValid();
        } elseif ($invalidVotes > $validVotes) {
            $this->markAsInvalid();
        }
        // If equal, keep as pending or set based on your game rules
    }

    /**
     * Check if the word starts with the round's letter.
     */
    public function startsWithCorrectLetter(): bool
    {
        $letter = $this->round->letter;
        $word = strtoupper($this->word);
        
        // Handle special cases for Italian
        $validStarts = [
            strtoupper($letter),
            // Handle accented letters
            $this->getAccentedVersion($letter)
        ];

        return in_array(substr($word, 0, 1), $validStarts);
    }

    /**
     * Get accented version of a letter.
     */
    private function getAccentedVersion(string $letter): string
    {
        $accented = [
            'A' => ['À', 'Á', 'Â', 'Ã', 'Ä'],
            'E' => ['È', 'É', 'Ê', 'Ë'],
            'I' => ['Ì', 'Í', 'Î', 'Ï'],
            'O' => ['Ò', 'Ó', 'Ô', 'Õ', 'Ö'],
            'U' => ['Ù', 'Ú', 'Û', 'Ü']
        ];

        return $accented[$letter][0] ?? $letter;
    }

    /**
     * Check if the word is valid (not empty, correct letter, etc.).
     */
    public function isWordValid(): bool
    {
        // Check if word is not empty
        if (empty(trim($this->word))) {
            return false;
        }

        // Check if word starts with correct letter
        if (!$this->startsWithCorrectLetter()) {
            return false;
        }

        // Check if word contains only letters and valid characters
        if (!preg_match('/^[\p{L}\s\-\'\.]+$/u', $this->word)) {
            return false;
        }

        // Check minimum length (at least 2 characters)
        if (strlen(trim($this->word)) < 2) {
            return false;
        }

        return true;
    }
}
