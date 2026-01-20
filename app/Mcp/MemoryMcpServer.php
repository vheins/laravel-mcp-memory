<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Mcp\Resources\MemoryHistoryResource;
use App\Mcp\Resources\MemoryResource;
use App\Mcp\Resources\SchemaResource;
use App\Mcp\Tools\BatchWriteMemoryTool;
use App\Mcp\Tools\DeleteMemoryTool;
use App\Mcp\Tools\LinkMemoriesTool;
use App\Mcp\Tools\SearchMemoriesTool;
use App\Mcp\Tools\UpdateMemoryTool;
use App\Mcp\Tools\VectorSearchTool;
use App\Mcp\Tools\WriteMemoryTool;
use Laravel\Mcp\Server;

class MemoryMcpServer extends Server
{
    protected string $name = 'Memory MCP Server';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        This server manages structured memories (facts, preferences, rules) for the application.
        It supports hierarchical searching and versioning.
    MARKDOWN;

    protected array $tools = [
        WriteMemoryTool::class,
        UpdateMemoryTool::class,
        DeleteMemoryTool::class,
        SearchMemoriesTool::class,
        BatchWriteMemoryTool::class,
        LinkMemoriesTool::class,
        VectorSearchTool::class,
    ];

    protected array $resources = [
        MemoryResource::class,
        MemoryHistoryResource::class,
        SchemaResource::class,
        Resources\DocsResource::class,
        Resources\MemoryIndexResource::class,
    ];
}
