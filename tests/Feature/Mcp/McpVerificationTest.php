<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('mcp client can list tools', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.tools', fn (array $tools) => count($tools) > 0);
});

test('mcp client can read a memory resource', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $memory = Memory::create([
        'organization' => 'test-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Verification Test Content',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'resources/read',
        'params' => [
            'uri' => "memory://{$memory->id}",
        ],
        'id' => 2,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.contents.0.text', 'Verification Test Content');
});

test('mcp client can search memories', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Memory::create([
        'organization' => 'test-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Special Secret Fact',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-search',
            'arguments' => [
                'query' => 'Special Secret',
            ],
        ],
        'id' => 3,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.content.0.text', function (string $text) {
        return str_contains($text, 'Special Secret Fact');
    });
});
