<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list all resources including schema', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'resources/list',
        'params' => [],
        'id' => 1,
    ]);

    $response->assertStatus(200);

    $resources = collect($response->json('result.resources'));
    expect($resources->contains('name', 'schema'))->toBeTrue();
});

it('can read the schema resource', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/v1/mcp/memory', [
        'jsonrpc' => '2.0',
        'method' => 'resources/read',
        'params' => [
            'uri' => 'schema://schema',
        ],
        'id' => 1,
    ]);

    $response->assertStatus(200);

    // Debugging the failure if it happens again
    if (!$response->json('result.contents.0.text')) {
        // fwrite(STDERR, print_r($response->json(), true));
    }

    $response->assertJsonPath('result.contents.0.text', function ($text) {
        if (is_null($text)) return false;
        $data = json_decode((string)$text, true);
        return isset($data['memory_types']) && isset($data['memory_statuses']);
    });
});
