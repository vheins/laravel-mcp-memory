<?php

use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->orgId = Str::uuid()->toString();
    $this->repo = Repository::create([
        'organization_id' => $this->orgId,
        'name' => 'Isolation Repo',
        'slug' => 'isolation-repo',
    ]);
});

it('requires authentication for MCP endpoint', function () {
    $response = $this->postJson(route('api.v1.mcp'), [
        'jsonrpc' => '2.0',
        'method' => 'memory.read',
        'params' => ['id' => '123'],
        'id' => 1,
    ]);

    $response->assertStatus(401);
});

it('forces authenticated user_id on write', function () {
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'memory.write',
        'params' => [
            'organization_id' => $this->orgId,
            'repository_id' => $this->repo->id,
            'scope_type' => 'user',
            'memory_type' => 'preference',
            'current_content' => 'My Preference',
            'user_id' => $this->otherUser->id, // Attempt to spoof
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('api.v1.mcp'), $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('result.user_id', $this->user->id); // Should be User A
    $response->assertJsonPath('result.user_id', fn($id) => $id !== $this->otherUser->id);
});

it('forces authenticated user_id on search', function () {
    // Create User A Memory
    Memory::create([
        'id' => Str::uuid(),
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'user_id' => $this->user->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User A Secret',
    ]);

    // Create User B Memory
    Memory::create([
        'id' => Str::uuid(),
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'user_id' => $this->otherUser->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User B Secret',
    ]);

    // User A tries to search for User B's memories
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'memory.search',
        'params' => [
            'repository_id' => $this->repo->id,
            'filters' => [
                'user_id' => $this->otherUser->id, // Attempt to spoof
            ],
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('api.v1.mcp'), $payload);

    $response->assertStatus(200);

    // Should find User A's memory (because controller overrode the filter to be User A)
    // OR should find nothing if checking for User B but scoped to User A.
    // Logic says: controller sets filters['user_id'] = Auth::id().
    // So MemoryService searches for user_id = User A.
    // So it should return User A's memory.

    $content = collect($response->json('result'));
    expect($content->pluck('current_content'))
        ->toContain('User A Secret')
        ->not->toContain('User B Secret');
});
