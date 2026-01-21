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
   - USE WHEN: You need to answer a question or check for duplicates.
   - DO NOT USE: As a way to "browse" indiscriminately (use `memory://index` for that).
   - REQUIREMENT: Use specific keywords to limit noise.

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
TEXT
        );
    }

    public function name(): string
    {
        return 'tool-usage-guidelines';
    }
}
