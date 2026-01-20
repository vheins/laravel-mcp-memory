<?php

use App\Filament\Widgets\MemoryUsageStatsWidget;
use App\Models\Memory;
use App\Models\MemoryAccessLog;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('memory service logs access on read', function () {
    $memory = Memory::factory()->create(['title' => 'Test Memory']);
    $service = app(MemoryService::class);

    $service->read($memory->id, 'user-1', 'user');

    expect(MemoryAccessLog::count())->toBe(1);
    $log = MemoryAccessLog::first();
    expect($log->action)->toBe('read');
    expect($log->resource_id)->toBe($memory->id);
    expect($log->actor_id)->toBe('user-1');
});

test('memory service logs access on search', function () {
    $service = app(MemoryService::class);

    $service->search('repo-1', 'query', [], 'user-1', 'user');

    expect(MemoryAccessLog::count())->toBe(1);
    $log = MemoryAccessLog::first();
    expect($log->action)->toBe('search');
    expect($log->actor_id)->toBe('user-1');
    expect($log->metadata['query'])->toBe('query');
});

test('dashboard stats widget calculates correctly', function () {
    // Create some logs
    MemoryAccessLog::create(['action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::create(['action' => 'search', 'created_at' => now()]);
    MemoryAccessLog::create(['action' => 'search', 'created_at' => now()]);
    MemoryAccessLog::create(['action' => 'create', 'created_at' => now()]);

    Livewire::test(MemoryUsageStatsWidget::class)
        ->assertSee('Total Requests (30d)')
        ->assertSee('4')
        ->assertSee('Total Searches (30d)')
        ->assertSee('2')
        ->assertSee('Write Operations (30d)')
        ->assertSee('1');
});

test('user usage widget displays top users', function () {
    MemoryAccessLog::create(['actor_id' => 'alice', 'action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::create(['actor_id' => 'alice', 'action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::create(['actor_id' => 'bob', 'action' => 'read', 'created_at' => now()]);

    Livewire::test(\App\Filament\Widgets\MemoryUserUsageChartWidget::class)
        ->assertSee('alice') // alice has 2
        ->assertSee('bob');  // bob has 1
});
