<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
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
            'repository' => $schema->string()->description('Repository slug to search within.')->required(),
            'query' => $schema->string()->description('Text query to match against content.'),
            'filters' => $schema->object([
                'user_id' => $schema->string()->description('Optional filter for a specific user ID.'),
                'memory_type' => $schema->string()->enum(['fact', 'preference', 'business_rule', 'system_constraint']),
                'status' => $schema->string()->enum(['draft', 'published', 'locked']),
            ]),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $filters = $request->get('filters', []);

        if ($user = $request->user()) {
            $filters['user_id'] = $user->getAuthIdentifier();
        }

        $results = $service->search(
            $request->get('repository'),
            $request->get('query'),
            $filters
        );

        return Response::make([
            Response::text(json_encode($results->toArray())),
        ]);
    }
}
