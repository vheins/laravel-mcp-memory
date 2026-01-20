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
            'id' => $schema->string()->format('uuid')->description('The unique UUID of the memory entry you wish to update.')->required(),
            'title' => $schema->string()->description('A new summary title for the memory. optional.'),
            'current_content' => $schema->string()->description('The new text content. Replaces the existing content entirely.'),
            'status' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value'))
                ->description('Update the status (e.g., promote "draft" to "active" after verification).'),
            'scope_type' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryScope::cases(), 'value'))
                ->description('Change the visibility scope (e.g., move from "user" to "organization" for shared knowledge).'),
            'memory_type' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryType::cases(), 'value'))
                ->description('Reclassify the memory type (e.g., from "fact" to "business_rule").'),
            'importance' => $schema->number()->min(1)->max(10)->description('Adjust the priority level (1-10). Higher importance boosts vector search ranking.'),
            'metadata' => $schema->object()->description('Merge or replace metadata keys. Provide the complete object if replacing.'),
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
