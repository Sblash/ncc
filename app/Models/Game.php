<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id',
        'max_players',
        'current_round_id',
        'status',
        'settings',
    ];

    protected $casts = [
        'status' => 'string',
        'settings' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(PlayerGame::class);
    }

    public function currentRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'current_round_id');
    }
}
