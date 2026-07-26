<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamFinalEnigmaLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'final_enigma_id',
        'final_enigma_qr_code_id',
        'letter',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function finalEnigma(): BelongsTo
    {
        return $this->belongsTo(FinalEnigma::class);
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(FinalEnigmaQrCode::class, 'final_enigma_qr_code_id');
    }
}
