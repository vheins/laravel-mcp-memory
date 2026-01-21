<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Enums\MemoryStatus;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AdvancedMemoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_bulk_write_memories(): void
    {
        $response = $this->postJson('/memory-mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-bulk-write',
                'arguments' => [
                    'items' => [
                        [
                            'organization' => 'test-org',
                            'scope_type' => 'organization',
                            'memory_type' => 'overview', // Now supported!
                            'current_content' => 'Bulk item 1',
                            'title' => 'Title 1',
                            'importance' => 8,
                        ],
                        [
                            'organization' => 'test-org',
                            'scope_type' => 'organization',
                            'memory_type' => 'preference',
                            'current_content' => 'Bulk item 2',
                            'title' => 'Title 2',
                            'importance' => 5,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();
        $this->assertCount(2, Memory::all());
        $this->assertEquals(8, Memory::query()->where('title', 'Title 1')->first()->importance);
        $this->assertEquals('overview', Memory::query()->where('title', 'Title 1')->first()->memory_type->value);
    }

    public function test_bulk_write_memories_validation_error(): void
    {
        $response = $this->postJson('/memory-mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-bulk-write',
                'arguments' => [
                    'items' => [
                        [
                            'organization' => 'test-org',
                            'scope_type' => 'organization',
                            'memory_type' => 'invalid_type', // Truly invalid
                            'current_content' => 'Bulk item 1',
                            'title' => 'Title 1',
                            'importance' => 8,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('result.isError', true);
        $this->assertStringContainsString('The selected memory type is invalid', (string) $response->json('result.content.0.text'));
    }

    public function test_link_memories(): void
    {
        $m1 = Memory::factory()->create(['title' => 'Memory 1']);
        $m2 = Memory::factory()->create(['title' => 'Memory 2']);

        $response = $this->postJson('/memory-mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-link',
                'arguments' => [
                    'source_id' => $m1->id,
                    'target_id' => $m2->id,
                    'relation_type' => 'supports',
                ],
            ],
        ]);

        $response->assertSuccessful();
        $this->assertTrue($m1->relatedMemories->contains($m2));
    }

    public function test_vector_search(): void
    {
        // Seed memories with embeddings (highly simplified)
        Memory::factory()->create([
            'title' => 'Apple',
            'embedding' => [1.0, 0.0, 0.0],
            'importance' => 1,
            'status' => MemoryStatus::Active,
        ]);
        Memory::factory()->create([
            'title' => 'Banana',
            'embedding' => [0.0, 1.0, 0.0],
            'importance' => 1,
            'status' => MemoryStatus::Active,
        ]);
        Memory::factory()->create([
            'title' => 'Important Apple',
            'embedding' => [1.0, 0.1, 0.0],
            'importance' => 10,
            'status' => MemoryStatus::Active,
        ]);

        $response = $this->postJson('/memory-mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-vector-search',
                'arguments' => [
                    'vector' => [1.0, 0.0, 0.0],
                    'threshold' => 0.8,
                ],
            ],
        ]);

        $response->assertSuccessful();

        $data = json_decode((string) $response->json('result.content.0.text'), true);

        // Should return 'Important Apple' first because of higher importance, then 'Apple'
        $this->assertEquals('Important Apple', $data[0]['title']);
        $this->assertEquals('Apple', $data[1]['title']);
        $this->assertCount(2, $data);
    }
}
