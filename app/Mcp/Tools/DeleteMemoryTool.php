<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Memory;
use Exception;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class DeleteMemoryTool extends Tool
{
    public function description(): string
    {
        return 'Soft-delete a memory entry by its UUID.';
    }

    public function handle(Request $request): ResponseFactory
    {
        $id = $request->get('id');
        $memory = Memory::query()->findOrFail($id);
        $user = $request->user();

        // Basic authorization check
        throw_if($user && $memory->user_id !== $user->getAuthIdentifier() && $memory->scope_type === 'user', Exception::class, 'Unauthorized to delete this user-scoped memory.');

        $memory->delete();

        return Response::make([
            Response::text("Memory {$id} has been soft-deleted."),
        ]);
    }

    public function name(): string
    {
        return 'memory-delete';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('The UUID of the memory entry to perform a soft-delete on.')->required(),
        ];
    }
}
