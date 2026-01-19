<?php

use App\Filament\Resources\Memories\Widgets\MemoryStatsOverview;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render memory stats widget', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repo = Repository::create(['organization_id' => $orgId, 'name' => 'Repo', 'slug' => 'repo']);

    Memory::create([
        'organization' => $orgId,
        'repository' => $repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Content 1',
        'user' => $user->id,
    ]);

    Memory::create([
        'organization' => $orgId,
        'repository' => $repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'locked',
        'created_by_type' => 'human',
        'current_content' => 'Content 2',
        'user' => $user->id,
    ]);

    Livewire::test(MemoryStatsOverview::class)
        ->assertSee('Total Memories')
        ->assertSee('2')
        ->assertSee('Unverified Memories')
        ->assertSee('1')
        ->assertSee('Locked Memories')
        ->assertSee('1');
});
