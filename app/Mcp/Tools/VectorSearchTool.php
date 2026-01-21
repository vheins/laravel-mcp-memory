<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

class VectorSearchTool extends Tool
{
    public function description(): string
    {
        return 'Semantic search using vector embeddings. The client must provide the vector.';
    }

    public function handle(Request $request, MemoryService $service): ResponseFactory
    {
        $vector = $request->get('vector');
        $repository = $request->get('repository');
        $filters = $request->get('filters', []);
        $threshold = (float) $request->get('threshold', 0.5);

        try {
            $results = $service->vectorSearch($vector, $repository, $filters, $threshold);
        } catch (Throwable $exception) {
            return Response::make([
                Response::text(json_encode(['error' => $exception->getMessage()])),
            ]);
        }

        // Map to lightweight locator DTOs
        $mappedResults = $results->map(fn ($memory) => [
            'id' => $memory->id,
            'title' => $memory->title,
            'score' => $memory->similarity ?? 0, // Vector similarity score
            'memory_type' => $memory->memory_type->value,
            'scope_type' => $memory->scope_type->value,
            'repository' => $memory->repository,
            'status' => $memory->status->value,
            'importance' => (int) $memory->importance,
            'current_content' => $memory->current_content,
            'metadata' => $memory->metadata,
        ])->values()->all();

        return Response::make([
            Response::text(json_encode($mappedResults)),
        ]);
    }

    public function name(): string
    {
        return 'memory-vector-search';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'vector' => $schema->array()->items($schema->number())->description('The 1536-dimensional embedding vector representing the search query.')->required(),
            'repository' => $schema->string()->description('Limit search to a specific repository slug for context isolation.'),
            'threshold' => $schema->number()->default(0.5)->description('Similarity threshold (0.0 to 1.0). Higher values return closer matches but fewer results.'),
            'filters' => $schema->object()->description('Structured filters to refine results (e.g., {"status": "active"}).'),
        ];
    }
}
