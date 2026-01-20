<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchMemoriesTool extends Tool
{
    public function name(): string
    {
        return 'memory-search';
    }

    public function description(): string
    {
        return 'Search for memories with hierarchical resolution and filtering.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Text query to match against content.'),
            'filters' => $schema->object([
                'repository' => $schema->string()->description('Repository slug to search within.'),
                'memory_type' => $schema->string()->enum(array_column(MemoryType::cases(), 'value')),
                'status' => $schema->string()->enum(array_column(MemoryStatus::cases(), 'value')),
                'scope_type' => $schema->string()->enum(array_column(MemoryScope::cases(), 'value')),
                'metadata' => $schema->object()->description('Key-value pairs to filter by in metadata.'),
            ]),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $filters = $request->get('filters', []);

        $results = $service->search(
            $request->get('query'),
            $filters
        );

        return Response::make([
            Response::text(json_encode($results->toArray())),
        ]);
    }
}
