<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'year',
        'date',
        'start_time',
        'end_time',
        'status',
        'max_teams',
        'logo_path',
        'banner_path',
        'rules_markdown',
        'admin_notes',
        'started_at',
        'finished_at',
        'school_name',
        'school_address',
        'school_latitude',
        'school_longitude',
        'school_logo_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'year' => 'integer',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('order');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
