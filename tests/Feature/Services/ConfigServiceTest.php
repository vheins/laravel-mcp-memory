<?php

use App\Models\Configuration;
use App\Services\ConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('caches configuration calculation', function () {
    $item = Configuration::create(['key' => 'foo', 'value' => 'bar']);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with('config_foo', Closure::class)
        ->andReturn('bar');

    $service = new ConfigService;
    $value = $service->get('foo');

    expect($value)->toBe('bar');
});

it('invalidates cache on update', function () {
    $config = Configuration::create(['key' => 'foo', 'value' => 'bar']);

    // Seed cache
    $service = new ConfigService;
    $service->get('foo');

    expect(Cache::has('config_foo'))->toBeTrue();

    // Update via service or event
    $config->update(['value' => 'baz']);

    expect(Cache::has('config_foo'))->toBeFalse();
});
