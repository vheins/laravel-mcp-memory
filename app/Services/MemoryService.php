<?php

namespace App\Services;

use App\Models\Memory;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Rules\ImmutableTypeRule;
use Illuminate\Support\Facades\Validator;

class MemoryService
{
    /**
     * Create or update a memory.
     *
     * @param array $data
     * @param string $actorId
     * @param string $actorType
     * @return Memory
     */
    public function write(array $data, string $actorId, string $actorType = 'human'): Memory
    {
        // Validate input type
        if (isset($data['memory_type'])) {
            Validator::make($data, [
                'memory_type' => [new ImmutableTypeRule($actorType)],
            ])->validate();
        }

        return DB::transaction(function () use ($data, $actorId, $actorType) {
            $id = $data['id'] ?? null;
            $content = $data['current_content'];
            $isNew = false;
            $oldValue = null;

            if ($id) {
                $memory = Memory::findOrFail($id);

                // Validate existing type for updates
                if ($actorType === 'ai') {
                    $rule = new ImmutableTypeRule($actorType);
                    $validator = Validator::make(['memory_type' => $memory->memory_type], [
                        'memory_type' => [$rule],
                    ]);
                    if ($validator->fails()) {
                         throw new \Illuminate\Validation\ValidationException($validator);
                    }
                }

                $oldValue = $memory->toArray();

                // Check if locked
                if ($memory->status === 'locked' && $memory->current_content !== $content) {
                    throw new \Exception('Cannot update locked memory.');
                }

                $memory->update([
                    'current_content' => $content,
                    'status' => $data['status'] ?? $memory->status,
                    'metadata' => $data['metadata'] ?? $memory->metadata,
                    // We typically don't update structural keys like organization/repo/user, but if needed:
                    // 'organization' => $data['organization'] ?? $memory->organization, etc.
                ]);
            } else {
                $isNew = true;
                $memory = Memory::create([
                    'id' => $data['id'] ?? Str::uuid()->toString(),
                    'organization' => $data['organization'],
                    'repository' => $data['repository'] ?? null,
                    'user_id' => $data['user_id'] ?? $data['user'] ?? null,
                    'scope_type' => $data['scope_type'],
                    'memory_type' => $data['memory_type'],
                    'created_by_type' => $data['created_by_type'] ?? $actorType,
                    'status' => $data['status'] ?? 'draft',
                    'current_content' => $content,
                    'metadata' => $data['metadata'] ?? null,
                ]);
            }

            // Create Version
            MemoryVersion::create([
                'memory_id' => $memory->id,
                'version_number' => $memory->versions()->max('version_number') + 1,
                'content' => $content,
                'created_by' => $actorId,
                'input_source' => $actorType,
            ]);

            // Audit Log
            MemoryAuditLog::create([
                'memory_id' => $memory->id,
                'actor_id' => $actorId,
                'actor_type' => $actorType,
                'event' => $isNew ? 'create' : 'update',
                'old_value' => $isNew ? null : $oldValue,
                'new_value' => $memory->fresh()->toArray(),
            ]);

            return $memory;
        });
    }

    public function read(string $id): Memory
    {
        return Memory::with(['versions', 'auditLogs'])->findOrFail($id);
    }


    /**
     * Search memories with hierarchy resolution.
     * Hierarchy: System -> Organization -> Repository -> User
     *
     * @param string $repositoryId (UUID of repository)
     * @param string|null $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(?string $repository, ?string $query = null, array $filters = [])
    {
        // 1. Resolve Hierarchy Context
        // Since organization/repository are now strings, we match them directly.
        // If we want to support hierarchy, we might need a way to link repo -> org as strings.
        // For now, we assume the provided $repository string is the context.
        $userId = $filters['user_id'] ?? $filters['user'] ?? null;

        $q = Memory::query();

        $q->where(function ($group) use ($repository, $userId) {
            // System Scope (Global)
            $group->where(function ($sub) {
                $sub->where('scope_type', 'system');
            });

            // Repository Scope
            if ($repository) {
                $group->orWhere(function ($sub) use ($repository) {
                    $sub->where('scope_type', 'repository')
                        ->where('repository', $repository);
                });
            } else {
                // If no repository specified, still include repository-scoped memories that are visible
                $group->orWhere('scope_type', 'repository');
            }

            // User Scope
            if ($userId) {
                $group->orWhere(function ($sub) use ($userId, $repository) {
                    $sub->where('scope_type', 'user')
                        ->where('user_id', $userId);

                    if ($repository) {
                        $sub->where(function($s) use ($repository) {
                            $s->where('repository', $repository)
                              ->orWhereNull('repository');
                        });
                    }
                });
            }
        });

        if ($query) {
            $q->where('current_content', 'like', "%{$query}%");
        }

        if (isset($filters['memory_type'])) {
            $q->where('memory_type', $filters['memory_type']);
        }

        if (isset($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        // Order by priority (User > Repo > Org > System) or Recency which is simpler.
        // Usually relevant memories are most specific.
        // Let's order by created_at desc for now as requested by user initially.
        $results = $q->orderByDesc('created_at')->get();
        return $results;
    }

    protected function createVersion(Memory $memory, string $content): void
    {
        // Determine next version number
        $latestVersion = $memory->versions()->max('version_number') ?? 0;

        $memory->versions()->create([
            'version_number' => $latestVersion + 1,
            'content' => $content,
        ]);
    }

    protected function createAuditLog(Memory $memory, string $actorId, string $actorType, string $event, ?array $oldValue, ?array $newValue): void
    {
        $memory->auditLogs()->create([
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'event' => $event,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
