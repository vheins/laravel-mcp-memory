<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Memory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class MemoryIndexResource extends Resource
{
    public function name(): string
    {
        return 'memory-index';
    }

    public function uri(): string
    {
        return 'memory://index';
    }

    public function title(): string
    {
        return 'Memory Index';
    }

    public function description(): string
    {
        return 'Discovery endpoint listing recent memories to help agents explore the knowledge base.';
    }

    public function handle(Request $request): Response
    {
        // Fetch recent memories to give the agent a "glance" at what's in the system
        // Limit to 20 to avoid overwhelming the context window
        $recentMemories = Memory::query()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Memory $memory) => sprintf(
                "- [%s] %s (Type: %s, Scope: %s, ID: %s)",
                $memory->created_at->format('Y-m-d'),
                $memory->title,
                $memory->memory_type->value,
                $memory->scope_type->value,
                $memory->id
            ))
            ->implode("\n");

        $content = "Here are the most recent memories stored in the system (up to 20):\n\n" .
                   ($recentMemories ?: "No memories found.");

        return Response::text($content)->withMeta(['type' => 'index']);
    }
}
