<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Memory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

class MemoryHistoryResource extends Resource implements HasUriTemplate
{
    public function description(): string
    {
        return 'Retrieve full version history and audit logs for a memory. Useful for debugging or understanding how a memory evolved.';
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
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

        return Response::json([
            'versions' => $versions,
            'audit_logs' => $auditLogs,
        ])->withMeta([
            'total_versions' => $versions->count(),
            'total_logs' => $auditLogs->count(),
        ]);
    }

    public function name(): string
    {
        return 'memory-history';
    }

    public function title(): string
    {
        return 'Memory Version History';
    }

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('memory://{id}/history');
    }
}
