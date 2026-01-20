<?php

use App\Filament\Resources\Memories\MemoryResource;
use App\Filament\Resources\Memories\Pages\CreateMemory;
use App\Filament\Resources\Memories\Pages\EditMemory;
use App\Filament\Resources\Memories\Pages\ListMemories;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render memory resource page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(MemoryResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list memories', function () {
    $user = User::factory()->create();
    $this->actingAs($user); // Assuming user has permission

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repository = Repository::create([
        'organization_id' => $orgId,
        'name' => 'Test Repo',
        'slug' => 'test-repo',
    ]);

    $memory = Memory::create([
        'organization' => $orgId,
        'repository' => $repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Test Content',
        'user' => $user->id,
    ]);

    Livewire::test(ListMemories::class)
        ->assertCanSeeTableRecords([$memory]);
});

it('can create a memory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repository = Repository::create([
        'organization_id' => $orgId,
        'name' => 'Test Repo',
        'slug' => 'test-repo',
    ]);

    Livewire::test(CreateMemory::class)
        ->fillForm([
            'organization' => $orgId,
            'repository' => $repository->id,
            'scope_type' => 'repository',
            'memory_type' => 'business_rule',
            'status' => 'draft',
            'current_content' => 'New Content',
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('memories', [
        'current_content' => 'New Content',
        'repository' => $repository->id,
    ]);
});

it('can edit a memory', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repository = Repository::create([
        'organization_id' => $orgId,
        'name' => 'Test Repo',
        'slug' => 'test-repo',
    ]);

    $memory = Memory::create([
        'organization' => $orgId,
        'repository' => $repository->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Old Content',
        'user' => $user->id,
    ]);

    Livewire::test(EditMemory::class, [
        'record' => $memory->getRouteKey(),
    ])
        ->fillForm([
            'current_content' => 'Updated Content',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('memories', [
        'id' => $memory->id,
        'current_content' => 'Updated Content',
    ]);
});
