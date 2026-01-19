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

it('forces authenticated user on write', function () {
    $payload = [
        'jsonrpc' => '2.0',
        'method' => 'memory.write',
        'params' => [
            'organization' => $this->orgId,
            'repository' => $this->repo->id,
            'scope_type' => 'user',
            'memory_type' => 'preference',
            'current_content' => 'My Preference',
            'user' => $this->otherUser->id, // Attempt to spoof
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('api.v1.mcp'), $payload);

    $response->assertStatus(200);

    $response->assertJsonPath('result.user', $this->user->id); // Should be User A
    $response->assertJsonPath('result.user', fn($id) => $id !== $this->otherUser->id);
});

it('forces authenticated user on search', function () {
    // Create User A Memory
    Memory::create([
        'id' => Str::uuid(),
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user' => $this->user->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User A Secret',
    ]);

    // Create User B Memory
    Memory::create([
        'id' => Str::uuid(),
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user' => $this->otherUser->id,
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
            'repository' => $this->repo->id,
            'filters' => [
                'user' => $this->otherUser->id, // Attempt to spoof
            ],
        ],
        'id' => 1,
    ];

    $response = $this->actingAs($this->user, 'sanctum')->postJson(route('api.v1.mcp'), $payload);

    $response->assertStatus(200);

    $content = collect($response->json('result'));
    expect($content->pluck('current_content'))
        ->toContain('User A Secret')
        ->not->toContain('User B Secret');
});
