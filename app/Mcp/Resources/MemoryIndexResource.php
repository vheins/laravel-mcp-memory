<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Memory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class MemoryIndexResource extends Resource
{
    public function description(): string
    {
        return 'Discovery endpoint listing recent memories. Returns a JSON array of lightweight objects for topic discovery and de-duplication. NEVER contains full content.';
    }

    public function handle(Request $request): Response
    {
        // Strict field selection based on Memory Index Policy
        // forbidden: current_content
        $memories = Memory::query()
            ->latest()
            ->limit(50) // Expanded limit for better discovery since payload is lighter
            ->get()
            ->map(fn (Memory $memory): array => [
                'id' => $memory->id,
                'title' => $memory->title,
                'scope_type' => $memory->scope_type->value,
                'memory_type' => $memory->memory_type->value,
                'importance' => (int) $memory->importance,
                'status' => $memory->status->value,
                'repository' => $memory->repository,
                'organization' => $memory->organization,
                'updated_at' => $memory->updated_at->toIso8601String(),
                'metadata' => $this->filterMetadata($memory->metadata ?? []),
            ])
            ->values()
            ->all();

        // Return structured JSON data
        return Response::json($memories)
            ->withMeta(['type' => 'index', 'count' => \count($memories)]);
    }

    public function name(): string
    {
        return 'memory-index';
    }

    public function title(): string
    {
        return 'Memory Index';
    }

    public function uri(): string
    {
        return 'memory://index';
    }

    protected function filterMetadata(array $metadata): array
    {
        // Metadata Rules: short, flat key-value, max 5 keys
        $filtered = [];
        $count = 0;

        foreach ($metadata as $key => $value) {
            if ($count >= 5) {
                break;
            }

            // Only allow scalar values (string, int, bool)
            if (\is_scalar($value)) {
                // Truncate long strings just in case
                if (\is_string($value) && \strlen($value) > 50) {
                    $value = substr($value, 0, 47) . '...';
                }

                $filtered[$key] = $value;
                $count++;
            }
        }

        return $filtered;
    }
}
