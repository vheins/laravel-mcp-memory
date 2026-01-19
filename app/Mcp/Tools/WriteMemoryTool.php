<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class WriteMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory.write';
    }

    public function description(): string
    {
        return 'Create or update a memory entry. Supports facts, preferences, and business rules.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('UUID of the memory to update (leave empty for new entry).'),
            'organization' => $schema->string()->description('Organization name/slug.')->required(),
            'repository' => $schema->string()->description('Repository name/slug.'),
            'scope_type' => $schema->string()->enum(['system', 'repository', 'user'])->description('Visibility scope of the memory.')->required(),
            'memory_type' => $schema->string()->enum(['fact', 'preference', 'business_rule', 'system_constraint'])->description('Categorization of the memory.')->required(),
            'current_content' => $schema->string()->description('The text content to store.')->required(),
            'status' => $schema->string()->enum(['draft', 'published', 'locked'])->default('draft'),
            'metadata' => $schema->object()->description('Additional key-value pairs.'),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $arguments = $request->all();
        $user = $request->user();

        if ($user) {
            $arguments['user_id'] = $user->getAuthIdentifier();
        }

        $actorId = (string) ($user ? $user->getAuthIdentifier() : 'system');
        $actorType = $user ? 'human' : 'ai';

        $memory = $service->write($arguments, $actorId, $actorType);

        return Response::make([
            Response::text(json_encode($memory->toArray())),
        ]);
    }
}
