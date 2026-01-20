<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization',
        'repository',
        'title',
        'user_id',
        'scope_type',
        'memory_type',
        'status',
        'importance',
        'embedding',
        'created_by_type',
        'current_content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'embedding' => 'array',
        'importance' => 'integer',
    ];

    // User relationship
    public function userRel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MemoryVersion::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MemoryAuditLog::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(MemoryAccessLog::class, 'resource_id');
    }

    public function relatedMemories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Memory::class,
            'memory_relations',
            'source_id',
            'target_id'
        )->withPivot('relation_type')->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function (Memory $memory) {
            if (! $memory->created_by_type) {
                $memory->created_by_type = 'human';
            }
        });

        static::updating(function (Memory $memory) {
            if ($memory->original['status'] === 'locked' && $memory->isDirty('current_content')) {
                throw new \Exception('Cannot update locked memory.');
            }
        });
    }
}
