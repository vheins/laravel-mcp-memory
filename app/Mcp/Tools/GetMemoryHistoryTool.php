<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Memory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

class GetMemoryHistoryTool extends Tool
{
    public function description(): string
    {
        return 'Retrieve full version history and audit logs for a memory by its ID.';
    }

    public function handle(Request $request): ResponseFactory
    {
        $id = $request->get('id');

        try {
            $memory = Memory::with(['versions', 'auditLogs'])->findOrFail($id);

            $versions = $memory->versions
                ->sortByDesc('version_number')
                ->values()
                ->map(fn ($v) => [
                    'version' => $v->version_number,
                    'created_at' => $v->created_at->toIso8601String(),
                    'content' => $v->content,
                ]);

            $auditLogs = $memory->auditLogs
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($log) => [
                    'event' => $log->event,
                    'actor' => "{$log->actor_type}:{$log->actor_id}",
                    'created_at' => $log->created_at->toIso8601String(),
                    'changes' => [
                        'old' => $log->old_value,
                        'new' => $log->new_value,
                    ],
                ]);

            return Response::make([
                Response::text(json_encode([
                    'id' => $memory->id,
                    'title' => $memory->title,
                    'versions' => $versions,
                    'audit_logs' => $auditLogs,
                    'meta' => [
                        'total_versions' => $versions->count(),
                        'total_logs' => $auditLogs->count(),
                    ],
                ], JSON_UNESCAPED_UNICODE)),
            ]);

        } catch (Throwable $exception) {
            return Response::make([
                Response::error(json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE)),
            ]);
        }
    }

    public function name(): string
    {
        return 'memory-history';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('The UUID of the memory to fetch history for.'),
        ];
    }
}
