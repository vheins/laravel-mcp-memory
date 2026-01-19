<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Memory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class DeleteMemoryTool extends Tool
{
    public function name(): string
    {
        return 'memory.delete';
    }

    public function description(): string
    {
        return 'Soft-delete a memory entry by its UUID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->format('uuid')->description('UUID of the memory to delete.')->required(),
        ];
    }

    public function handle(Request $request)
    {
        $id = $request->get('id');
        $memory = Memory::findOrFail($id);
        $user = $request->user();

        // Basic authorization check
        if ($user && $memory->user_id !== $user->getAuthIdentifier() && $memory->scope_type === 'user') {
            throw new \Exception('Unauthorized to delete this user-scoped memory.');
        }

        $memory->delete();

        return Response::make([
            Response::text("Memory {$id} has been soft-deleted."),
        ]);
    }
}
