<?php

use Illuminate\Support\Str;
use App\Filament\Resources\Memories\Pages\ListMemories;
use App\Models\Memory;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Enums\MemoryStatus;

uses(RefreshDatabase::class);

it('can verify a memory', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = Str::uuid()->toString();
    $repo = Repository::query()->create(['organization_id' => $orgId, 'name' => 'Repo', 'slug' => 'repo']);

    $memory = Memory::query()->create([
        'organization' => $orgId,
        'repository' => $repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => MemoryStatus::Draft,
        'created_by_type' => 'human',
        'current_content' => 'Content',
        'user' => $user->id,
    ]);

    Livewire::test(ListMemories::class)
        ->callTableAction('verify', $memory);

    expect($memory->refresh()->status)->toBe(MemoryStatus::Verified);
});

it('can lock a memory', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = Str::uuid()->toString();
    $repo = Repository::query()->create(['organization_id' => $orgId, 'name' => 'Repo', 'slug' => 'repo']);

    $memory = Memory::query()->create([
        'organization' => $orgId,
        'repository' => $repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => MemoryStatus::Verified,
        'created_by_type' => 'human',
        'current_content' => 'Content',
        'user' => $user->id,
    ]);

    Livewire::test(ListMemories::class)
        ->callTableAction('lock', $memory);

    expect($memory->refresh()->status)->toBe(MemoryStatus::Locked);
});
