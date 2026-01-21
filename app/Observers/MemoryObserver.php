<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Memory;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;

class MemoryObserver
{
    /**
     * Handle the Memory "created" event.
     */
    public function created(Memory $memory): void
    {
        $this->createVersion($memory);
        $this->createAuditLog($memory, 'create', null);
    }

    /**
     * Handle the Memory "updated" event.
     */
    public function updated(Memory $memory): void
    {
        // Only create version if content changed
        if ($memory->wasChanged('current_content')) {
            $this->createVersion($memory);
        }

        $this->createAuditLog($memory, 'update', $memory->getOriginal());
    }

    /**
     * Handle the Memory "deleted" event.
     */
    public function deleted(Memory $memory): void
    {
        $this->createAuditLog($memory, 'delete', $memory->getOriginal());
    }

    protected function createVersion(Memory $memory): void
    {
        $actorId = $memory->audit_actor_id ?? auth()->id() ?? '00000000-0000-0000-0000-000000000000';
        $actorType = $memory->audit_actor_type ?? (auth()->check() ? 'human' : 'system');

        // Ensure actor type is valid
        if (! in_array($actorType, ['human', 'ai', 'system'])) {
            $actorType = 'system';
        }

        // Determine next version number
        $latestVersion = $memory->versions()->max('version_number') ?? 0;

        MemoryVersion::query()->create([
            'memory_id' => $memory->id,
            'version_number' => $latestVersion + 1,
            'content' => $memory->current_content,
            'created_by' => $actorId,
            'input_source' => $actorType,
        ]);
    }

    protected function createAuditLog(Memory $memory, string $event, ?array $oldValue): void
    {
        $actorId = $memory->audit_actor_id ?? auth()->id() ?? '00000000-0000-0000-0000-000000000000';
        $actorType = $memory->audit_actor_type ?? (auth()->check() ? 'human' : 'system');

        // Ensure actor type is valid
        if (! in_array($actorType, ['human', 'ai', 'system'])) {
            $actorType = 'system';
        }

        MemoryAuditLog::query()->create([
            'memory_id' => $memory->id,
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'event' => $event,
            'old_value' => $oldValue,
            'new_value' => $event === 'delete' ? null : $memory->toArray(),
        ]);
    }
}
