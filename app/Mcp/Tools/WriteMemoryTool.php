<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class WriteMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory-write';
    }

    public function description(): string
    {
        return 'Create a new memory entry. Supports facts, preferences, and business rules.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('UUID of the memory to update (leave empty for new entry).'),
            'organization' => $schema->string()->description('Organization name/slug.')->required(),
            'repository' => $schema->string()->description('Repository name/slug.'),
            'title' => $schema->string()->description('Brief summary of the memory.'),
            'scope_type' => $schema->string()->enum(array_column(\App\Enums\MemoryScope::cases(), 'value'))->description('Visibility scope of the memory.')->required(),
            'memory_type' => $schema->string()->enum(array_column(\App\Enums\MemoryType::cases(), 'value'))->description('Categorization of the memory.')->required(),
            'current_content' => $schema->string()->description('The text content to store.')->required(),
            'status' => $schema->string()->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value'))->default(\App\Enums\MemoryStatus::Draft->value),
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

        try {
            $memory = $service->write($arguments, $actorId, $actorType);
        } catch (\Exception $e) {
            throw new JsonRpcException($e->getMessage(), -32000, $request->get('id'));
        }

        return Response::make([
            Response::text(json_encode($memory->toArray())),
        ]);
    }
}
