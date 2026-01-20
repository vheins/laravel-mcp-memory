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
            'query' => $schema->string()->description('The search query string. Use specific keywords to pinpoint relevant memories.'),
            'filters' => $schema->object([
                'repository' => $schema->string()->description('The specific repository to restrict the search to. Omit to search across all accessible repositories.'),
                'memory_type' => $schema->string()
                    ->enum(array_column(MemoryType::cases(), 'value'))
                    ->description('Filter by a specific memory category (e.g., "business_rule" for logic, "system_constraint" for immutable rules).'),
                'status' => $schema->string()
                    ->enum(array_column(MemoryStatus::cases(), 'value'))
                    ->description('Filter by memory status (e.g., "active" for current knowledge, "draft" for works in progress).'),
                'scope_type' => $schema->string()
                    ->enum(array_column(MemoryScope::cases(), 'value'))
                    ->description('Filter by scope (e.g., "system" for global rules, "organization" for team knowledge).'),
                'metadata' => $schema->object()
                    ->description('A JSON object to match against strict key-value pairs in the metadata column. Useful for tag-based filtering.'),
            ])->description('Optional filters to narrow down the search results.'),
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
