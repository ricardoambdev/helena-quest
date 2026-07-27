<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemPreference extends Model
{
    protected $table = 'system_preferences';

    protected $fillable = ['key', 'value', 'type', 'label', 'group'];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $pref = static::where('key', $key)->first();
        if (!$pref) {
            return $default;
        }
        $val = $pref->value;
        return match ($pref->type) {
            'integer' => (int) $val,
            'float' => (float) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            default => $val,
        };
    }

    public static function setValue(string $key, mixed $value, ?string $type = null): void
    {
        if ($type === null) {
            $type = match (true) {
                is_int($value) => 'integer',
                is_float($value) => 'float',
                is_bool($value) => 'boolean',
                default => 'string',
            };
        }
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type],
        );
    }

    public static function getAllGrouped(): array
    {
        return static::orderBy('group')->orderBy('key')->get()->groupBy('group')->toArray();
    }
}
