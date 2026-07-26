<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamStageProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'stage_id',
        'status',
        'qr_scanned_at',
        'gps_lat',
        'gps_lng',
        'photo_path',
        'photo_sent_at',
        'attempts_count',
        'started_at',
        'completed_at',
        'score_earned',
        'time_spent_seconds',
        'last_answer',
        'was_correct',
        'hint_used',
    ];

    protected $casts = [
        'qr_scanned_at' => 'datetime',
        'photo_sent_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'gps_lat' => 'decimal:8',
        'gps_lng' => 'decimal:8',
        'attempts_count' => 'integer',
        'was_correct' => 'boolean',
        'hint_used' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }
}
