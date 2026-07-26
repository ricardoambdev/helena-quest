<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalEnigmaQrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_enigma_id',
        'qr_code_uuid',
        'letter',
        'hint_text',
        'order',
    ];

    protected $hidden = ['letter'];

    protected static function booted(): void
    {
        static::creating(function (FinalEnigmaQrCode $code): void {
            if (empty($code->qr_code_uuid)) {
                $code->qr_code_uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function finalEnigma(): BelongsTo
    {
        return $this->belongsTo(FinalEnigma::class);
    }
}
