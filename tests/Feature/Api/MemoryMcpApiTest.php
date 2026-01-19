<?php

use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->repository = Repository::factory()->create();
});

it('can write memory via MCP', function () {
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'memory-write',
        'params' => [
            'organization' => $this->repository->organization_id,
            'repository' => $this->repository->id,
            'scope_type' => 'repository',
            'memory_type' => 'business_rule',
            'created_by_type' => 'human',
            'current_content' => 'MCP Content',
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', $payload);

    $response->assertStatus(200)
        ->assertJsonPath('result.content.0.text', function ($text) {
             $data = json_decode($text, true);
             return $data['current_content'] === 'MCP Content';
        })
        ->assertJsonPath('id', 1);
});

it('can read memory via MCP', function () {
    // Seed
    $service = app(\App\Services\MemoryService::class);
    $memory = $service->write([
        'organization' => $this->repository->organization_id,
        'repository' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Read Me',
    ], $this->user->id);

    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'resources/read',
        'params' => ['uri' => "memory://{$memory->id}"],
        'id' => 2,
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', $payload)
        ->assertStatus(200)
        ->assertJsonPath('result.contents.0.text', function ($text) use ($memory) {
             $data = json_decode($text, true);
             return $data['id'] === $memory->id && $data['current_content'] === 'Read Me';
        });
});

it('can search memory via MCP', function () {
    $service = app(\App\Services\MemoryService::class);
    $service->write([
        'organization' => $this->repository->organization_id,
        'repository' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'Searchable',
    ], $this->user->id);

    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'memory-search',
        'params' => [
            'repository' => $this->repository->id,
            'filters' => ['memory_type' => 'preference']
        ],
        'id' => 3,
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', $payload)
        ->assertStatus(200)
        ->assertJsonPath('result.content.0.text', function ($text) {
             $data = json_decode($text, true);
             return $data[0]['current_content'] === 'Searchable';
        });
});

it('returns error for unknown method', function () {
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'unknown.method',
        'params' => [],
        'id' => 4,
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/mcp/memory', $payload)
        ->assertStatus(200) // JSON-RPC errors return 200 usually, but with error object
        ->assertJsonPath('error.code', -32601)
        ->assertJsonPath('error.message', 'The method [unknown.method] was not found.');
});
