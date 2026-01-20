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
            'status' => $schema->string()->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value')),
            'metadata' => $schema->object()->description('Additional key-value pairs.'),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $arguments = $request->all();
        $arguments['user_id'] = auth()->id();

        $actorId = (string) (auth()->id() ?? 'system');
        $actorType = request()->is('api/*') ? 'ai' : 'human';

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
