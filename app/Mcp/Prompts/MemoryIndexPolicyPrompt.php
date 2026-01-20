<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class MemoryIndexPolicyPrompt extends Prompt
{
    public function name(): string
    {
        return 'memory-index-policy';
    }

    public function description(): string
    {
        return 'Enforces the strict policy regarding memory index usage and content.';
    }

    public function handle(Request $request): Response
    {
        return Response::text(<<<'TEXT'
MEMORY INDEX POLICY (CRITICAL)

The memory index is a compact discovery tool, NOT a mirror of content.

1. CONTENT LIMITS
   - The index NEVER contains full memory content.
   - The index includes ONLY: id, title, scope, type, importance, status, tags.
   - Index entries must be lightweight.

2. TITLE RULES
   - Titles must be one short sentence (max 12 words).
   - No explanations.
   - No punctuation-heavy formatting.

3. METADATA RULES
   - Max 5 keys per entry.
   - Flat key-value pairs only.
   - No nested objects or long text.

4. USAGE
   - Use the index to discover WHAT knowledge exists.
   - Do NOT use the index to learn HOW things work.
   - Always use `memory-search` to retrieve the full `current_content` if reasoning is needed.

5. INDEX GENERATION
   - When writing memory, you must ensure the metadata and title fit these constraints.
   - The system automatically actively excludes `current_content` from the index.
TEXT
        );
    }
}
