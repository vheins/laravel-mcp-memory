<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

class BatchWriteMemoryTool extends Tool
{
    public function description(): string
    {
        return 'Create or update multiple memory entries in a single batch operation. Use this for bulk imports, migrations, or when processing large datasets to ensure atomicity and consistency. Significantly more efficient than individual calls.';
    }

    public function handle(Request $request, MemoryService $service): ResponseFactory
    {
        $items = $request->get('items');
        $actorId = (string) (auth()->id() ?? 'system');
        $actorType = request()->is('api/*') ? 'ai' : 'human';

        try {
            $memories = $service->bulkWrite($items, $actorId, $actorType);
        } catch (ValidationException $exception) {
            return Response::make([
                Response::error(json_encode($exception->errors(), JSON_UNESCAPED_UNICODE)),
            ]);
        } catch (Throwable $exception) {
            return Response::make([
                Response::error(json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE)),
            ]);
        }

        return Response::make([
            Response::text(json_encode(array_map(fn ($m) => $m->toArray(), $memories), JSON_UNESCAPED_UNICODE)),
        ]);
    }

    public function name(): string
    {
        return 'memory-bulk-write';
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
                        ->enum(array_column(MemoryScope::cases(), 'value'))
                        ->description('Visibility: "system", "organization", or "user".'),
                    'memory_type' => $schema->string()
                        ->enum(array_column(MemoryType::cases(), 'value'))
                        ->description('Type: "business_rule", "fact", "preference", etc.'),
                    'title' => $schema->string()->description('A short, descriptive title.'),
                    'current_content' => $schema->string()->description('The main content of the memory.')->required(),
                    'status' => $schema->string()
                        ->enum(array_column(MemoryStatus::cases(), 'value'))
                        ->description('Status: "draft", "active", "archived".'),
                    'importance' => $schema->number()->min(1)->max(10)->description('Priority level (1-10).'),
                    'metadata' => $schema->object()->description('Custom key-value pairs.'),
                ])->description('A memory object to create or update.')
            )->description('List of memory objects to process in batch.')->required(),
        ];
    }
}
