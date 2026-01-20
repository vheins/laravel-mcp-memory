<?php

use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Enums\MemoryScope;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new MemoryService;
    $this->user = User::factory()->create();
    $this->repository = Repository::factory()->create(['organization_id' => str()->uuid()]);
});

it('can create a new memory', function () {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Initial content',
        'metadata' => ['key' => 'value'],
    ];

    $memory = $this->service->write($data, $this->user->id);
    $data['title'] = 'Test Title';

    $memory = $this->service->write($data, $this->user->id);

    expect($memory)->toBeInstanceOf(Memory::class)
        ->id->not->toBeNull()
        ->title->toBe('Test Title')
        ->current_content->toBe('Initial content')
        ->status->toBe(MemoryStatus::Draft);

    // Verify Version
    expect($memory->versions)->toHaveCount(1);
    expect($memory->versions->first()->content)->toBe('Initial content');

    // Verify Audit Log
    expect($memory->auditLogs)->toHaveCount(1);
    expect($memory->auditLogs->first()->event)->toBe('create');
});

it('creates a new version when content updates', function () {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
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
    expect($updatedMemory->auditLogs()->latest('created_at')->first()->event)->toBe('update');
});

it('prevents update when memory is locked', function () {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Content',
        'status' => MemoryStatus::Locked,
    ];

    // Force create a locked memory directly via model to bypass service check if any
    // asking service to create locked memory
    $memory = Memory::create(array_merge($data, ['organization' => 'test-org']));

    $updateData = [
        'id' => $memory->id,
        'current_content' => 'New Content',
        'organization' => 'test-org',
        'repository' => 'test-repo',
        // ... other required fields
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
    ];

    expect(fn () => $this->service->write($updateData, $this->user->id))
        ->toThrow(\Exception::class, 'Cannot update locked memory.');
});

it('can read a memory', function () {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
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
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Apple pie recipe',
        'status' => MemoryStatus::Verified,
    ], $this->user->id);

    $mem2 = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::Preference,
        'created_by_type' => 'human',
        'current_content' => 'Banana bread recipe',
        'status' => MemoryStatus::Draft,
    ], $this->user->id);

    // Search by content
    $results = $this->service->search('test-repo', 'Apple');
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);

    // Search by type
    $results = $this->service->search('test-repo', null, ['memory_type' => MemoryType::Preference]);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem2->id);

    // Search by status
    $results = $this->service->search('test-repo', null, ['status' => MemoryStatus::Verified]);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);
});

it('prevents AI from creating restricted memory types', function () {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => MemoryType::SystemConstraint, // Restricted
        'created_by_type' => 'ai',
        'current_content' => 'Restricted Content',
    ];

    expect(fn () => $this->service->write($data, 'agent-1', 'ai'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('prevents AI from updating restricted memory types', function () {
    // Created by human (allowed)
    $memory = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => MemoryType::BusinessRule, // Restricted
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
