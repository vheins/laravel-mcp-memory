<?php

use App\Filament\Resources\Taxonomies\Pages\CreateTaxonomy;
use App\Filament\Resources\Taxonomies\Pages\EditTaxonomy;
use App\Filament\Resources\Taxonomies\Pages\ListTaxonomies;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render list taxonomies page', function (): void {
    Livewire::test(ListTaxonomies::class)
        ->assertSuccessful();
});

it('can create a taxonomy', function (): void {
    Livewire::test(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Product Categories',
            'slug' => 'product-categories',
            'is_hierarchical' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Taxonomy::query()->where('slug', 'product-categories')->exists())->toBeTrue();
});

it('can edit a taxonomy', function (): void {
    $taxonomy = Taxonomy::factory()->create();

    Livewire::test(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($taxonomy->refresh()->name)->toBe('Updated Name');
});

it('can validate taxonomy unique slug', function (): void {
    $taxonomy = Taxonomy::factory()->create(['slug' => 'existing-slug']);

    Livewire::test(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'New Taxonomy',
            'slug' => 'existing-slug',
            'is_hierarchical' => false,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});
