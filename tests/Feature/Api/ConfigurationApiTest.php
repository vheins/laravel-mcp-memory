<?php

use App\Models\Configuration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can fetch public configurations', function () {
    Configuration::create([
        'key' => 'site_name',
        'value' => 'My App',
        'type' => 'string',
        'is_public' => true,
    ]);

    Configuration::create([
        'key' => 'admin_email',
        'value' => 'admin@example.com',
        'type' => 'string',
        'is_public' => false,
    ]);

    $response = $this->getJson(route('api.v1.configurations.fetch'));

    $response->assertOk()
        ->assertJsonFragment(['site_name' => 'My App'])
        ->assertJsonMissing(['admin_email']);
});

it('can list all configurations', function () {
    Configuration::create(['key' => 'k1', 'value' => 'v1']);
    Configuration::create(['key' => 'k2', 'value' => 'v2']);

    $response = $this->getJson(route('api.v1.configurations.index'));

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can update configuration', function () {
    $config = Configuration::create(['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean']);

    $response = $this->putJson(route('api.v1.configurations.update', $config), [
        'value' => true,
    ]);

    $response->assertOk();
    expect($config->refresh()->value)->toBeTrue();
});

it('casts values correctly', function () {
    $config = Configuration::create(['key' => 'max_items', 'value' => 10, 'type' => 'number']);

    expect($config->value)->toBe(10);
    expect($config->fresh()->value)->toBe(10);

    $jsonConfig = Configuration::create([
        'key' => 'settings',
        'value' => ['theme' => 'dark'],
        'type' => 'json',
    ]);

    expect($jsonConfig->value)->toBe(['theme' => 'dark']);
});
