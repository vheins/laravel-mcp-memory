<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

class DocsResource extends Resource implements HasUriTemplate
{
    public function name(): string
    {
        return 'docs';
    }

    public function title(): string
    {
        return 'MCP Documentation';
    }

    public function description(): string
    {
        return 'Essential documentation for using this MCP server correctly.';
    }

    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('docs://{slug}');
    }

    public function handle(Request $request): Response
    {
        $slug = $request->get('slug');

        return match ($slug) {
            'mcp-overview' => $this->overview(),
            'tools-guide' => $this->toolsGuide(),
            'behavior-rules' => $this->behaviorRules(),
            'memory-rules' => $this->memoryRules(),
            default => Response::text("Document not found: $slug")->withMeta(['error' => true]),
        };
    }

    protected function overview(): Response
    {
        $content = <<<'MARKDOWN'
# MCP Server Overview

## Purpose
This Memory MCP Server serves as the central knowledge management system for the application. It allows AI agents to store, retrieve, and manage structured memories (facts, preferences, business rules) to persist context across different sessions and interactions.

## Problems Solved
1. **Knowledge Loss**: Prevents valuable insights and user preferences from being lost when a chat session ends.
2. **Context Fragmentation**: Provides a shared "brain" that all agents can access, ensuring consistency.
3. **Redundant Learning**: Prevents the need to re-learn the same business rules or user details repeatedly.

## Target Audience
All AI agents interacting with this application should use this MCP to check for existing knowledge before acting and to store new, permanent knowledge after solving problems.
MARKDOWN;

        return Response::text($content)->withMeta(['type' => 'documentation']);
    }

    protected function toolsGuide(): Response
    {
        $content = <<<'MARKDOWN'
# MCP Tools Guide

This guide explains the available tools and their intended usage.

## Available Tools

### 1. `memory-write`
- **Purpose**: Create a NEW memory entry.
- **When to use**: You have discovered a new fact, preference, or rule that does not exist in the system.
- **When NOT to use**: Do not use to update existing memories (use `memory-update` instead). Do not use for temporary or chat-specific data.

### 2. `memory-update`
- **Purpose**: Modify an EXISTING memory.
- **When to use**: Information has changed, or you need to refine/correct an existing memory.
- **When NOT to use**: Do not use if the memory ID is unknown.

### 3. `memory-delete`
- **Purpose**: Remove a memory entry.
- **When to use**: Information is no longer valid or was created in error.
- **Caution**: This is destructive. Ensure the memory is truly obsolete.

### 4. `memory-search`
- **Purpose**: Find memories using text query and filters.
- **When to use**: You need to answer a question or understand context. ALWAYS use this before deciding to write new memory to avoid duplicates.
- **Features**: Supports filtering by status, type, and scope.

### 5. `memory-batch-write`
- **Purpose**: Create multiple memories in a single request.
- **When to use**: You have extracted a list of facts (e.g., from a document analysis) and want to save them efficiently.

### 6. `memory-link`
- **Purpose**: Create a relationship between two memories.
- **When to use**: You want to explicitly connect related concepts (e.g., "Feature A" depends_on "Service B").

### 7. `memory-vector-search`
- **Purpose**: Semantic search using vector embeddings.
- **When to use**: Keyword search (`memory-search`) fails, or you are looking for conceptually similar memories regardless of exact wording.
MARKDOWN;

        return Response::text($content)->withMeta(['type' => 'documentation']);
    }

    protected function behaviorRules(): Response
    {
        $content = <<<'MARKDOWN'
# Agent Behavior Rules

All agents interacting with this MCP server MUST follow these rules:

1. **Discovery First**:
    - Always call `resources/list` at the start of a session to understand available resources.
    - Read `docs://mcp-overview` and `docs://tools-guide` if you are unfamiliar with the capabilities.

2. **Search Before Write**:
    - Before creating a new memory, ALWAYS search (`memory-search`) to ensure it doesn't already exist.
    - Duplicate memories cause confusion and inconsistencies.

3. **Check the Index**:
    - Before creating a new memory, read `memory://index`.
    - Compare titles and metadata to see if a similar topic exists.
    - If it exists, use `memory-update` instead of `memory-write`.
    - NEVER rely on the index content for reasoning; it is for discovery only. Use `memory-search` to retrieve full content.

4. **Read the Rules**:
    - You MUST read `docs://memory-rules` before writing any memory to ensure compliance with data quality standards.

5. **Context Awareness**:
    - Use `memory-search` to load relevant context into your conversation before answering complex user queries.
MARKDOWN;

        return Response::text($content)->withMeta(['type' => 'documentation']);
    }

    protected function memoryRules(): Response
    {
        $content = <<<'MARKDOWN'
# Memory Quality Rules

To maintain a high-quality knowledge base, adhere to the following rules when writing to memory:

### 1. Atomic Memories
- **Rule**: Each memory should contain ONE distinct concept, fact, or rule.
- **Why**: Atomic memories are easier to search, link, and update.
- **Bad**: "User likes dark mode, lives in NYC, and works as a dev."
- **Good**: Three separate memories: (1) Preference: Dark Mode, (2) Fact: Location NYC, (3) Fact: Occupation Dev.

### 2. No Chat Logs
- **Rule**: NEVER store raw chat transcripts or "The user said X" verbatim.
- **Why**: Memories should be synthesized knowledge, not a recording of history.
- **Bad**: "User said 'I want the button to be blue'."
- **Good**: "Preference: Primary button color should be blue."

### 3. No Ephemeral Data
- **Rule**: Do not store temporary debugging info, session IDs, or scratchpad thoughts.
- **Why**: Memory is for long-term persistence.

### 4. Descriptive & Concise Titles
- **Rule**: Title must describe the memory in one short sentence. Maximum 12 words. No explanation.
- **Why**: Helps in quick identification during search results and index scanning.
- **Good**: "Livewire SPA undefined uri error"
- **Bad**: "Detailed explanation about how to fix Livewire error with steps"

### 5. Metadata Restrictions
- **Rule**: Metadata must be optional, flat key-value pairs, contain only identifiers or tags, and never exceed 5 keys.
- **Allowed**: `{"framework": "livewire", "type": "bugfix"}`
- **Forbidden**: Summaries, reasoning, nested objects, or large arrays.

### 6. Proper Scoping
- **Rule**: Use the correct `scope_type`.
- **System**: Global truths (e.g., "The app uses Laravel").
- **Organization**: Team-specific knowledge.
- **User**: Personal preferences.
MARKDOWN;

        return Response::text($content)->withMeta(['type' => 'documentation']);
    }
}
