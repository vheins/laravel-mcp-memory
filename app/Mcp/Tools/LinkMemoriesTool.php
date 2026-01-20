<?php

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class LinkMemoriesTool extends Tool
{
    public function name(): string
    {
        return 'memory-link';
    }

    public function description(): string
    {
        return 'Create a relationship between two existing memories (Knowledge Graph).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'source_id' => $schema->string()->format('uuid')->description('Source memory UUID.')->required(),
            'target_id' => $schema->string()->format('uuid')->description('Target memory UUID.')->required(),
            'relation_type' => $schema->string()->enum(['related', 'conflicts', 'supports'])->default('related'),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $sourceId = $request->get('source_id');
        $targetId = $request->get('target_id');
        $type = $request->get('relation_type', 'related');

        try {
            $service->linkMemories($sourceId, $targetId, $type);
        } catch (\Exception $e) {
            throw new JsonRpcException($e->getMessage(), -32000, $request->get('id'));
        }

        return Response::make([
            Response::text("Memories linked successfully as '{$type}'."),
        ]);
    }
}
