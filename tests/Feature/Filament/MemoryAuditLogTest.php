<?php

use App\Filament\Resources\Memories\Pages\EditMemory;
use App\Filament\Resources\Memories\RelationManagers\AuditLogsRelationManager;
use App\Models\Memory;
use App\Models\MemoryAuditLog;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render audit logs relation manager', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $orgId = \Illuminate\Support\Str::uuid()->toString();
    $repo = Repository::create(['organization_id' => $orgId, 'name' => 'Repo', 'slug' => 'repo']);

    $memory = Memory::create([
        'organization' => $orgId,
        'repository' => $repo->id,
        'scope_type' => 'repository',
        'memory_type' => 'business_rule',
        'status' => 'draft',
        'created_by_type' => 'human',
        'current_content' => 'Content 1',
        'user' => $user->id,
    ]);

    // Create a dummy audit log manually since we don't know if Memory observer creates it automatically yet
    MemoryAuditLog::create([
        'memory_id' => $memory->id,
        'actor_id' => $user->id,
        'actor_type' => User::class,
        'event' => 'created',
        'new_value' => ['current_content' => 'Content 1'],
        'created_at' => now(),
    ]);

    Livewire::test(AuditLogsRelationManager::class, [
        'ownerRecord' => $memory,
        'pageClass' => EditMemory::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($memory->auditLogs);
});
