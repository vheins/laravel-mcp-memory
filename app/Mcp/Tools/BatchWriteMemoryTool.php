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
                    'id' => $schema->string()->format('uuid')->description('UUID for updating an existing memory. Omit for creating a new one.'),
                    'organization' => $schema->string()->description('The organization slug (required for new memories).'),
                    'repository' => $schema->string()->description('The repository slug to associate with the memory.'),
                    'scope_type' => $schema->string()
                        ->enum(array_column(\App\Enums\MemoryScope::cases(), 'value'))
                        ->description('Visibility: "system", "organization", or "user".'),
                    'memory_type' => $schema->string()
                        ->enum(array_column(\App\Enums\MemoryType::cases(), 'value'))
                        ->description('Type: "business_rule", "fact", "preference", etc.'),
                    'title' => $schema->string()->description('A short, descriptive title.'),
                    'current_content' => $schema->string()->description('The main content of the memory.')->required(),
                    'status' => $schema->string()
                        ->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value'))
                        ->description('Status: "draft", "active", "archived".'),
                    'importance' => $schema->number()->min(1)->max(10)->description('Priority level (1-10).'),
                    'metadata' => $schema->object()->description('Custom key-value pairs.'),
                ])->description('A memory object to create or update.')
            )->description('List of memory objects to process in batch.')->required(),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $items = $request->get('items');
        $actorId = (string) (auth()->id() ?? 'system');
        $actorType = request()->is('api/*') ? 'ai' : 'human';

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
