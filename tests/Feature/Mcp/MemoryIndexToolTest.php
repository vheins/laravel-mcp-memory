<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can get memory index via tool', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create some memories
    Memory::query()->create([
        'user_id' => $user->id,
        'organization' => 'test-org',
        'title' => 'Memory 1',
        'scope_type' => 'organization',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Content 1',
        'status' => 'active',
    ]);

    Memory::query()->create([
        'user_id' => $user->id,
        'organization' => 'test-org',
        'title' => 'Memory 2',
        'scope_type' => 'organization',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Content 2',
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-index',
            'arguments' => (object) [],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.isError', false);

    $response->assertJsonPath('result.content.0.text', function (string $text) {
        $data = json_decode($text, true);

        // Should be an array of memories
        if (!is_array($data)) return false;
        if (count($data) !== 2) return false;

        // Verify fields are present and current_content is NOT present
        foreach ($data as $memory) {
            if (!isset($memory['id'])) return false;
            if (!isset($memory['title'])) return false;
            if (!isset($memory['memory_type'])) return false;
            if (!isset($memory['scope_type'])) return false;
            if (isset($memory['current_content'])) return false;
        }

        return true;
    });
});
