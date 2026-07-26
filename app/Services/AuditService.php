<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function log(
        Team $team,
        string $action,
        ?Model $auditable = null,
        array $context = [],
        ?string $ip = null,
    ): void {
        AuditLog::create([
            'team_id' => $team->id,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'context' => empty($context) ? null : $context,
            'ip' => $ip ?? request()->ip(),
        ]);
    }
}
