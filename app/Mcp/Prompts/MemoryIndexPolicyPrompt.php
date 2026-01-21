<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;

class MemoryIndexPolicyPrompt extends Prompt
{
    public function description(): string
    {
        return 'Defines and enforces the strict structural and behavioral policy for the memory index. Ensures the index remains a lightweight discovery mechanism by imposing constraints on titles, metadata, and agent interaction patterns.';
    }

    public function handle(Request $request): Response
    {
        return Response::text(<<<'TEXT'
MEMORY INDEX POLICY (CRITICAL)

The memory index is a compact discovery tool designed for rapid context acquisition. It is NOT a knowledge repository.

1. DATA CONSTRAINTS (STRICT)
   - CONTENT: The `current_content` field is EXPLICITLY EXCLUDED from the index to maintain performance.
   - ALLOWED FIELDS: ONLY id, title, scope_type, memory_type, importance, status, repository, organization, updated_at, and filtered metadata.
   - ENTRIES: Limited to the 50 most recent records.

2. TITLE FORMATTING RULES
   - LENGTH: Must be a single concise sentence (max 12 words).
   - CLARITY: Must summarize the core fact without preamble or explanation.
   - SYNTAX: No excessive punctuation or markdown formatting.

3. METADATA CONSTRAINTS
   - LIMIT: Max 5 keys per memory entry.
   - STRUCTURE: Must be flat key-value pairs (String, Integer, or Boolean).
   - PROHIBITED: No nested objects, arrays, or long textual blobs.

4. AGENT BEHAVIORAL PATTERNS
   - DISCOVERY FIRST: Use `memory-index` to map the knowledge landscape before performing targeted searches.
   - NO "BLIND" SEARCHES: Do not search for specific IDs or keywords without first validating their existence via the index.
   - JUST-IN-TIME READING: Only use `memory-search` or `vector-search` to retrieve full content when high-fidelity reasoning is required.

5. INDEX INTEGRITY
   - When creating or updating a memory, you must guarantee that the title and metadata adhere to these constraints to ensure index health.
TEXT
        );
    }

    public function name(): string
    {
        return 'memory-index-policy';
    }
}
