<?php

use Illuminate\Validation\ValidationException;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Enums\MemoryScope;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @property MemoryService $service
 * @property User $user
 * @property Repository $repository
 */
uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new MemoryService;
    $this->user = User::factory()->create();
    $this->repository = Repository::factory()->create(['organization_id' => str()->uuid()]);
});

it('can create a new memory', function (): void {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Initial content',
        'title' => 'Test Title',
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

it('creates a new version when content updates', function (): void {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Initial content',
        'title' => 'Test Title',
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

it('prevents update when memory is locked', function (): void {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Content',
        'status' => MemoryStatus::Locked,
        'title' => 'Locked Memory',
    ];

    // Force create a locked memory directly via model to bypass service check if any
    // asking service to create locked memory
    $memory = Memory::query()->create(array_merge($data, ['organization' => 'test-org']));

    $updateData = [
        'id' => $memory->id,
        'current_content' => 'New Content',
        'organization' => 'test-org',
        'repository' => 'test-repo',
        // ... other required fields
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'title' => 'Locked Memory',
    ];

    expect(fn () => $this->service->write($updateData, $this->user->id))
        ->toThrow(Exception::class, 'Cannot update locked memory.');
});

it('can read a memory', function (): void {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Content to read',
        'title' => 'Readable Memory',
    ];
    $memory = $this->service->write($data, $this->user->id);

    $readMemory = $this->service->read($memory->id);

    expect($readMemory->id)->toBe($memory->id);
    expect($readMemory->current_content)->toBe('Content to read');
});

it('can search memories', function (): void {
    // Create queryable memories
    $mem1 = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::BusinessRule,
        'created_by_type' => 'human',
        'current_content' => 'Apple pie recipe',
        'title' => 'Apple Pie',
        'status' => MemoryStatus::Verified,
    ], $this->user->id);

    $mem2 = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::Preference,
        'created_by_type' => 'human',
        'current_content' => 'Banana bread recipe',
        'title' => 'Banana Bread',
        'status' => MemoryStatus::Active,
    ], $this->user->id);

    // Search by content
    $results = $this->service->search('Apple', ['repository' => 'test-repo']);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);

    // Search by type
    $results = $this->service->search(null, ['repository' => 'test-repo', 'memory_type' => MemoryType::Preference]);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem2->id);

    // Search by status
    $results = $this->service->search(null, ['repository' => 'test-repo', 'status' => MemoryStatus::Verified]);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($mem1->id);
});

it('can search by metadata', function (): void {
    $memory = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => MemoryScope::Repository,
        'memory_type' => MemoryType::Fact,
        'current_content' => 'Tech Info',
        'title' => 'PHP Info',
        'metadata' => ['language' => 'PHP', 'version' => '8.3'],
        'status' => MemoryStatus::Active,
    ], $this->user->id);

    $results = $this->service->search('PHP', ['repository' => 'test-repo']);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($memory->id);

    $results = $this->service->search('8.3', ['repository' => 'test-repo']);
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($memory->id);
});

it('prevents AI from creating restricted memory types', function (): void {
    $data = [
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => MemoryType::SystemConstraint, // Restricted
        'created_by_type' => 'ai',
        'current_content' => 'Restricted Content',
        'title' => 'Restricted Memory',
    ];

    expect(fn () => $this->service->write($data, 'agent-1', 'ai'))
        ->toThrow(ValidationException::class);
});

it('prevents AI from updating restricted memory types', function (): void {
    // Created by human (allowed)
    $memory = $this->service->write([
        'organization' => 'test-org',
        'repository' => 'test-repo',
        'scope_type' => 'repository',
        'memory_type' => MemoryType::BusinessRule, // Restricted
        'created_by_type' => 'human',
        'current_content' => 'Human Rule',
        'title' => 'Human Rule',
    ], $this->user->id, 'human');

    // AI tries to update
    $updateData = [
        'id' => $memory->id,
        'current_content' => 'AI Hack',
    ];

    expect(fn () => $this->service->write($updateData, 'agent-1', 'ai'))
        ->toThrow(ValidationException::class);
});
