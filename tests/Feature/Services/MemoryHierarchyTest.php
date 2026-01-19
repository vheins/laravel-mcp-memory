<?php

use App\Models\Memory;
use App\Models\Repository;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new MemoryService();
    $this->orgId = Str::uuid()->toString();
    $this->repo = Repository::create([
        'organization_id' => $this->orgId,
        'name' => 'Hierarchy Repo',
        'slug' => 'hierarchy-repo',
    ]);
    $this->userId = Str::uuid()->toString();
});

it('resolves hierarchy correctly', function () {
    // 1. System Memory
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => null,
        'scope_type' => 'system',
        'memory_type' => 'system_constraint',
        'created_by_type' => 'human',
        'current_content' => 'System Content',
    ], 'system', 'human');

    // 2. Organization Memory
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => null,
        'scope_type' => 'organization',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Org Content',
    ], 'org-admin', 'human');

    // 3. Repository Memory
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Repo Content',
    ], 'repo-admin', 'human');

    // 4. User Memory (for this user)
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'user_id' => $this->userId,
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'User Content',
    ], $this->userId, 'human');

    // 5. Another User Memory (should NOT be seen)
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'user_id' => Str::uuid()->toString(),
        'scope_type' => 'user',
        'memory_type' => 'preference',
        'created_by_type' => 'human',
        'current_content' => 'Other User Content',
    ], 'other-user', 'human');

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
    $resultsUser = $this->service->search($this->repo->id, null, ['user_id' => $this->userId]);
    expect($resultsUser)->toHaveCount(4);
    expect($resultsUser->pluck('current_content'))
        ->toContain('System Content')
        ->toContain('Org Content')
        ->toContain('Repo Content')
        ->toContain('User Content')
        ->not->toContain('Other User Content');
});

it('isolates memories between organizations and repositories', function () {
    // Org 2 & Repo 2
    $org2Id = Str::uuid()->toString();
    $repo2 = Repository::create([
        'organization_id' => $org2Id,
        'name' => 'Other Org Repo',
        'slug' => 'other-org-repo',
    ]);

    // Create Memory in Org 2
    $this->service->write([
        'organization_id' => $org2Id,
        'repository_id' => null,
        'scope_type' => 'organization',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Org 2 Content',
    ], 'org2-admin', 'human');

    // Create Memory in Repo 2
    $this->service->write([
        'organization_id' => $org2Id,
        'repository_id' => $repo2->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Repo 2 Content',
    ], 'repo2-admin', 'human');

    // Search Repo 1 (from setup) -> Should NOT see Org 2 or Repo 2 content
    $results = $this->service->search($this->repo->id);

    // We expect 0 here because in this test function we didn't create any Repo 1 content
    // (except what might be in setup? Setup creates Repo 1 but no memories).
    // Note: 'beforeEach' creates $this->repo. The first test created memories, but tests are isolated by RefreshDatabase?
    // standard PHPUnit/Pest with RefreshDatabase clears DB.
    // So distinct test functions start empty.

    // Let's create one Repo 1 memory to be sure we see SOMETHING
    $this->service->write([
        'organization_id' => $this->orgId,
        'repository_id' => $this->repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'created_by_type' => 'human',
        'current_content' => 'Repo 1 Content',
    ], 'repo1-admin', 'human');

    $results = $this->service->search($this->repo->id);

    expect($results->pluck('current_content'))
        ->toContain('Repo 1 Content')
        ->not->toContain('Org 2 Content')
        ->not->toContain('Repo 2 Content');
});
