<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamBonusOnus extends Model
{
    use HasFactory;

    protected $table = 'team_bonus_onus';

    protected $fillable = [
        'team_id',
        'bonus_onus_id',
        'status',
        'score_earned',
        'completed_at',
    ];

    protected $casts = [
        'score_earned' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function bonusOnus(): BelongsTo
    {
        return $this->belongsTo(BonusOnus::class);
    }
}
