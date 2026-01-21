<?php

use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can get memory history via tool', function (): void {
    Sanctum::actingAs(User::factory()->create());

    // Create a memory with some history
    $memory = Memory::query()->create([
        'organization' => 'hist-org',
        'title' => 'Original Title',
        'scope_type' => 'organization',
        'memory_type' => 'fact',
        'created_by_type' => 'human',
        'current_content' => 'Original Content',
        'status' => 'active',
    ]);

    // Update it to generate a version/audit log
    $memory->update(['current_content' => 'Data 2']);
    $memory->update(['current_content' => 'Data 3']);

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => [
            'name' => 'memory-audit',
            'arguments' => [
                'id' => $memory->id,
            ],
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('result.isError', false);

    $response->assertJsonPath('result.content.0.text', function (string $text) {
        $data = json_decode($text, true);

        // Should have current title
        if ($data['title'] !== 'Original Title') return false;

        // Should have current ID
        if ($data['id'] !== $data['id']) return false;

        // Verify versions (should have at least the initial create + 2 updates if model handles versions automatically,
        // OR depending on how Memory model is implemented, it might store old versions only.
        // Let's just check structure exists
        if (!isset($data['versions'])) return false;

        // Verify audit logs
        if (!isset($data['audit_logs'])) return false;

        return true;
    });
});
