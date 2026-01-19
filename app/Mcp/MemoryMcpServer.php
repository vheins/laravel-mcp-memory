<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Mcp\Resources\MemoryHistoryResource;
use App\Mcp\Resources\MemoryResource;
use App\Mcp\Tools\DeleteMemoryTool;
use App\Mcp\Tools\SearchMemoriesTool;
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
        DeleteMemoryTool::class,
        SearchMemoriesTool::class,
    ];

    protected array $resources = [
        MemoryResource::class,
        MemoryHistoryResource::class,
    ];
}
