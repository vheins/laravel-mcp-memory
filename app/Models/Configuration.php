<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
        'is_system',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the value casted to the correct type.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->castValue($value, $attributes['type'] ?? 'string'),
            set: fn ($value) => $this->prepareValue($value),
        );
    }

    protected function castValue($value, string $type)
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? $value + 0 : $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    protected function prepareValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ConfigurationAudit::class);
    }

    protected static function booted()
    {
        static::saved(function (Configuration $config) {
            Cache::forget("config_{$config->key}");
        });

        static::deleted(function (Configuration $config) {
            Cache::forget("config_{$config->key}");
        });
    }
}
