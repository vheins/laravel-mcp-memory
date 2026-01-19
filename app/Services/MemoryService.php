<?php

namespace App\Services;

use App\Models\Memory;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data, $actorId, $actorType) {
            $id = $data['id'] ?? null;
            $content = $data['current_content'];
            $isNew = false;
            $oldValue = null;

            if ($id) {
                $memory = Memory::findOrFail($id);
                $oldValue = $memory->toArray();

                // Update
                $memory->fill(Arr::except($data, ['id']));

                // Observer will handle locked check, but we can double check here if needed
                if ($memory->isDirty('current_content')) {
                    $memory->save();
                    $this->createVersion($memory, $content);
                    $this->createAuditLog($memory, $actorId, $actorType, 'updated', $oldValue, $memory->toArray());
                } else {
                    $memory->save(); // Save other fields if any
                    // If no content change, maybe just 'updated' event without version?
                    // Review says: "Setiap perubahan pada current_content ... wajib menciptakan record baru di memory_versions"
                    // If content not changed, no new version.
                    if ($memory->wasChanged()) {
                        $this->createAuditLog($memory, $actorId, $actorType, 'updated', $oldValue, $memory->toArray());
                    }
                }
            } else {
                $isNew = true;
                // Create
                $memory = Memory::create($data);
                $this->createVersion($memory, $content);
                $this->createAuditLog($memory, $actorId, $actorType, 'created', null, $memory->toArray());
            }

            return $memory->fresh(['versions', 'auditLogs']);
        });
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
