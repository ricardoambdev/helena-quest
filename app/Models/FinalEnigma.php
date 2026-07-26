<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinalEnigma extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'word',
        'max_attempts',
        'cooldown_minutes',
        'final_score',
        'description',
    ];

    protected $hidden = ['word'];

    protected $casts = [
        'final_score' => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(FinalEnigmaQrCode::class)->orderBy('order');
    }

    public function teamAttempts(): HasMany
    {
        return $this->hasMany(TeamFinalEnigmaAttempt::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(TeamFinalEnigmaLetter::class);
    }

    public function getLettersListAttribute(): array
    {
        return $this->qrCodes->pluck('letter')->toArray();
    }
}
