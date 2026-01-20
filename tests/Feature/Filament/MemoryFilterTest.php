<?php

use App\Filament\Resources\Memories\Pages\ListMemories;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can filter memories by repository', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repo1 = Repository::create(['organization_id' => $orgId, 'name' => 'Repo A', 'slug' => 'repo-a']);
    $repo2 = Repository::create(['organization_id' => $orgId, 'name' => 'Repo B', 'slug' => 'repo-b']);

    $mem1 = Memory::create([
        'organization' => $orgId,
        'repository' => $repo1->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Memory A',
        'user' => $user->id,
    ]);

    $mem2 = Memory::create([
        'organization' => $orgId,
        'repository' => $repo2->id, // Different repo
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Memory B',
        'user' => $user->id,
    ]);

    Livewire::test(ListMemories::class)
        ->assertCanSeeTableRecords([$mem1, $mem2])
        ->filterTable('repository', $repo1->id)
        ->assertCanSeeTableRecords([$mem1])
        ->assertCanNotSeeTableRecords([$mem2]);
});
