<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proof extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'name',
        'description',
        'order',
        'status',
        'icon',
        'image_path',
        'color_hex',
        'max_score',
        'estimated_time_minutes',
        'admin_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order' => 'integer',
        'max_score' => 'integer',
        'estimated_time_minutes' => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('order');
    }

    public function firstStage(): ?Stage
    {
        return $this->stages()->first();
    }

    public function finalStage(): ?Stage
    {
        return $this->stages()->latest('order')->first();
    }
}
