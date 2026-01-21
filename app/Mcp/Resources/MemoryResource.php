<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Memory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

class MemoryResource extends Resource implements HasUriTemplate
{
    public function description(): string
    {
        return 'Read access to memory content. Use this to retrieve full details of a specific memory (knowledge) after discovering it via `memory-index` or `memory-search`. Represents the core knowledge unit.';
    }

    public function handle(Request $request): Response
    {
        // When using UriTemplate, the variables are merged into the request
        $id = $request->get('id');
        $memory = Memory::query()->findOrFail($id);

        return Response::json([
            'id' => $memory->id,
            'title' => $memory->title,
            'content' => $memory->current_content,
            'type' => $memory->memory_type->value,
            'scope' => $memory->scope_type->value,
            'status' => $memory->status->value,
            'importance' => $memory->importance,
            'context' => [
                'organization' => $memory->organization,
                'repository' => $memory->repository,
                'user_id' => $memory->user_id,
                'created_by' => $memory->created_by_type,
            ],
            'metadata' => $memory->metadata,
            'dates' => [
                'created_at' => $memory->created_at->toIso8601String(),
                'updated_at' => $memory->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function name(): string
    {
        return 'memory';
    }

    public function title(): string
    {
        return 'Individual Memory';
    }

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('memory://{id}');
    }
}
