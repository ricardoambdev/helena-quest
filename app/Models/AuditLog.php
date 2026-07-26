<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'team_id',
        'auditable_type',
        'auditable_id',
        'action',
        'context',
        'ip',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
