<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Transport\JsonRpcRequest;
use Laravel\Mcp\Server\Transport\JsonRpcResponse;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('can retrieve memory core prompt', function () {
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
                'description' => 'The core behavioral contract for all agents interacting with the Memory MCP.',
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

it('can retrieve memory index policy prompt', function () {
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
                'description' => 'Enforces the strict policy regarding memory index usage and content.',
            ],
        ]);
});

it('can retrieve tool usage prompt', function () {
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
                'description' => 'Strict guidelines on when to use (and when NOT to use) each MCP tool.',
            ],
        ]);
});
