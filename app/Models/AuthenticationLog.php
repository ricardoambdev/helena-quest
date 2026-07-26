<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'username_attempted',
        'ip',
        'device_id',
        'user_agent',
        'latitude',
        'longitude',
        'action',
        'success',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'success' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
