<?php

namespace App\Services;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Models\Memory;
use App\Models\MemoryAccessLog;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;
use App\Rules\ImmutableTypeRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MemoryService
{
    /**
     * Create or update a memory.
     */
    public function write(array $data, string $actorId, string $actorType = 'human'): Memory
    {
        // Validate input type and enums
        Validator::make($data, [
            'memory_type' => [
                'sometimes',
                'required',
                new \Illuminate\Validation\Rules\Enum(MemoryType::class),
                new ImmutableTypeRule($actorType),
            ],
            'status' => [
                'sometimes',
                'required',
                new \Illuminate\Validation\Rules\Enum(MemoryStatus::class),
            ],
            'scope_type' => [
                'sometimes',
                'required',
                new \Illuminate\Validation\Rules\Enum(MemoryScope::class),
            ],
        ])->validate();

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
                    $validator = Validator::make(['memory_type' => $memory->memory_type->value], [
                        'memory_type' => [$rule],
                    ]);
                    if ($validator->fails()) {
                        throw new \Illuminate\Validation\ValidationException($validator);
                    }
                }

                $oldValue = $memory->toArray();

                // Check if locked
                if ($memory->status === MemoryStatus::Locked && $memory->current_content !== $content) {
                    throw new \Exception('Cannot update locked memory.');
                }

                $memory->update([
                    'current_content' => $content,
                    'title' => $data['title'] ?? $memory->title,
                    'status' => $data['status'] ?? $memory->status,
                    'importance' => $data['importance'] ?? $memory->importance,
                    'embedding' => $data['embedding'] ?? $memory->embedding,
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
                    'title' => $data['title'] ?? null,
                    'user_id' => $data['user_id'] ?? $data['user'] ?? null,
                    'scope_type' => $data['scope_type'],
                    'memory_type' => $data['memory_type'],
                    'created_by_type' => $data['created_by_type'] ?? $actorType,
                    'status' => $data['status'] ?? ($actorType === 'ai' ? MemoryStatus::Active : MemoryStatus::Draft),
                    'importance' => $data['importance'] ?? 1,
                    'embedding' => $data['embedding'] ?? null,
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

            // Access Log
            $this->logAccess(
                $isNew ? 'create' : 'update',
                $actorId,
                $actorType,
                $memory->id,
                ['title' => $memory->title]
            );

            return $memory;
        });
    }

    /**
     * Bulk create or update memories.
     */
    public function bulkWrite(array $items, string $actorId, string $actorType = 'human'): array
    {
        return DB::transaction(function () use ($items, $actorId, $actorType) {
            $results = [];
            foreach ($items as $item) {
                $results[] = $this->write($item, $actorId, $actorType);
            }

            return $results;
        });
    }

    /**
     * Link two memories.
     */
    public function linkMemories(string $sourceId, string $targetId, string $type = 'related'): void
    {
        $source = Memory::findOrFail($sourceId);
        $target = Memory::findOrFail($targetId);

        $source->relatedMemories()->syncWithoutDetaching([
            $targetId => ['relation_type' => $type],
        ]);

        // Bi-directional link for 'related' type
        if ($type === 'related') {
            $target->relatedMemories()->syncWithoutDetaching([
                $sourceId => ['relation_type' => $type],
            ]);
        }
    }

    /**
     * Perform vector search by calculating cosine similarity on the server.
     */
    public function vectorSearch(array $inputEmbedding, ?string $repository = null, array $filters = [], float $threshold = 0.5): \Illuminate\Support\Collection
    {
        // 1. Get base search results (to apply scope isolation)
        $candidates = $this->search(null, array_merge($filters, ['repository' => $repository]));

        // Access Log for Vector Search
        // We log it here because search() is called internally but we want to capture the vector aspect
        // Note: search() will also log a 'search' event, which might be double logging.
        // To avoid double logging, we could add a flag to search() or just accept it.
        // For now, logging specific vector search is useful.
        $this->logAccess('vector_search', 'system', 'ai', null, [
            'repository' => $repository,
            'filters' => $filters,
        ]);

        // 2. Calculate similarity and rank
        return $candidates->map(function (Memory $memory) use ($inputEmbedding) {
            if (! $memory->embedding) {
                $memory->similarity = 0;
                $memory->rank_score = 0;

                return $memory;
            }

            $memory->similarity = $this->cosineSimilarity($inputEmbedding, $memory->embedding);

            // Calculate a weighted rank score:
            // 60% similarity, 30% importance (scaled 0-1), 10% recency
            $importanceFactor = $memory->importance / 10;

            // Recency factor: 1.0 for now, 0.5 as it gets older (linear decay over 30 days)
            $ageInDays = $memory->created_at->diffInDays(now());
            $recencyFactor = max(0.5, 1 - ($ageInDays / 60));

            $memory->rank_score = ($memory->similarity * 0.6) + ($importanceFactor * 0.3) + ($recencyFactor * 0.1);

            return $memory;
        })
            ->filter(fn ($m) => $m->similarity >= $threshold)
            ->sortByDesc('rank_score')
            ->values();
    }

    protected function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        foreach ($vec1 as $i => $val) {
            $dotProduct += $val * ($vec2[$i] ?? 0);
            $norm1 += $val * $val;
            $norm2 += ($vec2[$i] ?? 0) * ($vec2[$i] ?? 0);
        }

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }

    public function read(string $id, ?string $actorId = null, ?string $actorType = null): Memory
    {
        $memory = Memory::with(['versions', 'auditLogs'])->findOrFail($id);

        $this->logAccess('read', $actorId, $actorType, $memory->id, ['title' => $memory->title]);

        return $memory;
    }

    /**
     * Search memories with hierarchy resolution.
     * Hierarchy: System -> Organization -> Repository -> User
     *
     * @param  string  $repositoryId  (UUID of repository)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(?string $query = null, array $filters = [])
    {
        $actorId = $filters['actor_id'] ?? auth()->id();
        $actorType = $filters['actor_type'] ?? 'human';

        $repository = $filters['repository'] ?? null;

        $this->logAccess('search', auth()->id(), 'human', null, [
            'repository' => $repository,
            'query' => $query,
            'filters' => $filters,
        ]);

        // 1. Resolve Hierarchy Context
        $orgId = $filters['organization'] ?? null;
        if (! $orgId && $repository) {
            // Try to find the repository model to get its organization
            $repoModel = \App\Models\Repository::where('id', $repository)
                ->orWhere('slug', $repository)
                ->first();

            if ($repoModel) {
                $orgId = $repoModel->organization_id;
            } elseif (str_contains($repository, '/')) {
                // Infer organization from owner/repo slug
                $orgId = explode('/', $repository)[0];
            }
        }

        $userId = $filters['user_id'] ?? $filters['user'] ?? null;

        $q = Memory::query()
            ->when(! ($repository || $userId || $orgId), function ($query) {
                $query->whereIn('status', [MemoryStatus::Active, MemoryStatus::Verified]);
            }, function ($query) {
                $query->whereIn('status', [MemoryStatus::Active, MemoryStatus::Verified, MemoryStatus::Draft]);
            });

        // 2. Apply Scope Isolation only if context is provided
        if ($repository || $userId || $orgId) {
            $q->where(function ($group) use ($repository, $userId, $orgId) {
                $group->where('scope_type', 'system')
                    ->when($orgId, fn ($q) => $q->orWhere(fn ($sub) => $sub->where('scope_type', 'organization')->where('organization', $orgId)))
                    ->when($repository, fn ($q) => $q->orWhere(fn ($sub) => $sub->where('scope_type', 'repository')->where('repository', $repository)))
                    ->unless($repository, fn ($q) => $q->orWhere('scope_type', 'repository'))
                    ->when($userId, fn ($q) => $q->orWhere(fn ($sub) => $sub->where('scope_type', 'user')
                        ->where('user_id', $userId)
                        ->when($repository, fn ($deep) => $deep->where(fn ($d) => $d->where('repository', $repository)->orWhereNull('repository')))
                    ));
            });
        }

        if ($query) {
            $isMysql = DB::connection()->getDriverName() === 'mysql';

            if ($isMysql) {
                // MySQL Full-Text search with relevance scoring
                // We use a sub-query or direct MATCH against the filtered set for performance
                $q->whereFullText(['title', 'current_content'], $query)
                    ->select('*')
                    ->selectRaw('MATCH(title, current_content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
                    ->orderByDesc('relevance');
            } else {
                // SQLite fallback using existing LIKE search
                $q->where(function ($sub) use ($query) {
                    $term = "%{$query}%";
                    $sub->whereLike('title', $term)
                        ->orWhereLike('current_content', $term)
                        ->orWhereLike('repository', $term)
                        ->orWhereLike('memory_type', $term)
                        ->orWhereLike('scope_type', $term)
                        ->orWhereLike('metadata', $term);
                });
            }
        }

        // 3. Apply Specific Filters (Post-Query but Pre-Execution)
        if (isset($filters['memory_type'])) {
            $q->where('memory_type', $filters['memory_type']);
        }

        if (isset($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        // Handle repository filter from both $repository param and $filters array
        $finalRepo = $repository ?? $filters['repository'] ?? null;
        if ($finalRepo) {
            $q->where('repository', $finalRepo);
        }

        if (isset($filters['scope_type'])) {
            $q->where('scope_type', $filters['scope_type']);
        }

        if (isset($filters['metadata']) && is_array($filters['metadata'])) {
            foreach ($filters['metadata'] as $key => $value) {
                if (is_null($value)) {
                    continue;
                }
                $q->where('metadata->'.$key, $value);
            }
        }

        // Order by Importance (desc) then Recency (desc)
        $results = $q->orderByDesc('importance')
            ->orderByDesc('created_at')
            ->get();

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

    protected function logAccess(string $action, ?string $actorId = null, ?string $actorType = null, ?string $resourceId = null, ?array $metadata = null): void
    {
        try {
            MemoryAccessLog::create([
                'actor_id' => auth()->id(),
                'actor_type' => $actorType,
                'action' => $action,
                'resource_id' => $resourceId,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            // Fail silently to not disrupt the main flow
            // Log::error('Failed to log memory access: ' . $e->getMessage());
        }
    }

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
