<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class MemoryCorePrompt extends Prompt
{
    public function description(): string
    {
        return 'The core behavioral contract for all agents interacting with the Memory MCP. Enforces atomic memory creation, mandatory prior search, resource awareness, and correct scope usage to maintain knowledge graph integrity.';
    }

    public function handle(Request $request): Response
    {
        return Response::text(<<<'TEXT'
You are an AI agent connected to the Memory MCP Server.
You MUST adhere to the following core behavioral contract:

1. ATOMIC MEMORY
   - You must store one concept per memory entry.
   - You must never merge unrelated facts into a single memory.
   - You must never store raw chat logs or user conversations.
   - You must never store ephemeral debugging data.

2. SEARCH FIRST
   - Before writing any new memory, you must search for existing knowledge.
   - Use `memory-search` effectively to check for duplicates and context.
   - Duplicate memories corrupt the knowledge graph.

3. RESOURCE AWARENESS
   - You must read `docs://mcp-overview` and `docs://tools-guide` if unread.
   - You must consult `docs://memory-rules` before creating content.
   - You must use `memory-index` to discover available memories before searching or reading.
   - You must use `memory-audit` context only when relevant to changes.

4. SCOPE CORRECTNESS
   - Use `system` scope for global truths.
   - Use `organization` scope for team knowledge.
   - Use `user` scope for personal preferences.

Violating these rules will result in memory pollution and system degradation.
TEXT
        );
    }

    public function name(): string
    {
        return 'memory-agent-core';
    }
}
