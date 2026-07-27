<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusOnus extends Model
{
    use HasFactory;

    protected $table = 'bonus_onus';

    protected $fillable = [
        'stage_id',
        'type',
        'qr_code_uuid',
        'name',
        'description',
        'points',
        'message',
        'riddle',
        'correct_answer',
        'image_path',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function teamProgress(): HasMany
    {
        return $this->hasMany(TeamBonusOnus::class);
    }
}
