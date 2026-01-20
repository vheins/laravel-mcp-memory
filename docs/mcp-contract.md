# MCP Server Contract: Laravel Memory

## 1. Server Manifest

| Field           | Value                                                                                                                        |
| :-------------- | :--------------------------------------------------------------------------------------------------------------------------- |
| **Name**        | `Memory MCP Server`                                                                                                          |
| **Version**     | `1.0.0`                                                                                                                      |
| **Description** | Manages structured memories (facts, preferences, rules) for the application. Supports hierarchical searching and versioning. |

---

## 2. Tools Contract

### 2.1 `memory-write`
**Purpose**: Create a new memory entry. Supports facts, preferences, and business rules.

| Argument          | Type            | Required | Description                                                | Constraints                                                      |
| :---------------- | :-------------- | :------- | :--------------------------------------------------------- | :--------------------------------------------------------------- |
| `id`              | `string` (UUID) | No       | UUID of the memory to update. Leave empty for new entries. | Must be valid UUID.                                              |
| `organization`    | `string`        | **Yes**  | The organization slug to which this memory belongs.        | e.g., "my-org"                                                   |
| `repository`      | `string`        | No       | The specific repository slug if project-specific.          | e.g., "frontend-repo"                                            |
| `title`           | `string`        | No       | A concise summary of the memory content.                   | Max 12 words, no explanation.                                    |
| `scope_type`      | `string` (Enum) | **Yes**  | Visibility scope.                                          | `system`, `organization`, `user`                                 |
| `memory_type`     | `string` (Enum) | **Yes**  | Category of the memory.                                    | `business_rule`, `preference`, `fact`, `system_constraint`, etc. |
| `current_content` | `string`        | **Yes**  | The actual content of the memory.                          | Precise and concise.                                             |
| `status`          | `string` (Enum) | No       | Lifecycle status.                                          | `draft` (default), `active`, `archived`                          |
| `importance`      | `number`        | No       | Priority level.                                            | Min: 1, Max: 10. Default: 1.                                     |
| `metadata`        | `object`        | No       | Arbitrary JSON key-value pairs.                            | Max 5 keys, flat key-values only.                                |

### 2.2 `memory-update`
**Purpose**: Update an existing memory entry by its UUID.

| Argument          | Type            | Required | Description                                    | Constraints                                 |
| :---------------- | :-------------- | :------- | :--------------------------------------------- | :------------------------------------------ |
| `id`              | `string` (UUID) | **Yes**  | The unique UUID of the memory entry to update. | Must exist.                                 |
| `title`           | `string`        | No       | A new summary title.                           | Max 12 words.                               |
| `current_content` | `string`        | No       | The new text content.                          | Replaces entire content.                    |
| `status`          | `string` (Enum) | No       | Update the status.                             | `draft`, `active`, `archived`               |
| `scope_type`      | `string` (Enum) | No       | Change the visibility scope.                   | `system`, `organization`, `user`            |
| `memory_type`     | `string` (Enum) | No       | Reclassify the memory type.                    | `business_rule`, `preference`, `fact`, etc. |
| `importance`      | `number`        | No       | Adjust the priority level.                     | Min: 1, Max: 10.                            |
| `metadata`        | `object`        | No       | Merge or replace metadata keys.                | Max 5 keys, flat.                           |

### 2.3 `memory-delete`
**Purpose**: Soft-delete a memory entry by its UUID.

| Argument | Type            | Required | Description                                  |
| :------- | :-------------- | :------- | :------------------------------------------- |
| `id`     | `string` (UUID) | **Yes**  | The UUID of the memory entry to soft-delete. |

### 2.4 `memory-search`
**Purpose**: Search for memories with hierarchical resolution and filtering.

| Argument  | Type     | Required | Description              |
| :-------- | :------- | :------- | :----------------------- |
| `query`   | `string` | No       | The search query string. |
| `filters` | `object` | No       | Structured filters.      |

**Filters Schema**:
- `repository` (string): Limit to specific repository.
- `memory_type` (enum): Filter by category.
- `status` (enum): Filter by status.
- `scope_type` (enum): Filter by scope.
- `metadata` (object): Strict key-value match.

### 2.5 `memory-bulk-write`
**Purpose**: Create or update multiple memory entries in a single batch.

| Argument | Type    | Required | Description                        |
| :------- | :------ | :------- | :--------------------------------- |
| `items`  | `array` | **Yes**  | List of memory objects to process. |

**Item Schema**:
Same as `memory-write` arguments, but `id` is optional (for creation) or required (for update).

### 2.6 `memory-link`
**Purpose**: Create a relationship between two existing memories (Knowledge Graph).

| Argument        | Type            | Required | Description                     | Values                                       |
| :-------------- | :-------------- | :------- | :------------------------------ | :------------------------------------------- |
| `source_id`     | `string` (UUID) | **Yes**  | Starting point of relationship. |                                              |
| `target_id`     | `string` (UUID) | **Yes**  | Endpoint of relationship.       |                                              |
| `relation_type` | `string` (Enum) | No       | Nature of the relationship.     | `related` (default), `conflicts`, `supports` |

### 2.7 `memory-vector-search`
**Purpose**: Semantic search using vector embeddings.

| Argument     | Type             | Required | Description                                    |
| :----------- | :--------------- | :------- | :--------------------------------------------- |
| `vector`     | `array` (floats) | **Yes**  | 1536-dimensional embedding vector.             |
| `repository` | `string`         | No       | Limit search to specific repository.           |
| `threshold`  | `number`         | No       | Similarity threshold (0.0 - 1.0). Default 0.5. |
| `filters`    | `object`         | No       | Structured filters (same as memory-search).    |

---

## 3. Resources Contract

### 3.1 Static Resources

| URI               | Name           | Description                                                                                                 |
| :---------------- | :------------- | :---------------------------------------------------------------------------------------------------------- |
| `memory://index`  | `memory-index` | Discovery endpoint listing recent memories. Returns JSON array of lightweight objects (NO current_content). |
| `schema://schema` | `schema`       | JSON schema defining valid types, statuses, and scopes.                                                     |

### 3.2 Dynamic Resources (Templates)

| URI Template            | Name             | Description                           | Details                                                                 |
| :---------------------- | :--------------- | :------------------------------------ | :---------------------------------------------------------------------- |
| `memory://{id}`         | `memory`         | Read a specific memory entry.         | Returns raw text content. Meta: type, status, org, repo.                |
| `memory://{id}/history` | `memory-history` | Retrieve all versions and audit logs. | Returns JSON with `versions` and `audit_logs`.                          |
| `docs://{slug}`         | `docs`           | Documentation pages.                  | Slugs: `mcp-overview`, `tools-guide`, `behavior-rules`, `memory-rules`. |

---

## 4. Prompts Contract

### 4.1 `memory-agent-core`
**Description**: The core behavioral contract for all agents interacting with the Memory MCP.
**Usage**: Should be loaded at the start of a session to establish rules.

### 4.2 `memory-index-policy`
**Description**: Enforces strict policy regarding memory index usage (discovery only) and content limits (no payload).

### 4.3 `tool-usage-guidelines`
**Description**: Strict guidelines on when to use (and when NOT to use) each tool.

---

## 5. Memory Policy & Usage Rules

**CRITICAL**: All agents must strictly adhere to these rules.

1.  **Atomic Memories**:
    *   Store ONE distinct concept per memory.
    *   Never merge unrelated facts.
    *   **Bad**: "User likes dark mode and lives in NYC."
    *   **Good**: Two separate memories.

2.  **No Chat Logs**:
    *   Never store raw chat transcripts.
    *   Synthesize knowledge into facts/rules.

3.  **Search Before Write**:
    *   **Mandatory**: Use `memory-search` before creating new memories to prevent duplicates.
    *   Read `memory://index` for discovery, but do not rely on it for deep reasoning.

4.  **Title Constraints**:
    *   Max 12 words.
    *   No explanations.
    *   One short sentence.

5.  **Metadata Limits**:
    *   Max 5 keys.
    *   Flat key-value pairs only (no nested objects).
    *   No long text.

6.  **Scope definitions**:
    *   `system`: Global truths.
    *   `organization`: Team-wide knowledge.
    *   `user`: Private preferences.
