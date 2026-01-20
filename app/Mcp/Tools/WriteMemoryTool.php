<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\MemoryService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Exceptions\JsonRpcException;
use Laravel\Mcp\Server\Tool;

class WriteMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory-write';
    }

    public function description(): string
    {
        return 'Create a new memory entry. Supports facts, preferences, and business rules.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('UUID of the memory to update. Leave this field empty if you are creating a NEW memory entry.'),
            'organization' => $schema->string()->description('The organization slug to which this memory belongs (e.g., "my-org"). Required for validation.')->required(),
            'repository' => $schema->string()->description('The specific repository slug (e.g., "frontend-repo") if this memory is project-specific.'),
            'title' => $schema->string()->description('A concise summary of the memory content, used for quick identification.'),
            'scope_type' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryScope::cases(), 'value'))
                ->description('The visibility scope: "system" for global rules, "organization" for team-wide knowledge, or "user" for private context.')
                ->required(),
            'memory_type' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryType::cases(), 'value'))
                ->description('The category of the memory: "business_rule", "preference", "fact", "system_constraint", etc.')
                ->required(),
            'current_content' => $schema->string()->description('The actual content of the memory. Be precise and concise.')->required(),
            'status' => $schema->string()
                ->enum(array_column(\App\Enums\MemoryStatus::cases(), 'value'))
                ->default(\App\Enums\MemoryStatus::Draft->value)
                ->description('The lifecycle status: "draft" (default), "active" (verified), or "archived".'),
            'metadata' => $schema->object()->description('Arbitrary JSON key-value pairs for additional context or tagging (e.g., {"tags": ["ui", "v2"]}).'),
        ];
    }

    public function handle(Request $request, MemoryService $service)
    {
        $arguments = $request->all();
        $arguments['user_id'] = auth()->id();

        $actorId = (string) (auth()->id() ?? 'system');
        $actorType = request()->is('api/*') ? 'ai' : 'human';

        try {
            if ($actorType === 'ai' && ($arguments['memory_type'] ?? '') === 'system_constraint') {
                throw new \Exception('AI agents cannot create system constraints.');
            }

            $memory = $service->write($arguments, $actorId, $actorType);
        } catch (\Exception $e) {
            throw new JsonRpcException($e->getMessage(), -32000, $request->get('id'));
        }

        return Response::make([
            Response::text(json_encode($memory->toArray())),
        ]);
    }
}
