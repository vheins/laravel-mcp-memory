<?php

use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->orgId = Str::uuid()->toString();
    $this->repo = Repository::query()->create([
        'organization_id' => $this->orgId,
        'name' => 'Isolation Repo',
        'slug' => 'isolation-repo',
    ]);
});

it('requires authentication for MCP endpoint', function (): void {
    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'memory.read',
        'params' => ['id' => '123'],
        'id' => 1,
    ]);

    $response->assertStatus(401);
});

it('forces authenticated user on write', function (): void {
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-write',
            'arguments' => [
                'organization' => $this->orgId,
                'repository' => $this->repo->id,
                'scope_type' => 'user',
                'memory_type' => 'preference',
                'current_content' => 'My Preference',
                'title' => 'Isolation Title',
                'user' => $this->otherUser->id, // Attempt to spoof
            ],
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/mcp/memory', $payload);

    $response->assertStatus(200);

    $response->assertJsonPath('result.content.0.text', function ($text): bool {
        $data = json_decode($text, true);

        return (string) $data['user_id'] === (string) $this->user->id && (string) $data['user_id'] !== (string) $this->otherUser->id;
    });
});

it('forces authenticated user on search', function (): void {
    // Create User A Memory
    Memory::query()->create([
        'id' => Str::uuid(),
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user_id' => $this->user->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User A Secret',
        'title' => 'User A Title',
        'status' => 'active',
    ]);

    // Create User B Memory
    Memory::query()->create([
        'id' => Str::uuid(),
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user_id' => $this->otherUser->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User B Secret',
        'title' => 'User B Title',
        'status' => 'active',
    ]);

    // User A tries to search for User B's memories
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-search',
            'arguments' => [
                'repository' => $this->repo->id,
                'filters' => [
                    'user' => $this->otherUser->id, // Attempt to spoof
                ],
            ],
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/mcp/memory', $payload);

    $response->assertStatus(200);

    $response->assertJsonPath('result.content.0.text', function ($text): bool {
        $content = collect(json_decode($text, true) ?? []);

        return $content->count() > 0
            && $content->pluck('current_content')->contains('User A Secret')
            && $content->pluck('current_content')->doesntContain('User B Secret');
    });
});
