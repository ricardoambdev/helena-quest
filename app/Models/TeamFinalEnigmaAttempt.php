<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamFinalEnigmaAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'final_enigma_id',
        'attempt_number',
        'guessed_word',
        'correct',
        'next_available_at',
    ];

    protected $casts = [
        'correct' => 'boolean',
        'next_available_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function finalEnigma(): BelongsTo
    {
        return $this->belongsTo(FinalEnigma::class);
    }
}
