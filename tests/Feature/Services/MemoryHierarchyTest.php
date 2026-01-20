<?php

use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new MemoryService;
    $this->orgId = Str::uuid()->toString();
    $this->repo = Repository::create([
        'organization_id' => $this->orgId, // Repository model map to organization_id column
        'name' => 'Hierarchy Repo',
        'slug' => 'hierarchy-repo',
    ]);
    // Create a real user
    $this->user = User::factory()->create();
    $this->userId = $this->user->id;
});

it('resolves hierarchy correctly', function () {
    // 1. System Memory
    $this->service->write([
        'organization' => $this->orgId,
        'repository' => null,
        'scope_type' => 'system',
        'memory_type' => 'system_constraint',
        'created_by_type' => 'human',
        'current_content' => 'System Content',
    ], 'system', 'human');

    // 2. Organization Memory
    $this->service->write([
        'organization' => $this->orgId,
        'repository' => null,
        'scope_type' => 'organization',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Org Content',
    ], 'org-admin', 'human');

    // 3. Repository Memory
    $this->service->write([
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Repo Content',
    ], 'repo-admin', 'human');

    // 4. User Memory (for this user)
    $this->service->write([
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user' => $this->userId,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User Content',
    ], (string) $this->userId, 'human');

    // 5. Another User Memory (should NOT be seen)
    $otherUser = User::factory()->create();
    $this->service->write([
        'organization' => $this->orgId,
        'repository' => $this->repo->id,
        'user' => $otherUser->id,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'Other User Content',
    ], (string) $otherUser->id, 'human');

    // Search WITHOUT user context -> Expect System, Org, Repo (3 items)
    $resultsNoUser = $this->service->search($this->repo->id);
    expect($resultsNoUser)->toHaveCount(3);
    expect($resultsNoUser->pluck('current_content'))
        ->toContain('System Content')
        ->toContain('Org Content')
        ->toContain('Repo Content')
        ->not->toContain('User Content')
        ->not->toContain('Other User Content');

    // Search WITH user context -> Expect System, Org, Repo, User (4 items)
    $resultsUser = $this->service->search($this->repo->id, null, ['user' => $this->userId]);
    expect($resultsUser)->toHaveCount(4);
    expect($resultsUser->pluck('current_content'))
        ->toContain('System Content')
        ->toContain('Org Content')
        ->toContain('Repo Content')
        ->toContain('User Content')
        ->not->toContain('Other User Content');
});

it('isolates memories between organizations and repositories', function () {
    // 1. Create a different Organization and Repository
    $otherOrgId = Str::uuid()->toString();
    $otherRepo = Repository::create([
        'organization_id' => $otherOrgId,
        'name' => 'Other Repo',
        'slug' => 'other-repo',
    ]);

    // 2. Create memory in Other Repo
    $this->service->write([
        'organization' => $otherOrgId,
        'repository' => $otherRepo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Other Repo Rule',
    ], 'other-admin', 'human');

    // 3. Search in ORIGINAL Repo -> Should NOT see Other Repo Rule
    $results = $this->service->search($this->repo->id);
    expect($results->pluck('current_content'))->not->toContain('Other Repo Rule');

    // 4. Search in OTHER Repo -> Should see Other Repo Rule
    $resultsOther = $this->service->search($otherRepo->id);
    expect($resultsOther->pluck('current_content'))->toContain('Other Repo Rule');
});
