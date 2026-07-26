<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hint extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'hint_text',
        'price',
        'order',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }
}
