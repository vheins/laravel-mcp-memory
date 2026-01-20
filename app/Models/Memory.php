<?php

namespace App\Models;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
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
        'organization', // github organization / user (repository owner)
        'repository', // specific repository slug (e.g. owner/repo)
        'title', // short summary or title
        'user_id', // specific user identifier
        'scope_type', // system, organization, repository, user
        'memory_type', // fact, preference, business_rule, system_constraint
        'status', // draft, published, locked
        'importance', // integer weight for ranking
        'embedding', // vector representation for semantic search
        'created_by_type', // human or ai
        'current_content', // the actual memory content
        'metadata', // additional structured data
    ];

    protected $casts = [
        'metadata' => 'array',
        'embedding' => 'array',
        'importance' => 'integer',
        'status' => MemoryStatus::class,
        'memory_type' => MemoryType::class,
        'scope_type' => MemoryScope::class,
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
            if ($memory->getOriginal('status') === MemoryStatus::Locked && $memory->isDirty('current_content')) {
                throw new \Exception('Cannot update locked memory.');
            }
        });
    }
}
