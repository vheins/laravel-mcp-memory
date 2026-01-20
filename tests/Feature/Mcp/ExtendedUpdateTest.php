<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can update memory scope and type via tool', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $memory = Memory::create([
        'organization' => 'scope-org',
        'scope_type' => 'user',
        'memory_type' => 'fact',
        'current_content' => 'Private Fact',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-update',
            'arguments' => [
                'id' => $memory->id,
                'scope_type' => 'organization',
                'memory_type' => 'preference',
                'importance' => 8,
                'current_content' => 'Public Rule',
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.content.0.text', function ($text) {
        return str_contains($text, 'Public Rule');
    });

    $this->assertDatabaseHas('memories', [
        'id' => $memory->id,
        'scope_type' => 'organization',
        'memory_type' => 'preference',
        'importance' => 8,
        'current_content' => 'Public Rule',
    ]);
});
