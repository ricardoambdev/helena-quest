<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamProgress extends Model
{
    use HasFactory;

    protected $table = 'team_progress';

    protected $fillable = [
        'team_id',
        'proof_id',
        'current_stage_id',
        'total_score',
        'total_time_seconds',
        'stages_completed',
        'correct_answers',
        'wrong_answers',
        'photos_count',
        'audios_count',
        'hints_bought',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function proof(): BelongsTo
    {
        return $this->belongsTo(Proof::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }
}
