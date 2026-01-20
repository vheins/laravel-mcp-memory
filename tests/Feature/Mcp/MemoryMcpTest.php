<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can discover tools', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.tools.0.name', 'memory-write')
        ->assertJsonPath('result.tools.1.name', 'memory-update')
        ->assertJsonPath('result.tools.2.name', 'memory-delete')
        ->assertJsonPath('result.tools.3.name', 'memory-search');
});

it('can discover resource templates', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'resources/templates/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'memory']);
    $response->assertJsonFragment(['name' => 'memory-history']);
});

it('can write a memory via tool', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-write',
            'arguments' => [
                'organization' => 'test-org',
                'scope_type' => 'organization',
                'memory_type' => 'fact',
                'current_content' => 'Test Content',
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('memories', [
        'current_content' => 'Test Content',
        'organization' => 'test-org',
    ]);
});

it('can search memories via tool (team context)', function () {
    Sanctum::actingAs(User::factory()->create());
    $anotherUser = User::factory()->create();

    Memory::create([
        'organization' => 'search-org',
        'repository' => 'search-repo',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Team Fact',
        'user_id' => $anotherUser->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-search',
            'arguments' => [
                'query' => 'Team',
                'filters' => [
                    'repository' => 'search-repo',
                ],
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.content.0.text', function (string $text) {
        return str_contains($text, 'Team Fact');
    });
});

it('can delete a memory via tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $memory = Memory::create([
        'organization' => 'del-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Delete Me',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-delete',
            'arguments' => [
                'id' => $memory->id,
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $this->assertSoftDeleted('memories', ['id' => $memory->id]);
});

it('can read a memory via resource', function () {
    Sanctum::actingAs(User::factory()->create());

    $memory = Memory::create([
        'organization' => 'res-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Read Me Resource',
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'resources/read',
        'params' => [
            'uri' => "memory://{$memory->id}",
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.contents.0.text', 'Read Me Resource');
});

it('can search memories with user hierarchy', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create a repository-scoped memory
    Memory::create([
        'organization' => 'hier-org',
        'repository' => 'hier-repo',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Repo Fact',
    ]);

    // Create a user-scoped memory for the same repository
    Memory::create([
        'organization' => 'hier-org',
        'repository' => 'hier-repo',
        'scope_type' => 'user',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'User Fact',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-search',
            'arguments' => [
                'filters' => [
                    'repository' => 'hier-repo',
                    'user_id' => (string) $user->id,
                ],
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    // Values are returned as JSON string in result.content[0].text
    $response->assertJsonPath('result.content.0.text', function (string $text) {
        $data = json_decode($text, true);
        $contents = collect($data)->pluck('current_content')->toArray();

        return in_array('User Fact', $contents) && in_array('Repo Fact', $contents);
    });
});

it('cannot update locked memory via tool', function () {
    Sanctum::actingAs(User::factory()->create());

    $memory = Memory::create([
        'organization' => 'lock-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Immutable Content',
        'status' => 'locked',
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-write',
            'arguments' => [
                'id' => $memory->id,
                'organization' => 'lock-org',
                'scope_type' => 'repository',
                'memory_type' => 'fact',
                'current_content' => 'Attempted Change',
            ],
        ],
        'id' => 1,
    ]);

    // MCP server caught the exception and should return an error
    $response->assertJsonPath('error.message', 'Cannot update locked memory.');
});

it('enforces immutable types for AI updates', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-write',
            'arguments' => [
                'organization' => 'rule-org',
                'scope_type' => 'system',
                'memory_type' => 'system_constraint',
                'current_content' => 'Human setting system constraint',
            ],
        ],
        'id' => 1,
    ]);

    // Expect an error because AI cannot write system_constraints
    $response->assertStatus(200);
    $response->assertJson(['error' => ['code' => -32000]]); // JSON-RPC error code
    $this->assertDatabaseMissing('memories', ['memory_type' => 'system_constraint']);
});

it('can search memories without repository argument', function () {
    Sanctum::actingAs(User::factory()->create());

    Memory::create([
        'organization' => 'global-org',
        'repository' => 'repo-a',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Content in Repo A',
        'status' => 'active',
    ]);

    Memory::create([
        'organization' => 'global-org',
        'repository' => 'repo-b',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Content in Repo B',
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-search',
            'arguments' => [
                'query' => 'Content',
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.content.0.text', function (string $text) {
        $data = json_decode($text, true);
        $contents = collect($data)->pluck('current_content')->toArray();

        return count($contents) >= 2;
    });
});

it('can access the mcp server via memory-mcp alias', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory-mcp', [
        'jsonrpc' => '2.0',
        'method' => 'tools/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.tools.0.name', 'memory-write');
});
