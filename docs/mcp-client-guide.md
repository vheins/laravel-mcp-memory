# MCP Client Connection Guide

This guide describes how to connect an MCP client (such as Claude Desktop, or another AI agent) to the Laravel Memory MCP Server.

## Endpoint

The Memory MCP server is available at the following endpoint:

```
http://<your-app-url>/api/v1/mcp/memory
```

> [!NOTE]
> Since this is a web-based MCP server using HTTP streaming, ensure your client supports the MCP over HTTP protocol.

## Authentication

All requests to the MCP server must be authenticated using Laravel Sanctum. You need to provide a Bearer token in the `Authorization` header.

```http
Authorization: Bearer YOUR_MCP_TOKEN
```

You can generate an MCP Token from the user profile settings in the application.

## Available Tools

The server provides several tools to manage memories:

- `memory-write`: Create or update a single memory.
- `memory-update`: Update an existing memory.
- `memory-delete`: Delete a memory.
- `memory-search`: Search for memories with filters and hierarchical context.
- `memory-batch-write`: Write multiple memories in a single operation.
- `memory-link`: Create relationships between memories.
- `memory-vector-search`: Semantic search using vector embeddings.

## Example Request

To list available tools, send a POST request to the endpoint:

```json
{
  "jsonrpc": "2.0",
  "method": "tools/list",
  "params": {},
  "id": 1
}
```

### Calling a Tool (Search)

```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "memory-search",
    "arguments": {
      "query": "system rules",
      "repository": "my-project"
    }
  },
  "id": 2
}
```

## Available Resources

- `memory://{id}`: Read a specific memory by its ID.
- `memory-history://{id}`: Read the audit history of a specific memory.

### Reading a Resource

```json
{
  "jsonrpc": "2.0",
  "method": "resources/read",
  "params": {
    "uri": "memory://1"
  },
  "id": 3
}
```
