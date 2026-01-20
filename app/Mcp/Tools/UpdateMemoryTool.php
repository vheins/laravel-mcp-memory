<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class UpdateMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory-update';
    }

    public function description(): string
    {
        return 'Update an existing memory entry by its UUID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('UUID of the memory to update.')->required(),
            'title' => $schema->string()->description('Updated brief summary of the memory.'),
            'current_content' => $schema->string()->description('Updated text content.'),
            'status' => $schema->string()->enum(['draft', 'published', 'locked']),
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
