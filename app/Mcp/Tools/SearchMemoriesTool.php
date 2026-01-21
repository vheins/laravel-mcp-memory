<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

class SearchMemoriesTool extends Tool
{
    public function description(): string
    {
        return 'Search for memories using full-text search with fuzzy matching and filtering. Results are ranked by relevance and importance. Returns content, metadata, and relationships to provide full context.';
    }

    public function handle(Request $request, MemoryService $service): ResponseFactory
    {
        $filters = $request->get('filters', []);
        $inputQueries = $request->get('queries', []);
        $singleQuery = $request->get('query');

        if (\is_string($inputQueries)) {
            $inputQueries = [$inputQueries];
        }

        if ($singleQuery) {
            $inputQueries[] = $singleQuery;
        }

        $inputQueries = array_unique(array_filter($inputQueries));
        $allResults = new \Illuminate\Database\Eloquent\Collection;

        try {
            if (empty($inputQueries)) {
                // If no query provided, just run search once with null to respect filters
                $allResults = $service->search(null, $filters);
            } else {
                foreach ($inputQueries as $query) {
                    $results = $service->search($query, $filters);
                    $allResults = $allResults->merge($results);
                }
            }
        } catch (Throwable $exception) {
            return Response::make([
                Response::error(json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE)),
            ]);
        }

        // Deduplicate by ID
        $allResults = $allResults->unique('id')->values();

        // Map to lightweight locator DTOs
        $mappedResults = $allResults->map(fn ($memory) => [
            'id' => $memory->id,
            'title' => $memory->title,
            'score' => $memory->relevance ?? null, // MySQL fulltext score if available
            'memory_type' => $memory->memory_type->value,
            'scope_type' => $memory->scope_type->value,
            'repository' => $memory->repository,
            'status' => $memory->status->value,
            'importance' => (int) $memory->importance,
            'created_at' => $memory->created_at->toIso8601String(),
            'current_content' => $memory->current_content,
            'metadata' => $memory->metadata,
            // Creator and viewer names
            'creator_name' => $memory->userRel?->name ?? null,
            'viewer_name' => $request->user()?->name ?? null,
        ])->all();

        return Response::make([
            Response::text(json_encode($mappedResults, JSON_UNESCAPED_UNICODE)),
        ]);
    }

    public function name(): string
    {
        return 'memory-search';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The search query string. Use specific keywords to pinpoint relevant memories.'),
            'queries' => $schema->array()->items($schema->string())->description('Array of search queries or a single string queries. Used to perform multiple searches and merge results.'),
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
}
