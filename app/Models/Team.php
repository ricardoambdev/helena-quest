<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Team extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'name',
        'color_hex',
        'username',
        'password_hash',
        'status',
        'crest_path',
        'war_cry_audio_path',
        'war_cry_text',
        'photo_path',
        'description',
        'admin_notes',
        'password_changed_at',
        'password_changed_by',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'password_changed_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function stageProgress(): HasMany
    {
        return $this->hasMany(TeamStageProgress::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TeamProgress::class);
    }

    public function audios(): HasMany
    {
        return $this->hasMany(Audio::class);
    }

    public function authenticationLogs(): HasMany
    {
        return $this->hasMany(AuthenticationLog::class);
    }

    public function bonusOnus(): HasMany
    {
        return $this->hasMany(TeamBonusOnus::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canLogin(): bool
    {
        return in_array($this->status, ['active'], true);
    }
}
