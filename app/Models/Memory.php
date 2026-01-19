<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'repository_id',
        'scope_type',
        'memory_type',
        'status',
        'created_by_type',
        'current_content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MemoryVersion::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MemoryAuditLog::class);
    }

    protected static function booted(): void
    {
        static::updating(function (Memory $memory) {
            if ($memory->original['status'] === 'locked' && $memory->isDirty('current_content')) {
                throw new \Exception('Cannot update locked memory.');
            }
        });
    }
}
