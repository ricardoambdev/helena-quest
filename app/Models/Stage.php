<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'name',
        'description',
        'order',
        'stage_type',
        'latitude',
        'longitude',
        'radius',
        'qr_code_uuid',
        'narrative_text',
        'image_path',
        'correct_answer',
        'secret_number',
        'next_stage_hint',
        'score',
        'penalty',
        'time_limit_minutes',
        'sub_questions',
        'compass_direction',
        'compass_steps',
        'compass_landmarks',
        'treasure_hint',
        'unlock_password',
        'unlock_order',
        'unlock_phrase',
        'word',
        'max_attempts',
        'cooldown_minutes',
        'word_score',
        'wrong_word_penalty',
        'admin_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'order' => 'integer',
        'score' => 'integer',
        'penalty' => 'integer',
        'time_limit_minutes' => 'integer',
        'sub_questions' => 'array',
        'compass_steps' => 'integer',
        'max_attempts' => 'integer',
        'cooldown_minutes' => 'integer',
        'word_score' => 'integer',
        'wrong_word_penalty' => 'integer',
    ];

    protected $hidden = ['correct_answer', 'secret_number', 'unlock_password', 'word'];

    protected static function booted(): void
    {
        static::creating(function (Stage $stage): void {
            if (empty($stage->qr_code_uuid)) {
                $stage->qr_code_uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class)->orderBy('order');
    }

    public function teamProgress(): HasMany
    {
        return $this->hasMany(TeamStageProgress::class);
    }

    public function audios(): HasMany
    {
        return $this->hasMany(Audio::class);
    }

    public function bonusOnus(): HasMany
    {
        return $this->hasMany(BonusOnus::class);
    }

    public function isFinalEnigma(): bool
    {
        return $this->stage_type === 'enigma_final';
    }

    protected function answerMasked(): Attribute
    {
        return Attribute::make(
            get: fn () => str_repeat('*', strlen($this->correct_answer ?? '')),
        );
    }
}
