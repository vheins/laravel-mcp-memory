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
    public function name(): string
    {
        return 'memory-history';
    }

    public function title(): string
    {
        return 'Memory Version History';
    }

    public function description(): string
    {
        return 'Retrieve all versions and audit logs for a specific memory.';
    }

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('memory://{id}/history');
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $memory = Memory::with(['versions', 'auditLogs'])->findOrFail($id);

        return Response::json([
            'versions' => $memory->versions->toArray(),
            'audit_logs' => $memory->auditLogs->toArray(),
        ]);
    }
}
