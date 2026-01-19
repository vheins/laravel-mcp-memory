<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can discover tools', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'params' => [],
            'id' => 1,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.tools.0.name', 'memory.write')
        ->assertJsonPath('result.tools.1.name', 'memory.delete')
        ->assertJsonPath('result.tools.2.name', 'memory.search');
});

it('can discover resource templates', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
            'jsonrpc' => '2.0',
            'method' => 'resources/templates/list',
            'params' => [],
            'id' => 1,
        ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'memory']);
    $response->assertJsonFragment(['name' => 'memory_history']);
});

it('can write a memory via tool', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory.write',
                'arguments' => [
                    'organization' => 'test-org',
                    'scope_type' => 'repository',
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

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory.search',
                'arguments' => [
                    'repository' => 'search-repo',
                    'query' => 'Team',
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
    $memory = Memory::create([
        'organization' => 'del-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Delete Me',
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory.delete',
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
    $memory = Memory::create([
        'organization' => 'res-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Read Me Resource',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', [
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
