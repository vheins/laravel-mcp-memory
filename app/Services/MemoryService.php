<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Models\Memory;
use App\Models\MemoryAccessLog;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;
use App\Models\Repository;
use App\Rules\ImmutableTypeRule;
use App\Rules\PlaintextContentRule;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class MemoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Bulk create or update memories.
     */
    public function bulkWrite(array $items, string $actorId, string $actorType = 'human'): array
    {
        return DB::transaction(function () use ($items, $actorId, $actorType): array {
            $results = [];

            foreach ($items as $item) {
                $results[] = $this->write($item, $actorId, $actorType);
            }

            return $results;
        });
    }

    /**
     * Delete a memory entry.
     */
    public function delete(string $id, ?string $actorId = null, ?string $actorType = 'human'): bool
    {
        $memory = Memory::query()->findOrFail($id);
        $title = $memory->title;
        $deleted = $memory->delete();

        if ($deleted) {
            $this->logAccess('delete', $actorId, null, null, ['title' => $title]);
        }

        return $deleted;
    }

    /**
     * Link two memories.
     */
    public function linkMemories(string $sourceId, string $targetId, string $type = 'related'): void
    {
        $source = Memory::query()->findOrFail($sourceId);
        $target = Memory::query()->findOrFail($targetId);

        $source->relatedMemories()->syncWithoutDetaching([
            $targetId => ['relation_type' => $type],
        ]);

        // Bi-directional link for 'related' type
        if ($type === 'related') {
            $target->relatedMemories()->syncWithoutDetaching([
                $sourceId => ['relation_type' => $type],
            ]);
        }

        $this->logAccess('link', (string) auth()->id(), null, null, [
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'relation_type' => $type,
        ]);
    }

    public function read(string $id, ?string $actorId = null, ?string $actorType = null): Memory
    {
        $memory = Memory::with(['versions', 'auditLogs'])->findOrFail($id);

        $this->logAccess('read', $actorId, null, null, ['title' => $memory->title]);

        return $memory;
    }

    /**
     * Search memories with hierarchy resolution.
     * Hierarchy: System -> Organization -> Repository -> User.
     *
     * @param  string  $repositoryId  (UUID of repository)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(?string $query = null, array $filters = [])
    {
        $filters['actor_id'] ?? auth()->id();
        $repository = $filters['repository'] ?? null;

        // 1. Resolve Hierarchy Context
        $orgId = $filters['organization'] ?? null;

        if (! $orgId && $repository) {
            // Try to find the repository model to get its organization
            $repoModel = Repository::query()->where('id', $repository)
                ->orWhere('slug', $repository)
                ->first();

            if ($repoModel) {
                $orgId = $repoModel->organization_id;
                $repository = $repoModel->id; // Use ID for logs and subsequent steps
            } elseif (str_contains((string) $repository, '/')) {
                // Infer organization from owner/repo slug
                $orgId = explode('/', (string) $repository)[0];
            }
        }

        $q = Memory::query()
            ->disableCache()
            ->unless($repository || auth()->id() || $orgId, function ($query): void {
                $query->whereIn('status', [MemoryStatus::Active, MemoryStatus::Verified]);
            }, function ($query): void {
                $query->whereIn('status', [MemoryStatus::Active, MemoryStatus::Verified, MemoryStatus::Draft]);
            });

        // 2. Apply Scope Isolation only if context is provided
        if ($repository || auth()->id() || $orgId) {
            $q->where(function (Builder $group) use ($repository, $orgId): void {
                $group->where('scope_type', 'system')
                    ->when($orgId, fn ($q) => $q->orWhere(fn (Builder $sub) => $sub->where('scope_type', 'organization')->where('organization', $orgId)))
                    ->when($repository, fn ($q) => $q->orWhere(fn (Builder $sub) => $sub->where('scope_type', 'repository')->where('repository', $repository)))
                    ->unless($repository, fn ($q) => $q->orWhere('scope_type', 'repository'))
                    ->orWhere(fn (Builder $sub) => $sub->where('scope_type', 'user')
                        ->where('user_id', auth()->id())
                        ->when($repository, fn ($deep) => $deep->where(fn (Builder $d) => $d->where('repository', $repository)->orWhereNull('repository')))
                    );
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
                $q->where(function (Builder $sub) use ($query): void {
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

        if (isset($filters['metadata']) && \is_array($filters['metadata'])) {
            foreach ($filters['metadata'] as $key => $value) {
                if (\is_null($value)) {
                    continue;
                }

                $q->where('metadata->' . $key, $value);
            }
        }

        // Order by Importance (desc) then Recency (desc)
        $results = $q->orderByDesc('importance')->latest()
            ->get();

        $this->logAccess('search', (string) auth()->id(), $query, $filters, ['repository' => $repository], $results->count());

        return $results;
    }

    /**
     * Perform vector search by calculating cosine similarity on the server.
     */
    public function vectorSearch(array $inputEmbedding, ?string $repository = null, array $filters = [], float $threshold = 0.5): Collection
    {
        // 1. Get base search results (to apply scope isolation)
        $candidates = $this->search(null, array_merge($filters, ['repository' => $repository]));

        // search() above already logs access with the correct repository context.
        $this->logAccess('vector_search', (string) auth()->id(), null, $filters, ['repository' => $repository], $candidates->count());

        // 2. Calculate similarity and rank
        return $candidates->map(function (Memory $memory) use ($inputEmbedding): Memory {
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
            ->filter(fn ($m): bool => $m->similarity >= $threshold)
            ->sortByDesc('rank_score')
            ->values();
    }

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
                new Enum(MemoryType::class),
                new ImmutableTypeRule($actorType),
            ],
            'status' => [
                'sometimes',
                'required',
                new Enum(MemoryStatus::class),
            ],
            'scope_type' => [
                'sometimes',
                'required',
                new Enum(MemoryScope::class),
            ],
            'current_content' => [
                'required',
                'string',
                new PlaintextContentRule,
            ],
        ])->validate();

        return DB::transaction(function () use ($data, $actorId, $actorType) {
            $id = $data['id'] ?? null;
            $content = $data['current_content'];
            $isNew = false;
            $oldValue = null;

            if ($id) {
                $memory = Memory::query()->findOrFail($id);

                // Validate existing type for updates
                if ($actorType === 'ai') {
                    $rule = new ImmutableTypeRule($actorType);
                    $validator = Validator::make(['memory_type' => $memory->memory_type->value], [
                        'memory_type' => [$rule],
                    ]);
                    throw_if($validator->fails(), ValidationException::class, $validator);
                }

                $oldValue = $memory->toArray();

                // Check if locked
                throw_if($memory->status === MemoryStatus::Locked && $memory->current_content !== $content, Exception::class, 'Cannot update locked memory.');

                $memory->update([
                    'current_content' => $content,
                    'title' => $data['title'] ?? $memory->title,
                    'status' => $data['status'] ?? $memory->status,
                    'importance' => $data['importance'] ?? $memory->importance,
                    'scope_type' => $data['scope_type'] ?? $memory->scope_type,
                    'memory_type' => $data['memory_type'] ?? $memory->memory_type,
                    'embedding' => $data['embedding'] ?? $memory->embedding,
                    'metadata' => $data['metadata'] ?? $memory->metadata,
                    // We typically don't update structural keys like organization/repo/user, but if needed:
                    // 'organization' => $data['organization'] ?? $memory->organization, etc.
                ]);
            } else {
                $isNew = true;
                $memory = Memory::query()->create([
                    'id' => $data['id'] ?? Str::uuid()->toString(),
                    'organization' => $data['organization'] ?? 'default',
                    'repository' => $data['repository'] ?? null,
                    'title' => $data['title'] ?? null,
                    'user_id' => auth()->id(),
                    'scope_type' => $data['scope_type'] ?? MemoryScope::Repository->value,
                    'memory_type' => $data['memory_type'] ?? MemoryType::Fact->value,
                    'created_by_type' => $data['created_by_type'] ?? $actorType,
                    'status' => $data['status'] ?? ($actorType === 'ai' ? MemoryStatus::Active : MemoryStatus::Draft),
                    'importance' => $data['importance'] ?? 1,
                    'embedding' => $data['embedding'] ?? null,
                    'current_content' => $content,
                    'metadata' => $data['metadata'] ?? null,
                ]);
            }

            // Create Version
            MemoryVersion::query()->create([
                'memory_id' => $memory->id,
                'version_number' => $memory->versions()->max('version_number') + 1,
                'content' => $content,
                'created_by' => $actorId,
                'input_source' => $actorType,
            ]);

            // Audit Log
            MemoryAuditLog::query()->create([
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
                null,
                null,
                ['title' => $memory->title]
            );

            return $memory;
        });
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

    protected function createVersion(Memory $memory, string $content): void
    {
        // Determine next version number
        $latestVersion = $memory->versions()->max('version_number') ?? 0;

        $memory->versions()->create([
            'version_number' => $latestVersion + 1,
            'content' => $content,
        ]);
    }

    protected function logAccess(string $action, ?string $actorId = null, ?string $query = null, ?array $filters = null, ?array $metadata = null, ?int $resultCount = null): void
    {
        try {
            MemoryAccessLog::query()->create([
                'actor_id' => $actorId ?? auth()->id(),
                'action' => $action,
                'query' => $query,
                'filters' => $filters,
                'metadata' => $metadata,
                'result_count' => $resultCount,
            ]);
        } catch (Exception) {
            // Fail silently
        }
    }
}
