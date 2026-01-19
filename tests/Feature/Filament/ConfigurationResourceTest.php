<?php

use App\Filament\Resources\Configurations\ConfigurationResource;
use App\Filament\Resources\Configurations\Pages\CreateConfiguration;
use App\Filament\Resources\Configurations\Pages\EditConfiguration;
use App\Filament\Resources\Configurations\Pages\ListConfigurations;
use App\Models\Configuration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can renders list page', function () {
    Livewire::test(ListConfigurations::class)
        ->assertSuccessful();
});

it('can create a configuration', function () {
    Livewire::test(CreateConfiguration::class)
        ->fillForm([
            'key' => 'feature_x',
            'type' => 'boolean',
            'group' => 'features',
            'value' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Configuration::where('key', 'feature_x')->exists())->toBeTrue();
});

it('can edit a configuration', function () {
    $config = Configuration::create([
        'key' => 'site_title',
        'value' => 'Old Title',
        'type' => 'string',
        'group' => 'general',
    ]);

    Livewire::test(EditConfiguration::class, ['record' => $config->getKey()])
        ->fillForm([
            'value' => 'New Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($config->fresh()->value)->toBe('New Title');
});

it('validates unique key', function () {
    Configuration::create(['key' => 'exists', 'value' => '1', 'type' => 'string']);

    Livewire::test(CreateConfiguration::class)
        ->fillForm(['key' => 'exists'])
        ->call('create')
        ->assertHasFormErrors(['key']);
});
