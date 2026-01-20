<?php

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class BatchWriteMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory-bulk-write';
    }

    public function description(): string
    {
        return 'Create or update multiple memory entries in a single batch.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'items' => $schema->array()->items(
                $schema->object([
                    'id' => $schema->string()->format('uuid')->description('Optional UUID for update.'),
                    'organization' => $schema->string()->description('Organization ID (required for new).'),
                    'repository' => $schema->string()->description('Optional repository context.'),
                    'scope_type' => $schema->string()->enum(array_column(\App\Enums\MemoryScope::cases(), 'value')),
                    'memory_type' => $schema->string()->enum(array_column(\App\Enums\MemoryType::cases(), 'value')),
                    'title' => $schema->string(),
                    'current_content' => $schema->string()->required(),
                    'status' => $schema->string()->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value')),
                    'importance' => $schema->number()->min(1)->max(10),
                    'metadata' => $schema->object(),
                ])
            )->required(),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $items = $request->get('items');
        $user = $request->user();
        $actorId = (string) ($user ? $user->getAuthIdentifier() : 'system');
        $actorType = $user ? 'human' : 'ai';

        try {
            $memories = $service->bulkWrite($items, $actorId, $actorType);
        } catch (\Exception $e) {
            throw new JsonRpcException($e->getMessage(), -32000, $request->get('id'));
        }

        return Response::make([
            Response::text(json_encode(array_map(fn ($m) => $m->toArray(), $memories))),
        ]);
    }
}
