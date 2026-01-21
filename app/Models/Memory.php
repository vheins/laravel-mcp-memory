<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use Exception;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memory extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use Cachable;

    // Transient properties for audit logging
    public ?string $audit_actor_id = null;
    public ?string $audit_actor_type = null;

    protected $fillable = [
        'organization', // github organization / user (repository owner)
        'repository', // specific repository slug (e.g. owner/repo)
        'title', // short summary or title
        'user_id', // specific user identifier
        'scope_type', // App\Enums\MemoryScope: system, organization, repository, user
        'memory_type', // App\Enums\MemoryType: business_rule, decision_log, preference, system_constraint, documentation, tech_stack, fact, task, architecture, user_context, convention, risk
        'status', // App\Enums\MemoryStatus: draft, verified, locked, deprecated, active
        'importance', // integer weight for ranking
        'embedding', // vector representation for semantic search
        'created_by_type', // human or ai
        'current_content', // the actual memory content
        'metadata', // additional structured data
    ];

    /**
     * @return HasMany<MemoryAccessLog, $this>
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(MemoryAccessLog::class, 'resource_id');
    }

    /**
     * @return HasMany<MemoryAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(MemoryAuditLog::class);
    }

    /**
     * @return BelongsToMany<Memory, $this, Pivot>
     */
    public function relatedMemories(): BelongsToMany
    {
        return $this->belongsToMany(
            Memory::class,
            'memory_relations',
            'source_id',
            'target_id'
        )->withPivot('relation_type')->withTimestamps();
    }

    // User relationship
    /**
     * @return BelongsTo<User, $this>
     */
    public function userRel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<MemoryVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MemoryVersion::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'embedding' => 'array',
            'importance' => 'integer',
            'status' => MemoryStatus::class,
            'memory_type' => MemoryType::class,
            'scope_type' => MemoryScope::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Memory $memory): void {
            if (! $memory->created_by_type) {
                $memory->created_by_type = 'human';
            }
        });

        static::updating(function (Memory $memory): void {
            throw_if($memory->getOriginal('status') === MemoryStatus::Locked && $memory->isDirty('current_content'), Exception::class, 'Cannot update locked memory.');
        });
    }
}
