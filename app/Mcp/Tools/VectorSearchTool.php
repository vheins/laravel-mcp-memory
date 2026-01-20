<?php

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class VectorSearchTool extends Tool
{
    public function name(): string
    {
        return 'memory-vector-search';
    }

    public function description(): string
    {
        return 'Semantic search using vector embeddings. The client must provide the vector.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'vector' => $schema->array()->items($schema->number())->description('Input embedding vector.')->required(),
            'repository' => $schema->string()->description('Optional repository context.'),
            'threshold' => $schema->number()->default(0.5)->description('Similarity threshold (0-1).'),
            'filters' => $schema->object()->description('Optional filters (organization, status, etc.).'),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $vector = $request->get('vector');
        $repository = $request->get('repository');
        $filters = $request->get('filters', []);
        $threshold = (float) $request->get('threshold', 0.5);

        try {
            $results = $service->vectorSearch($vector, $repository, $filters, $threshold);
        } catch (\Exception $e) {
            throw new JsonRpcException($e->getMessage(), -32000, $request->get('id'));
        }

        return Response::make([
            Response::text(json_encode($results->toArray())),
        ]);
    }
}
