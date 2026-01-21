<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class ToolUsagePrompt extends Prompt
{
    public function description(): string
    {
        return 'Strict guidelines on when to use (and when NOT to use) each MCP tool.';
    }

    public function handle(Request $request): Response
    {
        return Response::text(<<<'TEXT'
TOOL USAGE GUIDELINES

1. memory-write
   - USE WHEN: A completely new fact/rule is discovered.
   - DO NOT USE: To update existing memories. To store chat logs.
   - REQUIREMENT: Must read `docs://memory-rules` first.

2. memory-update
   - USE WHEN: Refining existing knowledge or fixing errors.
   - DO NOT USE: If ID is unknown.
   - REQUIREMENT: Must preserve the atomic nature of the memory.

3. memory-search
   - USE WHEN: You need to FIND relevant memories.
   - OUTPUT: Returns content and metadata.
   - REQUIREMENT 1: Use specific keywords to limit noise.
   - REQUIREMENT 2: Verify the content is relevant before using it.

4. memory-delete
   - USE WHEN: Information is strictly invalid or completely obsolete.
   - CAUTION: Destructive action.

5. memory-batch-write
   - USE WHEN: Importing multiple distinct atomic facts from a single session.
   - REQUIREMENT: All entries must follow atomic validation separately.

6. memory-link
   - USE WHEN: Explicit logical connection exists (e.g., dependency).

7. memory-vector-search
   - USE WHEN: Exact keywords fail, or looking for conceptual similarity.
   - OUTPUT: Returns content and metadata.

8. memory-index
   - USE WHEN: You need to discover available memories before searching or reading.
   - OUTPUT: Lightweight list of user memories.
   - REQUIREMENT: Use this first to avoid blind searching.

9. memory-history
   - USE WHEN: You need to see the evolution of a memory or audit who changed what.
   - OUTPUT: Full version history and audit logs.
TEXT
        );
    }

    public function name(): string
    {
        return 'tool-usage-guidelines';
    }
}
