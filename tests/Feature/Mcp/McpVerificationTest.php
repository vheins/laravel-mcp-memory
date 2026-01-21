<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('mcp client can list tools', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.tools', fn (array $tools): bool => $tools !== []);
});

test('mcp client can read a memory resource', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $memory = Memory::query()->create([
        'organization' => 'test-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Verification Test Content',
        'title' => 'Verification Title',
        'status' => 'active',
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
        ->assertJsonPath('result.contents.0.text', fn (string $text) => str_contains($text, 'Verification Test Content'));
});

test('mcp client can search memories', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Memory::query()->create([
        'organization' => 'test-org',
        'scope_type' => 'repository',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Special Secret Fact',
        'title' => 'Secret Fact',
        'status' => 'active',
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
    $response->assertJsonPath('result.content.0.text', fn(string $text) => str_contains($text, 'Special Secret Fact'));
});
