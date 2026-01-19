<?php

use App\Filament\RelationManagers\EntityTermsRelationManager;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->taxonomy = Taxonomy::create(['name' => 'Tags', 'slug' => 'tags']);
    $this->term = Term::create(['taxonomy_id' => $this->taxonomy->id, 'name' => 'VIP', 'slug' => 'vip']);
    $this->user = User::factory()->create();
});

it('can list terms attached to a user', function () {
    $this->user->terms()->attach($this->term);

    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user,
        'pageClass' => \App\Filament\Resources\Users\Pages\EditUser::class,
    ])
    ->assertSuccessful()
    ->assertCanSeeTableRecords([$this->term]);
});

it('can attach a term', function () {
    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user,
        'pageClass' => \App\Filament\Resources\Users\Pages\EditUser::class,
    ])
    ->callTableAction('attach', data: [
        'recordId' => $this->term->getKey(),
    ])
    ->assertHasNoActionErrors();

    expect($this->user->fresh()->terms)->toHaveCount(1);
});

it('can detach a term', function () {
    $this->user->terms()->attach($this->term);

    Livewire::test(EntityTermsRelationManager::class, [
        'ownerRecord' => $this->user, // @phpstan-ignore-line
        'pageClass' => \App\Filament\Resources\Users\Pages\EditUser::class,
    ])
    ->callTableAction('detach', $this->term)
    ->assertHasNoActionErrors();

    expect($this->user->fresh()->terms)->toHaveCount(0);
});
