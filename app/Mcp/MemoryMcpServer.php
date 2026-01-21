<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Mcp\Prompts\MemoryCorePrompt;
use App\Mcp\Prompts\MemoryIndexPolicyPrompt;
use App\Mcp\Prompts\ToolUsagePrompt;
use App\Mcp\Resources\DocsResource;
use App\Mcp\Resources\MemoryAuditResource;
use App\Mcp\Resources\MemoryIndexResource;
use App\Mcp\Resources\MemoryResource;
use App\Mcp\Resources\SchemaResource;
use App\Mcp\Tools\BatchWriteMemoryTool;
use App\Mcp\Tools\DeleteMemoryTool;
use App\Mcp\Tools\GetMemoryAuditTool;
use App\Mcp\Tools\GetMemoryIndexTool;
use App\Mcp\Tools\LinkMemoriesTool;
use App\Mcp\Tools\SearchMemoriesTool;
use App\Mcp\Tools\UpdateMemoryTool;
use App\Mcp\Tools\VectorSearchTool;
use App\Mcp\Tools\WriteMemoryTool;
use Laravel\Mcp\Server;

class MemoryMcpServer extends Server
{
    protected string $instructions = <<<'MARKDOWN'
        This server manages structured memories (facts, preferences, rules) for the application.
        It supports hierarchical searching and versioning.
    MARKDOWN;

    protected string $name = 'Memory MCP Server';

    /**
     * @var array<int, class-string<MemoryCorePrompt>|class-string<MemoryIndexPolicyPrompt>|class-string<ToolUsagePrompt>>
     */
    protected array $prompts = [
        MemoryCorePrompt::class,
        MemoryIndexPolicyPrompt::class,
        ToolUsagePrompt::class,
    ];

    /**
     * @var array<int, class-string<MemoryResource>|class-string<MemoryAuditResource>|class-string<SchemaResource>|class-string<DocsResource>|class-string<MemoryIndexResource>>
     */
    protected array $resources = [
        MemoryResource::class,
        MemoryAuditResource::class,
        SchemaResource::class,
        DocsResource::class,
        MemoryIndexResource::class,
    ];

    /**
     * @var array<int, class-string<WriteMemoryTool>|class-string<UpdateMemoryTool>|class-string<DeleteMemoryTool>|class-string<SearchMemoriesTool>|class-string<BatchWriteMemoryTool>|class-string<LinkMemoriesTool>|class-string<VectorSearchTool>>
     */
    protected array $tools = [
        WriteMemoryTool::class,
        UpdateMemoryTool::class,
        DeleteMemoryTool::class,
        SearchMemoriesTool::class,
        BatchWriteMemoryTool::class,
        LinkMemoriesTool::class,
        VectorSearchTool::class,
        GetMemoryAuditTool::class,
        GetMemoryIndexTool::class,
    ];

    protected string $version = '1.0.0';
}
