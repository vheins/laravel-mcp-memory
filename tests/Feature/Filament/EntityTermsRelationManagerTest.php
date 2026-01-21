<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\RelationManagers\EntityTermsRelationManager;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->taxonomy = Taxonomy::query()->create(['name' => 'Tags', 'slug' => 'tags']);
    $this->term = Term::query()->create(['taxonomy_id' => $this->taxonomy->id, 'name' => 'VIP', 'slug' => 'vip']);
    $this->user = User::factory()->create();
});

it('can list terms attached to a user', function (): void {
    $this->user->terms()->attach($this->term);

    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user,
        'pageClass' => EditUser::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->term]);
});

it('can attach a term', function (): void {
    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('attach', data: [
            'recordId' => $this->term->getKey(),
        ])
        ->assertHasNoActionErrors();

    expect($this->user->fresh()->terms)->toHaveCount(1);
});

it('can detach a term', function (): void {
    $this->user->terms()->attach($this->term);

    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user, // @phpstan-ignore-line
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('detach', $this->term)
        ->assertHasNoActionErrors();

    expect($this->user->fresh()->terms)->toHaveCount(0);
});
