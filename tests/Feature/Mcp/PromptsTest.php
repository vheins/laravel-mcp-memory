<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('can retrieve memory core prompt', function (): void {
    Config::set('mcp.server.enabled', true);

    actingAs(User::factory()->create());

    $response = postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'memory-agent-core',
        ],
    ]);

    $response->assertOk()
        ->assertJson([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'description' => 'The core behavioral contract for all agents interacting with the Memory MCP. Enforces atomic memory creation, mandatory prior search, resource awareness, and correct scope usage to maintain knowledge graph integrity.',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ]);
});

it('can retrieve memory index policy prompt', function (): void {
    Config::set('mcp.server.enabled', true);

    actingAs(User::factory()->create());

    $response = postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'memory-index-policy',
        ],
    ]);

    $response->assertOk()
        ->assertJson([
            'jsonrpc' => '2.0',
            'id' => 2,
            'result' => [
                'description' => 'Defines and enforces the strict structural and behavioral policy for the memory index. Ensures the index remains a lightweight discovery mechanism by imposing constraints on titles, metadata, and agent interaction patterns.',
            ],
        ]);
});

it('can retrieve tool usage prompt', function (): void {
    Config::set('mcp.server.enabled', true);

    actingAs(User::factory()->create());

    $response = postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'prompts/get',
        'params' => [
            'name' => 'tool-usage-guidelines',
        ],
    ]);

    $response->assertOk()
        ->assertJson([
            'jsonrpc' => '2.0',
            'id' => 3,
            'result' => [
                'description' => 'Comprehensive guide and strict rules for using Memory MCP tools. Defines when to use search, index, write, update, and audit tools to ensure data integrity and avoid memory pollution.',
            ],
        ]);
});
