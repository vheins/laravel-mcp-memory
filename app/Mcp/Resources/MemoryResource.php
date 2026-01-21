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
        return 'Read a specific memory entry by its UUID.';
    }

    public function handle(Request $request): Response
    {
        // When using UriTemplate, the variables are merged into the request
        $id = $request->get('id');
        $memory = Memory::query()->findOrFail($id);

        return Response::text($memory->current_content)
            ->withMeta([
                'type' => $memory->memory_type,
                'status' => $memory->status,
                'organization' => $memory->organization,
                'repository' => $memory->repository,
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
