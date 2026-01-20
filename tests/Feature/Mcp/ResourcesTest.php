<?php

declare(strict_types=1);

use App\Mcp\Resources\DocsResource;
use App\Mcp\Resources\MemoryIndexResource;
use App\Models\Memory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Server\Contracts\Resource;

uses(RefreshDatabase::class);

test('docs resource returns overview', function () {
    $resource = new DocsResource();
    $request = new \Laravel\Mcp\Request(['slug' => 'mcp-overview']);

    $response = $resource->handle($request);

    expect((string) $response->content())->toContain('# MCP Server Overview')
        ->and($response->content()->toArray()['_meta']['type'])->toBe('documentation');
});

test('docs resource returns tools guide', function () {
    $resource = new DocsResource();
    $request = new \Laravel\Mcp\Request(['slug' => 'tools-guide']);

    $response = $resource->handle($request);

    expect((string) $response->content())->toContain('# MCP Tools Guide')
        ->and((string) $response->content())->toContain('memory-write');
});

test('docs resource returns behavior rules', function () {
    $resource = new DocsResource();
    $request = new \Laravel\Mcp\Request(['slug' => 'behavior-rules']);

    $response = $resource->handle($request);

    expect((string) $response->content())->toContain('# Agent Behavior Rules');
});

test('docs resource returns memory rules', function () {
    $resource = new DocsResource();
    $request = new \Laravel\Mcp\Request(['slug' => 'memory-rules']);

    $response = $resource->handle($request);

    expect((string) $response->content())->toContain('# Memory Quality Rules');
});

test('docs resource returns error for unknown slug', function () {
    $resource = new DocsResource();
    $request = new \Laravel\Mcp\Request(['slug' => 'unknown-doc']);

    $response = $resource->handle($request);

    expect((string) $response->content())->toContain('Document not found')
        ->and($response->content()->toArray()['_meta']['error'])->toBeTrue();
});

test('memory index resource returns recent memories', function () {
    Memory::factory()->create([
        'title' => 'Test Memory A',
        'current_content' => 'Content A',
        'organization' => 'test-org',
        'scope_type' => 'system',
        'memory_type' => 'fact',
        'importance' => 5,
        'metadata' => ['key1' => 'val1', 'key2' => 'val2', 'long' => str_repeat('a', 100)]
    ]);

    $resource = new MemoryIndexResource();
    $request = new \Laravel\Mcp\Request([]);

    $response = $resource->handle($request);

    // Verify JSON response
    $data = json_decode((string) $response->content(), true);

    expect($data)->toBeArray()
        ->and($data[0]['title'])->toBe('Test Memory A')
        ->and($data[0]['importance'])->toBe(5)
        ->and($data[0]['metadata'])->toHaveCount(3)
        ->and(strlen($data[0]['metadata']['long']))->toBeLessThan(55) // 47 + ...
        ->and($data[0])->not->toHaveKey('current_content') // Content forbidden
        ->and($response->content()->toArray()['_meta']['type'])->toBe('index')
        ->and($response->content()->toArray()['_meta']['count'])->toBe(1);
});

test('memory index handles empty state', function () {
    $resource = new MemoryIndexResource();
    $request = new \Laravel\Mcp\Request([]);

    $response = $resource->handle($request);

    $data = json_decode((string) $response->content(), true);
    expect($data)->toBeArray()->toBeEmpty();
});
