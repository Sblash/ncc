<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'word_id',
        'user_id',
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
     * Get the word this vote is for.
     */
    public function word()
    {
        return $this->belongsTo(Word::class);
    }

    /**
     * Get the user who made this vote.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a valid vote.
     */
    public function isValid(): bool
    {
        return $this->is_valid === true;
    }

    /**
     * Check if this is an invalid vote.
     */
    public function isInvalid(): bool
    {
        return $this->is_valid === false;
    }
}
