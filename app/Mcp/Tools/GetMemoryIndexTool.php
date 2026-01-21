<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Memory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetMemoryIndexTool extends Tool
{
    public function description(): string
    {
        return 'Get a lightweight, metadata-only index of user memories. Use this tool FIRST to discover available memories, understand the knowledge graph structure, and find IDs before using `memory-search` (for content) or `memory-audit` (for history). Returns a list of 50 most recent memories.';
    }

    public function handle(Request $request): Response
    {
        // Logic mirrors MemoryIndexResource
        $memories = Memory::query()
            ->where('user_id', auth()->id())
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

        return Response::json($memories)
            ->withMeta(['type' => 'index', 'count' => \count($memories)]);
    }

    public function name(): string
    {
        return 'memory-index';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [], // No arguments needed for the index
        ];
    }

    /**
     * Filter metadata to remove null values and ensure it's a flat array of primitives.
     */
    protected function filterMetadata(array $metadata): array
    {
        return array_filter(
            $metadata,
            fn ($value) => ! is_null($value) && is_scalar($value)
        );
    }
}
