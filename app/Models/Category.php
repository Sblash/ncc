<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Get all words in this category.
     */
    public function words()
    {
        return $this->hasMany(Word::class);
    }

    /**
     * Get all rounds that use this category.
     */
    public function rounds()
    {
        return $this->hasMany(Round::class);
    }
}
