<?php

use App\Models\Memory;
use App\Models\MemoryAuditLog;
use App\Models\MemoryVersion;
use App\Models\Repository;
use App\Services\MemoryService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new MemoryService();
    $this->user = User::factory()->create();
    $this->repository = Repository::factory()->create(['organization_id' => str()->uuid()]);
});

it('can create a new memory', function () {
    $data = [
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Initial content',
        'metadata' => ['key' => 'value'],
    ];

    $memory = $this->service->write($data, $this->user->id);

    expect($memory)->toBeInstanceOf(Memory::class)
        ->id->not->toBeNull()
        ->current_content->toBe('Initial content')
        ->status->toBe('draft');

    // Verify Version
    expect($memory->versions)->toHaveCount(1);
    expect($memory->versions->first()->content)->toBe('Initial content');

    // Verify Audit Log
    expect($memory->auditLogs)->toHaveCount(1);
    expect($memory->auditLogs->first()->event)->toBe('created');
});

it('creates a new version when content updates', function () {
    $data = [
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Initial content',
    ];

    $memory = $this->service->write($data, $this->user->id);

    $updateData = array_merge($data, [
        'id' => $memory->id, // Passing ID implies update
        'current_content' => 'Updated content',
    ]);

    $updatedMemory = $this->service->write($updateData, $this->user->id);

    expect($updatedMemory->current_content)->toBe('Updated content');
    expect($updatedMemory->versions()->count())->toBe(2);
    expect($updatedMemory->versions()->orderByDesc('version_number')->first()->content)->toBe('Updated content');

    // Audit Log for update
    expect($updatedMemory->auditLogs)->toHaveCount(2); // create + update
    expect($updatedMemory->auditLogs()->latest('created_at')->first()->event)->toBe('updated');
});

it('prevents update when memory is locked', function () {
    $data = [
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Content',
        'status' => 'locked',
    ];

    // Force create a locked memory directly via model to bypass service check if any
    // asking service to create locked memory
    $memory = Memory::create($data);

    $updateData = [
        'id' => $memory->id,
        'current_content' => 'New Content',
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        // ... other required fields
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
    ];

    expect(fn () => $this->service->write($updateData, $this->user->id))
        ->toThrow(\Exception::class, 'Cannot update locked memory.');
});

it('can read a memory', function () {
    $data = [
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Content to read',
    ];
    $memory = $this->service->write($data, $this->user->id);

    $readMemory = $this->service->read($memory->id);

    expect($readMemory->id)->toBe($memory->id);
    expect($readMemory->current_content)->toBe('Content to read');
});

it('can search memories', function () {
    // Create queryable memories
    $mem1 = $this->service->write([
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Apple pie recipe',
        'status' => 'verified',
    ], $this->user->id);

    $mem2 = $this->service->write([
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'Banana bread recipe',
        'status' => 'draft',
    ], $this->user->id);

    // Search by content
    $results = $this->service->search($this->repository->id, 'Apple');
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);

    // Search by type
    $results = $this->service->search($this->repository->id, null, ['memory_type' => 'preference']);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem2->id);

    // Search by status
    $results = $this->service->search($this->repository->id, null, ['status' => 'verified']);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);
});

it('prevents AI from creating restricted memory types', function () {
    $data = [
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'system_constraint', // Restricted
        'created_by_type' => 'ai',
        'current_content' => 'Restricted Content',
    ];

    expect(fn () => $this->service->write($data, 'agent-1', 'ai'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('prevents AI from updating restricted memory types', function () {
    // Created by human (allowed)
    $memory = $this->service->write([
        'organization_id' => $this->repository->organization_id,
        'repository_id' => $this->repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule', // Restricted
        'created_by_type' => 'human',
        'current_content' => 'Human Rule',
    ], $this->user->id, 'human');

    // AI tries to update
    $updateData = [
        'id' => $memory->id,
        'current_content' => 'AI Hack',
    ];

    expect(fn () => $this->service->write($updateData, 'agent-1', 'ai'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

